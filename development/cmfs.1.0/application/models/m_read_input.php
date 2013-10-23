<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_read_input extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        //load library
        $this->load->library('phpexcel');
    }

    /*
     * Read File Logic
     */

    // read data by type file
    public function read_data($params) {
        $ext = explode('.', $params['input_detail']['input_file_path']);
        // result
        $result = array();
        if (!empty($ext[1]) && strtolower($ext[1]) == $params['input_detail']['input_file_type'] && is_file($params['file_path'])) {
            // switch type file
            switch ($params['input_detail']['input_file_type']) {
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
        } else {
            $result = $this->read_default($params);
        }
        // return
        return $result;
    }

    // read data txt
    public function read_txt($params) {
        // result
        $result = array();
        /*
         * Start code below
         */
        // init var
        $data = array();
        $rs_file = array();
        $arr_scope = array();
        // open text file
        $file = fopen($params['file_path'], "r");
        // output a line of the file until the end is reached
        $start_baris = $params['input_detail']['input_row'];
        $total_baris = $start_baris + count($params['rs_rows']) - 1;
        for ($x = $start_baris; $x <= $total_baris; $x++) {
            $arr_scope[] = $x;
        }
        $i = 1;
        $j = 0;
        while (!feof($file)) {
            unset($data);
            $data = fgets($file);
            /*
             * limit untuk menghemat pembacaan
             * baris awal + total baris yang dibaca
             */
            if (in_array($i, $arr_scope)) {
                $jml_ambil = $params['rs_rows'][$j]['total_column'] + 1;
                if (empty($params['input_detail']['input_delimiter'])) {
                    $params['input_detail']['input_delimiter'] = ' ';
                }
                $total = explode($params['input_detail']['input_delimiter'], $data);
                $total = count($total);
                // explode
                $data = explode($params['input_detail']['input_delimiter'], $data, $jml_ambil);
                if ($total >= $jml_ambil) {
                    array_pop($data);
                }
                // push with empty array if data < total column
                if (count($data) < $params['rs_rows'][$j]['total_column']) {
                    for ($index = count($data) + 1; $index <= $params['rs_rows'][$j]['total_column']; $index++) {
                        array_push($data, '');
                    }
                }
                // merge
                $rs_file = array_merge($rs_file, $data);
                $j++;
            }
            $i++;
        }
        fclose($file);
        // array result
        $i = 1;
        foreach ($rs_file as $rec) {
            $result[$i]['name'] = isset($params['rs_field'][$i - 1]['field_name']) ? $params['rs_field'][$i - 1]['field_name'] : '';
            $result[$i]['desc'] = isset($params['rs_field'][$i - 1]['field_desc']) ? $params['rs_field'][$i - 1]['field_desc'] : '';
            $result[$i]['value'] = $rec;
            $i++;
        }
        // return
        return $result;
    }

    //read data csv
    public function read_csv($params) {
        // result
        $result = array();

        // init var
        $data = array();
        $rs_file = array();
        $arr_scope = array();
        // open text file
        $file = fopen($params['file_path'], "r");
        // output a line of the file until the end is reached
        $start_baris = $params['input_detail']['input_row'];
        $total_baris = $start_baris + count($params['rs_rows']) - 1;
        for ($x = $start_baris; $x <= $total_baris; $x++) {
            $arr_scope[] = $x;
        }
        $i = 1;
        $j = 0;
        while (!feof($file)) {
            unset($data);
            $data = fgets($file);
            /*
             * limit untuk menghemat pembacaan
             * baris awal + total baris yang dibaca
             */
            if (in_array($i, $arr_scope)) {
                $jml_ambil = $params['rs_rows'][$j]['total_column'] + 1;
                if (empty($params['input_detail']['input_delimiter'])) {
                    $params['input_detail']['input_delimiter'] = ' ';
                }
                $total = explode($params['input_detail']['input_delimiter'], $data);
                $total = count($total);
                // explode
                $data = explode($params['input_detail']['input_delimiter'], $data, $jml_ambil);
                if ($total >= $jml_ambil) {
                    array_pop($data);
                }
                // push with empty array if data < total column
                if (count($data) < $params['rs_rows'][$j]['total_column']) {
                    for ($index = count($data) + 1; $index <= $params['rs_rows'][$j]['total_column']; $index++) {
                        array_push($data, '');
                    }
                }
                // merge
                $rs_file = array_merge($rs_file, $data);
                $j++;
            }
            $i++;
        }
        fclose($file);
        // array result
        $i = 1;
        foreach ($rs_file as $rec) {
            $result[$i]['name'] = isset($params['rs_field'][$i - 1]['field_name']) ? $params['rs_field'][$i - 1]['field_name'] : '';
            $result[$i]['desc'] = isset($params['rs_field'][$i - 1]['field_desc']) ? $params['rs_field'][$i - 1]['field_desc'] : '';
            $result[$i]['value'] = $rec;
            $i++;
        }

        // return
        return $result;
    }

    //read data excel
    public function read_excel($params) {
        // result
        $result = array();
        $arr_scope = array();
        $rs_file = array();
        $data = array();
        //read excelx
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $this->phpexcel = $objReader->load($params['file_path']);
        $objWorksheet = $this->phpexcel->setActiveSheetIndex(0);
        $sheetData = $objWorksheet->toArray(null, true, true, false);
        //--
        //array scope
        $start_baris = $params['input_detail']['input_row'];
        $total_baris = $start_baris + count($params['rs_rows']) - 1;
        for ($x = $start_baris; $x <= $total_baris; $x++) {
            $arr_scope[] = $x;
        }
        $j = 1;
        // read data
        if (is_file($params['file_path'])) {
            // read
            if (!empty($sheetData)) {
                foreach ($arr_scope as $i => $row) {
                    $row = $row - 1;
                    for ($key = 0; $key < $params['rs_rows'][$i]['total_column']; $key++) {
                        $rs_file[$i][$key] = isset($sheetData[$row][$key]) ? mb_convert_encoding($sheetData[$row][$key], "UTF-8", "ISO-8859-9") : '';
                        $j++;
                    }
                    // push with empty array if rs_file < total column
                    if ($params['rs_rows'][$i]['total_column'] != 0) {
                        if (count($rs_file[$i]) < $params['rs_rows'][$i]['total_column']) {
                            for ($index = count($rs_file[$i]) + 1; $index <= $params['rs_rows'][$i]['total_column']; $index++) {
                                array_push($rs_file[$i], '');
                                $j++;
                            }
                        }
                    }
                }

                $i = 1;
                $t = 0;
                foreach ($rs_file as $value) {
                    foreach ($value as $key => $val) {
                        $data[$t] = $val;
                        $t++;
                    }
                }
            }
            // array result
            foreach ($data as $rec) {
                $result[$i]['name'] = isset($params['rs_field'][$i - 1]['field_name']) ? $params['rs_field'][$i - 1]['field_name'] : '';
                $result[$i]['desc'] = isset($params['rs_field'][$i - 1]['field_desc']) ? $params['rs_field'][$i - 1]['field_desc'] : '';
                $result[$i]['value'] = $rec;
                $i++;
            }
        }

        return $result;
    }

    //read data excelx
    public function read_excelx($params) {
        // result
        $result = array();
        $arr_scope = array();
        $rs_file = array();
        $data = array();
        //read excelx
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $this->phpexcel = $objReader->load($params['file_path']);
        $objWorksheet = $this->phpexcel->setActiveSheetIndex(0);
        $sheetData = $objWorksheet->toArray(null, true, true, false);
        //--
        //array scope
        $start_baris = $params['input_detail']['input_row'];
        $total_baris = $start_baris + count($params['rs_rows']) - 1;
        for ($x = $start_baris; $x <= $total_baris; $x++) {
            $arr_scope[] = $x;
        }
        $j = 1;
        // read data
        if (is_file($params['file_path'])) {
            // read
            if (!empty($sheetData)) {
                foreach ($arr_scope as $i => $row) {
                    $row = $row - 1;
                    for ($key = 0; $key < $params['rs_rows'][$i]['total_column']; $key++) {
                        $rs_file[$i][$key] = isset($sheetData[$row][$key]) ? mb_convert_encoding($sheetData[$row][$key], "UTF-8", "ISO-8859-9") : '';
                        $j++;
                    }
                    // push with empty array if rs_file < total column
                    if ($params['rs_rows'][$i]['total_column'] != 0) {
                        // push with empty array if rs_file < total column
                        if (count($rs_file[$i]) < $params['rs_rows'][$i]['total_column']) {
                            for ($index = count($rs_file[$i]) + 1; $index <= $params['rs_rows'][$i]['total_column']; $index++) {
                                array_push($rs_file[$i], '');
                                $j++;
                            }
                        }
                    }
                }

                $i = 1;
                $t = 0;
                foreach ($rs_file as $value) {
                    foreach ($value as $key => $val) {
                        $data[$t] = $val;
                        $t++;
                    }
                }
            }
            // array result
            foreach ($data as $rec) {
                $result[$i]['name'] = isset($params['rs_field'][$i - 1]['field_name']) ? $params['rs_field'][$i - 1]['field_name'] : '';
                $result[$i]['desc'] = isset($params['rs_field'][$i - 1]['field_desc']) ? $params['rs_field'][$i - 1]['field_desc'] : '';
                $result[$i]['value'] = $rec;
                $i++;
            }
        }

        return $result;
    }

    public function read_default($params) {
        $i = 1;
        $result = array();
        foreach ($params['rs_field'] as $rec) {
            $result[$i]['name'] = isset($rec['field_name']) ? $rec['field_name'] : '';
            $result[$i]['desc'] = isset($rec['field_desc']) ? $rec['field_desc'] : '';
            $result[$i]['value'] = '';
            $i++;
        }
        return $result;
    }

}