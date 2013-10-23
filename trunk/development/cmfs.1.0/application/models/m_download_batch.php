<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_download_batch extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        // preferences
        $this->load->model('m_preferences');
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
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        // get field mapping tanggal transaksi
        $index_date = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_id'));
        // get field mapping debitted account
        $index_account = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_da'));
        // get field mapping total amount
        $index_total = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_ta'));
        // get field mapping transfer amount
        $index_amount = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tra'));
        // get field number total record
        $index_record = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tr'));
        // transaksi di group kan berdasarkan 2 parameter khusus tersebut
        $group_transaksi = array();
        foreach ($transaksi as $data) {
            // get index value by param
            $tanggal = isset($data[$index_date]['value']) ? $data[$index_date]['value'] : date('Ymd');
            $account = isset($data[$index_account]['value']) ? $data[$index_account]['value'] : '1';
            if (!empty($tanggal) && !empty($account)) {
                $group_transaksi[$tanggal . $account][] = $data;
            }
        }
        // get total amount
        $arr_amount = array();
        $i = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $arr_amount[$i] = 0;
            $n = 1;
            $amount = 0;
            foreach ($detail_transaksi as $tran_data) {
                $amount = isset($tran_data[$index_amount]['value']) ? $tran_data[$index_amount]['value'] : 0;
                $arr_amount[$i] += number_format($amount, 2, '.', '');
                $n++;
            }
            $i++;
        }
        // loop transaksi
        $trans_index = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $start = true;
            $somecontent = '';
            foreach ($detail_transaksi as $data) {
                // index
                $nr = 1;
                $nd = 1;
                // create row
                foreach ($rs_baris as $baris) {
                    $delimiter = "";
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        // value
                        $value = isset($data[$nd]['value']) ? $data[$nd]['value'] : '';
                        // total record
                        if ($nd == $index_record) {
                            $value = count($detail_transaksi);
                        }
                        // total ammount
                        $total_amount = isset($arr_amount[$trans_index]) ? $arr_amount[$trans_index] : 0;
                        if ($nd == $index_total) {
                            $value = number_format($total_amount, 2, '.', '');
                        }
                        if ($nr == 2) {
                            $somecontent .= $delimiter . $value;
                            $delimiter = $result['output_delimiter'];
                        } elseif ($nr == 1) {
                            if ($start) {
                                $somecontent .= $delimiter . $value;
                                $delimiter = $result['output_delimiter'];
                            }
                        }
                        $nd++;
                    }
                    if ($nr == 1) {
                        if ($start) {
                            $somecontent .= "\n";
                        }
                    } else {
                        $somecontent .= "\n";
                    }
                    $nr++;
                    $start = false;
                }
            }
            $trans_index++;
            // pisah atau cuman satu
            //--
            if (count($group_transaksi) > 1) {
                // di copy kan ke folder output
                $folder_location = $this->m_preferences->get_preferences_by_group_name(array('folder', 'default_converter'));
                $folder_location = $folder_location['pref_value'];
                //--
                if (substr($folder_location, strlen($folder_location) - 1, 1) != "\\") {
                    $folder_location .= "\\";
                }
                if (!is_dir($folder_location)) {
                    if (!mkdir($folder_location, 0, true))
                        continue;
                }
                // di download banyak
                $location_file = "resource/doc/converter/temp/batch.txt";
                if (is_file($location_file)) {
                    unlink($location_file);
                }
                if (!$handle = fopen($location_file, 'a')) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                if (fwrite($handle, $somecontent) === FALSE) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                fclose($handle);
                //--
                if ($encrypt_status['pref_value'] == 'yes') {
                    $location = "resource/doc/converter/temp/";
                    $file_name = "batch.txt";
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.gpg';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.txt';
                    copy($location_file, $target_path);
                }
            } else {
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
                    redirect("converter/batchupload/result/");
                }
                if (fwrite($handle, $somecontent) === FALSE) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                fclose($handle);
                if ($encrypt_status['pref_value'] == 'yes') {
                    $target_path = '';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
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
        }
    }

    // export to csv file
    public function export_to_csv($result, $rs_baris, $transaksi, $prefix = "") {
        $somecontent = "";
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        //--
        // get field mapping tanggal transaksi
        $index_date = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_id'));
        // get field mapping debitted account
        $index_account = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_da'));
        // get field mapping total amount
        $index_total = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_ta'));
        // get field mapping transfer amount
        $index_amount = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tra'));
        // get field number total record
        $index_record = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tr'));
        // transaksi di group kan berdasarkan 2 parameter khusus tersebut
        $group_transaksi = array();
        foreach ($transaksi as $data) {
            // get index value by param
            $tanggal = isset($data[$index_date]['value']) ? $data[$index_date]['value'] : date('Ymd');
            $account = isset($data[$index_account]['value']) ? $data[$index_account]['value'] : '1';
            if (!empty($tanggal) && !empty($account)) {
                $group_transaksi[$tanggal . $account][] = $data;
            }
        }
        // get total amount
        $arr_amount = array();
        $i = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $arr_amount[$i] = 0;
            $n = 1;
            $amount = 0;
            foreach ($detail_transaksi as $tran_data) {
                $amount = isset($tran_data[$index_amount]['value']) ? $tran_data[$index_amount]['value'] : 0;
                $arr_amount[$i] += $amount;
                $n++;
            }
            $i++;
        }
        // loop transaksi
        $trans_index = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $start = true;
            $somecontent = '';
            foreach ($detail_transaksi as $data) {
                // index
                $nr = 1;
                $nd = 1;
                // create row
                foreach ($rs_baris as $baris) {
                    $delimiter = "";
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        // value
                        $value = isset($data[$nd]['value']) ? $data[$nd]['value'] : '';
                        // total record
                        if ($nd == $index_record) {
                            $value = count($detail_transaksi);
                        }
                        // total ammount
                        $total_amount = isset($arr_amount[$trans_index]) ? $arr_amount[$trans_index] : 0;
                        if ($nd == $index_total) {
                            $value = $total_amount;
                        }
                        if ($nr == 2) {
                            $somecontent .= $delimiter . $value;
                            $delimiter = $result['output_delimiter'];
                        } elseif ($nr == 1) {
                            if ($start) {
                                $somecontent .= $delimiter . $value;
                                $delimiter = $result['output_delimiter'];
                            }
                        }
                        $nd++;
                    }
                    if ($nr == 1) {
                        if ($start) {
                            $somecontent .= "\n";
                        }
                    } else {
                        $somecontent .= "\n";
                    }
                    $nr++;
                    $start = false;
                }
            }
            $trans_index++;
            // pisah atau cuman satu
            if (count($group_transaksi) > 1) {
                // di copy kan ke folder output
                $folder_location = $this->m_preferences->get_preferences_by_group_name(array('folder', 'default_converter'));
                $folder_location = $folder_location['pref_value'];
                //--
                if (substr($folder_location, strlen($folder_location) - 1, 1) != "\\") {
                    $folder_location .= "\\";
                }
                if (!is_dir($folder_location)) {
                    if (!mkdir($folder_location, 0, true))
                        continue;
                }
                // di download langsung
                $location_file = "resource/doc/converter/temp/batch.csv";
                if (is_file($location_file)) {
                    unlink($location_file);
                }
                if (!$handle = fopen($location_file, 'a')) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                if (fwrite($handle, $somecontent) === FALSE) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                fclose($handle);
                //--
                if ($encrypt_status['pref_value'] == 'yes') {
                    $location = "resource/doc/converter/temp/";
                    $file_name = "batch.csv";
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.gpg';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.csv';
                    copy($location_file, $target_path);
                }
            } else {
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
                    redirect("converter/batchupload/result/");
                }
                if (fwrite($handle, $somecontent) === FALSE) {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
                fclose($handle);
                if ($encrypt_status['pref_value'] == 'yes') {
                    $target_path = '';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
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
        }
    }

    // export to excel file
    public function export_to_excel($result, $rs_baris, $transaksi, $prefix = "") {
        //load library
        $this->load->library('phpexcel');
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        //--
        // group transaksi
        // get field mapping tanggal transaksi
        $index_date = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_id'));
        // get field mapping debitted account
        $index_account = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_da'));
        // get field mapping total amount
        $index_total = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_ta'));
        // get field mapping transfer amount
        $index_amount = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tra'));
        // get field number total record
        $index_record = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tr'));
        // transaksi di group kan berdasarkan 2 parameter khusus tersebut
        $group_transaksi = array();
        foreach ($transaksi as $data) {
            // get index value by param
            $tanggal = isset($data[$index_date]['value']) ? $data[$index_date]['value'] : date('Ymd');
            $account = isset($data[$index_account]['value']) ? $data[$index_account]['value'] : '1';
            if (!empty($tanggal) && !empty($account)) {
                $group_transaksi[$tanggal . $account][] = $data;
            }
        }
        // get total amount
        $arr_amount = array();
        $i = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $arr_amount[$i] = 0;
            $n = 1;
            $amount = 0;
            foreach ($detail_transaksi as $tran_data) {
                $amount = isset($tran_data[$index_amount]['value']) ? $tran_data[$index_amount]['value'] : 0;
                $arr_amount[$i] += $amount;
                $n++;
            }
            $i++;
        }
        // loop transaksi
        $trans_index = 1;
        $column = "A";
        $row = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $this->phpexcel = new PHPExcel();
            $start = true;
            foreach ($detail_transaksi as $data) {
                // index
                $nr = 1;
                $nd = 1;
                // create row
                foreach ($rs_baris as $baris) {
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        if (empty($length[$column])) {
                            $length[$column] = 0;
                        }
                        // value
                        $value = isset($data[$nd]['value']) ? $data[$nd]['value'] : '';
                        // total record
                        if ($nd == $index_record) {
                            $value = count($detail_transaksi);
                        }
                        // total ammount
                        $total_amount = isset($arr_amount[$trans_index]) ? $arr_amount[$trans_index] : 0;
                        if ($nd == $index_total) {
                            $value = $total_amount;
                        }
                        if ($nr == 2) {
                            $temp = strlen($value);
                            if ($temp > $length[$column]) {
                                $length[$column] = $temp;
                            }
                            $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''));
                            $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                        } elseif ($nr == 1) {
                            if ($start) {
                                $temp = strlen($value);
                                if ($temp > $length[$column]) {
                                    $length[$column] = $temp;
                                }
                                $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''));
                                $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                            }
                        }

                        $column++;
                        $nd++;
                    }
                    if ($nr == 1) {
                        if ($start) {
                            $row++;
                        }
                    } else {
                        $row++;
                    }
                    $temp = 0;
                    $start = false;
                    $column = "A";
                    $nr++;
                }
            }
            $trans_index++;
            $column = "A";
            $row = 1;
            foreach ($length as $key => $value) {
                $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension($key)->setWidth($value + 3);
            }
            // pisah atau cuman satu
            if (count($group_transaksi) > 1) {
                // di copy kan ke folder output
                $folder_location = $this->m_preferences->get_preferences_by_group_name(array('folder', 'default_converter'));
                $folder_location = $folder_location['pref_value'];
                //--
                if (substr($folder_location, strlen($folder_location) - 1, 1) != "\\") {
                    $folder_location .= "\\";
                }
                if (!is_dir($folder_location)) {
                    if (!mkdir($folder_location, 0, true))
                        continue;
                }
                if ($encrypt_status['pref_value'] == 'yes') {
                    $location = "resource/doc/converter/temp/";
                    $file_name = "batch.xls";
                    $location_file = $location . '/' . $file_name;
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                    $obj_writer->save("$location_file");
                    //--
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.gpg';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    // copy file
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.xls';
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                    $obj_writer->save("$target_path");
                }
            } else {
                $file_name = $prefix . date("YmdHis") . '.xls';
                $date_time = date("Y-m-d H:i:s");
                $location = "resource/doc/converter/output/" . date("Ymd");
                if (!is_dir($location)) {
                    $this->tupload->make_dir($location);
                }
                $location_file = $location . '/' . $file_name;
                if ($encrypt_status['pref_value'] == 'yes') {
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                    $obj_writer->save($location_file);
                    //--
                    $target_path = '';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    // download
                    // convert_file_input, convert_file_output, input_version, output_version, log_info
                    $params = array($date_time, $_SESSION['converter_file'], $location_file, $result['input_version'], $result['output_version'], '');
                    $this->m_convert->insert_history($params);
                    //--
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
    }

    // export to excel file
    public function export_to_excelx($result, $rs_baris, $transaksi, $prefix = "") {
        //load library
        $this->load->library('phpexcel');
        // get status decryption
        $encrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        //--
        // group transaksi
        // get field mapping tanggal transaksi
        $index_date = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_id'));
        // get field mapping debitted account
        $index_account = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_da'));
        // get field mapping total amount
        $index_total = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_ta'));
        // get field mapping transfer amount
        $index_amount = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tra'));
        // get field number total record
        $index_record = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tr'));
        // transaksi di group kan berdasarkan 2 parameter khusus tersebut
        $group_transaksi = array();
        foreach ($transaksi as $data) {
            // get index value by param
            $tanggal = isset($data[$index_date]['value']) ? $data[$index_date]['value'] : date('Ymd');
            $account = isset($data[$index_account]['value']) ? $data[$index_account]['value'] : '1';
            if (!empty($tanggal) && !empty($account)) {
                $group_transaksi[$tanggal . $account][] = $data;
            }
        }
        // get total amount
        $arr_amount = array();
        $i = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $arr_amount[$i] = 0;
            $n = 1;
            $amount = 0;
            foreach ($detail_transaksi as $tran_data) {
                $amount = isset($tran_data[$index_amount]['value']) ? $tran_data[$index_amount]['value'] : 0;
                $arr_amount[$i] += $amount;
                $n++;
            }
            $i++;
        }
        // loop transaksi
        $trans_index = 1;
        $column = "A";
        $row = 1;
        foreach ($group_transaksi as $detail_transaksi) {
            $this->phpexcel = new PHPExcel();
            $start = true;
            foreach ($detail_transaksi as $data) {
                // index
                $nr = 1;
                $nd = 1;
                // create row
                foreach ($rs_baris as $baris) {
                    for ($i = 0; $i <= ($baris['total_column'] - 1); $i++) {
                        if (empty($length[$column])) {
                            $length[$column] = 0;
                        }
                        // value
                        $value = isset($data[$nd]['value']) ? $data[$nd]['value'] : '';
                        // total record
                        if ($nd == $index_record) {
                            $value = count($detail_transaksi);
                        }
                        // total ammount
                        $total_amount = isset($arr_amount[$trans_index]) ? $arr_amount[$trans_index] : 0;
                        if ($nd == $index_total) {
                            $value = $total_amount;
                        }
                        if ($nr == 2) {
                            $temp = strlen($value);
                            if ($temp > $length[$column]) {
                                $length[$column] = $temp;
                            }
                            $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''));
                            $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                        } elseif ($nr == 1) {
                            if ($start) {
                                $temp = strlen($value);
                                if ($temp > $length[$column]) {
                                    $length[$column] = $temp;
                                }
                                $this->phpexcel->setActiveSheetIndex(0)->setCellValue($column . $row, (isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''));
                                $this->phpexcel->setActiveSheetIndex(0)->getCell($column . $row)->setValueExplicit((isset($value) ? trim(mb_convert_encoding($value, "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                            }
                        }

                        $column++;
                        $nd++;
                    }
                    if ($nr == 1) {
                        if ($start) {
                            $row++;
                        }
                    } else {
                        $row++;
                    }
                    $temp = 0;
                    $start = false;
                    $column = "A";
                    $nr++;
                }
            }
            $trans_index++;
            $column = "A";
            $row = 1;
            foreach ($length as $key => $value) {
                $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension($key)->setWidth($value + 3);
            }
            // pisah atau cuman satu
            if (count($group_transaksi) > 1) {
                // di copy kan ke folder output
                $folder_location = $this->m_preferences->get_preferences_by_group_name(array('folder', 'default_converter'));
                $folder_location = $folder_location['pref_value'];
                //--
                if (substr($folder_location, strlen($folder_location) - 1, 1) != "\\") {
                    $folder_location .= "\\";
                }
                if (!is_dir($folder_location)) {
                    if (!mkdir($folder_location, 0, true))
                        continue;
                }
                if ($encrypt_status['pref_value'] == 'yes') {
                    $location = "resource/doc/converter/temp/";
                    $file_name = "batch.xlsx";
                    $location_file = $location . '/' . $file_name;
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                    $obj_writer->save("$location_file");
                    //--
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.gpg';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    // copy file
                    $target_path = $folder_location . $prefix . date("Ymds") . '-' . ($trans_index - 1) . '.xlsx';
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                    $obj_writer->save("$target_path");
                }
            } else {
                $file_name = $prefix . date("YmdHis") . '.xlsx';
                $date_time = date("Y-m-d H:i:s");
                $location = "resource/doc/converter/output/" . date("Ymd");
                if (!is_dir($location)) {
                    $this->tupload->make_dir($location);
                }
                $location_file = $location . '/' . $file_name;
                if ($encrypt_status['pref_value'] == 'yes') {
                    // output
                    $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                    $obj_writer->save($location_file);
                    //--
                    $target_path = '';
                    $this->encyption_process($target_path, $location, $file_name, $date_time, $result, count($group_transaksi));
                } else {
                    // download
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
    }

    //encryption
    public function encyption_process($target_path, $location, $file_name, $date_time, $result, $count_trx) {
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
            if ($count_trx > 1) {
                $plaintext = FCPATH . 'resource\\doc\\converter\\temp\\' . $file_name;
                $file = explode('.', $file_name);
                $encrypted_file = FCPATH . 'resource\\doc\\converter\\temp\\' . $file[0] . '.gpg';
                if (is_file($encrypted_file)) {
                    unlink($encrypted_file);
                }
            } else {
                $plaintext = FCPATH . 'resource\\doc\\converter\\output\\' . date("Ymd") . '\\' . $file_name;
                $file = explode('.', $file_name);
                $encrypted_file = FCPATH . 'resource\\doc\\converter\\output\\' . date("Ymd") . '\\' . $file[0] . '.gpg';
            }
            //get recipient from preferences
            $recipient = $rs_id['pref_value'];
            if (empty($recipient)) {
                // default redirect
                $this->tnotification->sent_notification("error", "Recipient not found");
                // redirect page
                redirect("converter/batchupload/result/");
            }
            //execute command batch
            exec("$command --always-trust --no-random-seed --recipient $recipient --output $encrypted_file --encrypt $plaintext");
            exec("taskkill /f /im gpg.exe");
            //download file
            $location_file = $location . '/' . $file[0] . '.gpg';
            if ($count_trx > 1) {
                copy($location_file, $target_path);
            } else {
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
            }
        } else {
            //notification if fail
            $this->tnotification->set_error_message("Key File for Encryption not Found");
            $this->tnotification->sent_notification("error", "Process fails");
            // redirect page
            redirect("converter/batchupload/result/");
        }
    }

}