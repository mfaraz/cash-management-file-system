<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_download extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        // load other model
        $this->load->model('m_preferences');
        $this->load->model('m_convert');
        $this->load->model('m_utility');
        $this->load->library('tupload');
    }

    /*
     * Download to spesific file
     */

    // convert field
    public function export_file($result, $rs_baris, $transaksi, $prefix = "") {
        // switch type file
        switch ($result['output_file_type']) {
            case 'txt':
                $this->export_to_text($result, $rs_baris, $transaksi, $prefix);
                break;
            case 'csv':
                $this->export_to_csv($result, $rs_baris, $transaksi, $prefix);
                break;
            case 'xls':
                $this->export_to_excel($result, $rs_baris, $transaksi, $prefix);
                break;
            case 'xlsx':
                $this->export_to_excelx($result, $rs_baris, $transaksi, $prefix);
                break;
            default:
        }
    }

    // export to text file
    public function export_to_text($result, $rs_baris, $transaksi, $prefix = "") {
        $somecontent = "";
        foreach ($transaksi as $data) {
            $nr = 1;
            // create row
            foreach ($rs_baris as $baris) {
                $delimiter = "";
                for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                    $somecontent .= $delimiter;
                    $somecontent .= isset($data[$nr]['value']) ? $data[$nr]['value'] : '';
                    $delimiter = $result['output_delimiter'];
                    $nr++;
                }
                $somecontent .= "\n";
            }
        }
        // create file
        $file_name = $prefix . date("YmdHis") . '.txt';
        $date_time = date("Y-m-d H:i:s");
        $location = "resource/doc/converter/output/" . date("Ymd");
        if (!is_dir($location)) {
            $this->tupload->make_dir($location);
        }
        $location_file = $location . '/' . $file_name;
        if (is_file($location_file)) {
            unlink($location_file);
        }
        if (!$handle = fopen($location_file, 'a')) {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/singlemix/result/");
        }
        if (fwrite($handle, $somecontent) === FALSE) {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/singlemix/result/");
        }
        fclose($handle);
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        //--
        if ($encrypt_status['pref_value'] == 'yes') {
            $this->encyption_process($location, $file_name, $date_time, $result);
        } else {
            // download file
            if (is_file($location_file)) {
                // save to database before download
                // convert_file_input, convert_file_output, input_version, output_version, log_info
                $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                $this->m_convert->insert_history($params);
                // download
                header('Content-type: application/octet-stream');
                header("Content-Length:" . filesize($location_file));
                header("Content-Disposition: attachment; filename=" . $file_name);
                readfile($location_file);
                exit();
            }
        }
    }

    // export to csv file
    public function export_to_csv($result, $rs_baris, $transaksi, $prefix = "") {
        $somecontent = "";
        foreach ($transaksi as $data) {
            $nr = 1;
            // create row
            foreach ($rs_baris as $baris) {
                $delimiter = "";
                for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                    $somecontent .= $delimiter;
                    $somecontent .= isset($data[$nr]['value']) ? $data[$nr]['value'] : '';
                    $delimiter = $result['output_delimiter'];
                    $nr++;
                }
                $somecontent .= "\n";
            }
        }
        // create file
        $file_name = $prefix . date("YmdHis") . '.csv';
        $date_time = date("Y-m-d H:i:s");
        $location = "resource/doc/converter/output/" . date("Ymd");
        if (!is_dir($location)) {
            $this->tupload->make_dir($location);
        }
        $location_file = $location . '/' . $file_name;
        if (is_file($location_file)) {
            unlink($location_file);
        }
        if (!$handle = fopen($location_file, 'a')) {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/singlemix/result/");
        }
        if (fwrite($handle, $somecontent) === FALSE) {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/singlemix/result/");
        }
        fclose($handle);
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        //--
        if ($encrypt_status['pref_value'] == 'yes') {
            $this->encyption_process($location, $file_name, $date_time, $result);
        } else {
            // download file
            if (is_file($location_file)) {
                // save to database before download
                // convert_file_input, convert_file_output, input_version, output_version, log_info
                $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                $this->m_convert->insert_history($params);
                // download
                header('Content-type: application/octet-stream');
                header("Content-Length:" . filesize($location_file));
                header("Content-Disposition: attachment; filename=" . $file_name);
                readfile($location_file);
                exit();
            }
        }
    }

    // export to excel file
    public function export_to_excel($result, $rs_baris, $transaksi, $prefix = "") {
        //load library
        $this->load->library('phpexcel');
        if (!empty($transaksi)) {
            $column = "A";
            $row = 1;
            $length = array();
            foreach ($transaksi as $data) {
                $nr = 1;
                foreach ($rs_baris as $baris) {
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        if (empty($length[$column])) {
                            $length[$column] = 0;
                        }
                        $temp = strlen($data[$nr]['value']);
                        if ($temp > $length[$column]) {
                            $length[$column] = $temp;
                        }
                        $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($data[$nr]['value']) ? trim(mb_convert_encoding($data[$nr]['value'], "UTF-8", "ISO-8859-9")) : ''));
                        $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($data[$nr]['value']) ? trim(mb_convert_encoding($data[$nr]['value'], "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                        $nr++;
                        $column++;
                    }
                    $temp = 0;
                    $column = "A";
                    $row++;
                }
            }
            foreach ($length as $key => $value) {
                $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension($key)->setWidth($value + 3);
            }
            // download
            $file_name = $prefix . date("YmdHis") . '.xls';
            $date_time = date("Y-m-d H:i:s");
            $location = "resource/doc/converter/output/" . date("Ymd");
            if (!is_dir($location)) {
                $this->tupload->make_dir($location);
            }
            $location_file = $location . '/' . $file_name;
            // get status decryption
            $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
            //--
            if ($encrypt_status['pref_value'] == 'yes') {
                // output
                $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                $obj_writer->save($location_file);
                //--
                $this->encyption_process($location, $file_name, $date_time, $result);
            } else {
                // save to database before download
                // convert_file_input, convert_file_output, input_version, output_version, log_info
                $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                $this->m_convert->insert_history($params);
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename=' . $file_name);
                header('Cache-Control: max-age=0');
                // output
                $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                $obj_writer->save($location_file);
                readfile($location_file);
                exit();
            }
        }
    }

    // export to excel file
    public function export_to_excelx($result, $rs_baris, $transaksi, $prefix = "") {
        //load library
        $this->load->library('phpexcel');
        if (!empty($transaksi)) {
            $column = "A";
            $row = 1;
            $length = array();
            foreach ($transaksi as $data) {
                $nr = 1;
                foreach ($rs_baris as $baris) {
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        if (empty($length[$column])) {
                            $length[$column] = 0;
                        }
                        $temp = strlen($data[$nr]['value']);
                        if ($temp > $length[$column]) {
                            $length[$column] = $temp;
                        }
                        $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($data[$nr]['value']) ? trim(mb_convert_encoding($data[$nr]['value'], "UTF-8", "ISO-8859-9")) : ''));
                        $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($data[$nr]['value']) ? trim(mb_convert_encoding($data[$nr]['value'], "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                        $nr++;
                        $column++;
                    }
                    $temp = 0;
                    $column = "A";
                    $row++;
                }
            }
            foreach ($length as $key => $value) {
                $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension($key)->setWidth($value + 3);
            }
            // download
            $file_name = $prefix . date("YmdHis") . '.xlsx';
            $date_time = date("Y-m-d H:i:s");
            $location = "resource/doc/converter/output/" . date("Ymd");
            if (!is_dir($location)) {
                $this->tupload->make_dir($location);
            }
            $location_file = $location . '/' . $file_name;
            // get status decryption
            $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
            //--
            if ($encrypt_status['pref_value'] == 'yes') {
                // output
                $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                $obj_writer->save($location_file);
                //--
                $this->encyption_process($location, $file_name, $date_time, $result);
            } else {
                // save to database before download
                // convert_file_input, convert_file_output, input_version, output_version, log_info
                $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                $this->m_convert->insert_history($params);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename=' . $file_name);
                header('Cache-Control: max-age=0');
                // output
                $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                $obj_writer->save($location_file);
                readfile($location_file);
                exit();
            }
        }
    }

    //encryption
    public function encyption_process($location, $file_name, $date_time, $result) {
        //set path for encyption key
        $this->m_utility->set_path();
        //get recipient
        $rs_id = $this->m_preferences->get_preferences_by_group_name(array('gpg', 'recipient'));
        //base command
        $command = FCPATH . "system\plugins\gpgengine\gpg";
        //key file
        $pubring = FCPATH . 'resource\\doc\\encryption\\gpg\\key\\pubring.gpg';
        if (is_file($pubring)) {
            //encrypt gpg
            $plaintext = FCPATH . 'resource\\doc\\converter\\output\\' . date("Ymd") . '\\' . $file_name;
            $file = explode('.', $file_name);
            $encrypted_file = FCPATH . 'resource\\doc\\converter\\output\\' . date("Ymd") . '\\' . $file[0] . '.gpg';
            //get recipient from preferences
            $recipient = $rs_id['pref_value'];
            if (empty($recipient)) {
                // default redirect
                $this->tnotification->sent_notification("error", "Recipient not found");
                // redirect page
                redirect("converter/singlemix/result/");
            }
            //execute command batch
            exec("$command --always-trust --no-random-seed --recipient $recipient --output $encrypted_file --encrypt $plaintext");
            exec("taskkill /f /im gpg.exe");
            //download file
            $location_file = $location . '/' . $file[0] . '.gpg';
            if (is_file($encrypted_file)) {
                // save to database before download
                // convert_file_input, convert_file_output, input_version, output_version, log_info
                $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                $this->m_convert->insert_history($params);
                // download
                header('Content-type: application/octet-stream');
                header("Content-Length:" . filesize($encrypted_file));
                header("Content-Disposition: attachment; filename=" . $file[0] . '.gpg');
                readfile($encrypted_file);
                exit();
            }
        } else {
            //notification if fail
            $this->tnotification->set_error_message("Key File for Encryption not Found");
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/singlemix/result/");
        }
    }

}