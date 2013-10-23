<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_convert_file extends CI_Model {

    public $cigna_id;
    public $default_input;

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        //load model
        $this->load->model('m_convert');
        //load library
        $this->load->library('phpexcel');
        //assign global variable
        $this->cigna_id = 11;
        // get default format
        $params = array("Batch Upload", 'yes');
        $this->default_input = $this->m_convert->get_default_input($params);
    }

    /*
     * Read File Logic
     */

    // read data by type file
    public function read_data($params) {
        // result
        $result = array();
        // switch type file
        switch ($params['input_file_type']) {
            case 'txt':
                $result = $this->read_txt($params);
                break;
            case 'csv':
                $result = $this->read_csv($params);
                break;
            case 'xls':
                $result = $this->read_excel($params);
                break;
            case 'xlsx':
                $result = $this->read_excelx($params);
                break;
            default:
        }
        // return
        return $result;
    }

    // read data txt
    public function read_txt($params) {
        // default
        if (empty($params['input_delimiter'])) {
            $params['input_delimiter'] = ";";
        }
        /*
         * pembacaan input sampai pada jumlah transaksi yang akan di konvert
         */
        // result
        $transaksi = array();
        /*
         * Start code below
         */
        // get input row
        $rs_row = $this->m_input->get_all_input_rows_by_id($params['input_id']);
        $total_baris = count($rs_row);
        // read file
        if (isset($_SESSION['converter_file'])) {
            // read
            $file_path = $_SESSION['converter_file'];
            if (is_file($file_path)) {
                $file = fopen($file_path, "r");
                $data = array();
                $transaksi_data = array();
                $num_row = 0;
                $i = 0;
                $t = 1;
                // output a line of the file until the end is reached
                while (!feof($file)) {
                    // get data
                    $data = fgets($file);
                    // start row
                    $num_row++;
                    if ($num_row >= $params['input_row']) {
                        // process
                        if (!empty($data)) {
                            $jml_ambil = $rs_row[$i]['total_column'] + 1;
                            $total = explode($params['input_delimiter'], $data);
                            $total = count($total);
                            // explode
                            $data = explode($params['input_delimiter'], $data, $jml_ambil);
                            if ($total >= $jml_ambil) {
                                array_pop($data);
                            }
                            // push with empty array if data < total column
                            if (count($data) < $rs_row[$i]['total_column']) {
                                for ($index = count($data) + 1; $index <= $rs_row[$i]['total_column']; $index++) {
                                    array_push($data, '');
                                }
                            }
                            // hasil
                            $transaksi_data = array_merge($transaksi_data, $data);
                            // increment
                            $i++;
                            // --
                            if ($i >= ($total_baris)) {
                                $transaksi[$t] = $transaksi_data;
                                $i = 0;
                                $t++;
                                $transaksi_data = array();
                            }
                        }
                    }
                }
                fclose($file);
            }
        }
        // return
        return $transaksi;
    }

    // read data csv
    public function read_csv($params) {
        // default
        if (empty($params['input_delimiter'])) {
            $params['input_delimiter'] = ";";
        }
        /*
         * pembacaan input sampai pada jumlah transaksi yang akan di konvert
         */
        // result
        $transaksi = array();
        /*
         * Start code below
         */
        // get input row
        $rs_row = $this->m_input->get_all_input_rows_by_id($params['input_id']);
        $total_baris = count($rs_row);
        // read file
        if (isset($_SESSION['converter_file'])) {
            // read
            $file_path = $_SESSION['converter_file'];
            if (is_file($file_path)) {
                $file = fopen($file_path, "r");
                $data = array();
                $transaksi_data = array();
                $num_row = 0;
                $i = 0;
                $t = 1;
                // output a line of the file until the end is reached
                while (!feof($file)) {
                    // get data
                    $data = fgets($file);
                    // start row
                    $num_row++;
                    if ($num_row >= $params['input_row']) {
                        // process
                        if (!empty($data)) {
                            $jml_ambil = $rs_row[$i]['total_column'] + 1;
                            $total = explode($params['input_delimiter'], $data);
                            $total = count($total);
                            // explode
                            $data = explode($params['input_delimiter'], $data, $jml_ambil);
                            if ($total >= $jml_ambil) {
                                array_pop($data);
                            }
                            // push with empty array if data < total column
                            if (count($data) < $rs_row[$i]['total_column']) {
                                for ($index = count($data) + 1; $index <= $rs_row[$i]['total_column']; $index++) {
                                    array_push($data, '');
                                }
                            }
                            // hasil
                            $transaksi_data = array_merge($transaksi_data, $data);
                            // increment
                            $i++;
                            // --
                            if ($i >= ($total_baris)) {
                                $transaksi[$t] = $transaksi_data;
                                $i = 0;
                                $t++;
                                $transaksi_data = array();
                            }
                        }
                    }
                }
                fclose($file);
            }
        }
        // return
        return $transaksi;
    }

    // read data excel
    public function read_excel($params) {
        /*
         * pembacaan input sampai pada jumlah transaksi yang akan di konvert
         */
        // result
        $transaksi = array();
        /*
         * Start code below
         */
        // get input row
        $rs_row = $this->m_input->get_all_input_rows_by_id($params['input_id']);
        $total_baris = count($rs_row);
        $total_column = 0;
        foreach ($rs_row as $val) {
            $total_column+=$val['total_column'];
        }
        if (!empty($rs_row)) {
            // read file
            if (isset($_SESSION['converter_file'])) {
                // validasi jenis file
                $error_ext = true;
                $ext = explode('.', $_SESSION['converter_file']);
                $ext = end($ext);
                if ($ext != 'xls') {
                    $error_ext = false;
                }
                // read
                $file_path = $_SESSION['converter_file'];
                // result
                $rs_file = array();
                // identify index;
                $i = 0;
                $j = 0;
                $k = 1;
                $t = 1;

                // read data
                if (is_file($file_path) && $error_ext == true) {
                    //read excelx
                    $objReader = PHPExcel_IOFactory::createReader('Excel5');
                    $this->phpexcel = $objReader->load($file_path);
                    $objWorksheet = $this->phpexcel->setActiveSheetIndex(0);
                    $sheetData = $objWorksheet->toArray(null, true, true, false);

                    //unset empty field
                    function filter_cell($cell) {
                        return !is_null($cell);
                    }

                    //--
                    foreach ($sheetData as $key => &$row) {
                        $row = array_filter($row, 'filter_cell');
                        if (count($row) == 0) {
                            unset($sheetData[$key]);
                        }
                    }
                    unset($row);
                    // read
                    if (!empty($sheetData)) {
                        end($sheetData);
                        $lastindex = key($sheetData);
                        $limit_row = $params['input_row'] - 1;
                        for ($i = 0; $i <= $lastindex; $i++) {
                            if (!empty($sheetData[$i])) {
                                if ($i >= $limit_row) {
                                    if ($this->default_input['input_id'] == $this->cigna_id && empty($sheetData[$i][1]) && empty($sheetData[$i][2])) {
                                        break;
                                    }
                                    for ($key = 0; $key < $rs_row[$j]['total_column']; $key++) {
                                        $rs_file[$k][$key] = isset($sheetData[$i][$key]) ? mb_convert_encoding($sheetData[$i][$key], "UTF-8", "ISO-8859-9") : '';
                                        $t++;
                                    }
                                    // push with empty array if rs_file < total column
                                    if (count($rs_file[$k]) < $rs_row[$j]['total_column']) {
                                        for ($index = count($rs_file[$k]) + 1; $index <= $rs_row[$j]['total_column']; $index++) {
                                            array_push($rs_file[$k], '');
                                            $t++;
                                        }
                                    }
                                    //increment index
                                    $t = 1;
                                    $k++;
                                    $j++;
                                    // reset index j into 0
                                    // if reach limit row
                                    if ($j == $total_baris) {
                                        $j = 0;
                                    }
                                }
                            }
                        }
                    }
                }
                $i = 0;
                //assign into array transaksi
                foreach ($rs_file as $key => $value) {
                    $transaksi = array_merge($transaksi, $value);
                }
                //--
                if (!empty($transaksi)) {
                    $transaksi = array_chunk($transaksi, $total_column);
                }
            }
        }
        // return
        return $transaksi;
    }

    // read data excel
    public function read_excelx($params) {
        /*
         * pembacaan input sampai pada jumlah transaksi yang akan di konvert
         */
        // result
        $transaksi = array();
        /*
         * Start code below
         */
        // get input row
        $rs_row = $this->m_input->get_all_input_rows_by_id($params['input_id']);
        $total_baris = count($rs_row);
        $total_column = 0;
        foreach ($rs_row as $val) {
            $total_column+=$val['total_column'];
        }
        if (!empty($rs_row)) {
            // read file
            if (isset($_SESSION['converter_file'])) {
                // validasi jenis file
                $error_ext = true;
                $ext = explode('.', $_SESSION['converter_file']);
                $ext = end($ext);
                if ($ext != 'xlsx') {
                    $error_ext = false;
                }
                // read
                $file_path = $_SESSION['converter_file'];
                // result
                $rs_file = array();
                // identify index;
                $i = 0;
                $j = 0;
                $k = 1;
                $t = 1;

                // read data
                if (is_file($file_path) && $error_ext == true) {
                    //read excelx
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                    $this->phpexcel = $objReader->load($file_path);
                    $objWorksheet = $this->phpexcel->setActiveSheetIndex(0);
                    $sheetData = $objWorksheet->toArray(null, true, true, false);

                    //unset empty field
                    function filter_cellx($cell) {
                        return !is_null($cell);
                    }

                    //--
                    foreach ($sheetData as $key => &$row) {
                        $row = array_filterx($row, 'filter_cell');
                        if (count($row) == 0) {
                            unset($sheetData[$key]);
                        }
                    }
                    unset($row);
                    // read
                    if (!empty($sheetData)) {
                        end($sheetData);
                        $lastindex = key($sheetData);
                        $limit_row = $params['input_row'] - 1;
                        for ($i = 0; $i <= $lastindex; $i++) {
                            if (!empty($sheetData[$i])) {
                                if ($i >= $limit_row) {
                                    if ($this->default_input['input_id'] == $this->cigna_id && empty($sheetData[$i][1]) && empty($sheetData[$i][2])) {
                                        break;
                                    }
                                    for ($key = 0; $key < $rs_row[$j]['total_column']; $key++) {
                                        $rs_file[$k][$key] = isset($sheetData[$i][$key]) ? mb_convert_encoding($sheetData[$i][$key], "UTF-8", "ISO-8859-9") : '';
                                        $t++;
                                    }
                                    // push with empty array if rs_file < total column
                                    if (count($rs_file[$k]) < $rs_row[$j]['total_column']) {
                                        for ($index = count($rs_file[$k]) + 1; $index <= $rs_row[$j]['total_column']; $index++) {
                                            array_push($rs_file[$k], '');
                                            $t++;
                                        }
                                    }
                                    //increment index
                                    $t = 1;
                                    $k++;
                                    $j++;
                                    // reset index j into 0
                                    // if reach limit row
                                    if ($j == $total_baris) {
                                        $j = 0;
                                    }
                                }
                            }
                        }
                    }
                }
                $i = 0;
                //assign into array transaksi
                foreach ($rs_file as $key => $value) {
                    $transaksi = array_merge($transaksi, $value);
                }
                //--
                if (!empty($transaksi)) {
                    $transaksi = array_chunk($transaksi, $total_column);
                }
            }
        }
        // return
        return $transaksi;
    }

}