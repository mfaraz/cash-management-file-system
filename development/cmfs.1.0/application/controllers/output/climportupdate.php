<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class climportupdate extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_output');
        $this->load->model('m_importupdates');
        $this->load->model('m_specialfield');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "output/climportupdate/index.html");
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // update
    public function update($file_name = '') {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "output/climportupdate/update.html");
        // plugins
        require_once( BASEPATH . 'plugins/excelreader/excelreader.php');
        // read data
        $file_path = "resource/doc/importupdate/output/" . $file_name . '.xls';
        //assign rs output
        $this->smarty->assign('file_name', $file_name);
        //process
        $output_id = '';
        $rows = array();
        $fields = array();
        $rs_output = array();
        if (is_file($file_path)) {
            // load excel reader
            $obj_excel_reader = new Spreadsheet_Excel_Reader();
            $obj_excel_reader->setOutputEncoding('CP1251');
            // read excel
            $obj_excel_reader->read($file_path);
            // read
            if (!empty($obj_excel_reader->sheets)) {
                foreach ($obj_excel_reader->sheets as $key => $val) {
                    if ($key == 0) {
                        $rs_output =
                                array(
                                    !empty($val['cells']['4']['3']) ? $val['cells']['4']['3'] : '-',
                                    !empty($val['cells']['5']['3']) ? $val['cells']['5']['3'] : '-',
                                    !empty($val['cells']['6']['3']) ? $val['cells']['6']['3'] : '-',
                                    !empty($val['cells']['7']['3']) ? $val['cells']['7']['3'] : '-',
                                    !empty($val['cells']['8']['3']) ? $val['cells']['8']['3'] : '-',
                                    !empty($val['cells']['9']['3']) ? $val['cells']['9']['3'] : '-'
                        );
                        //insert row
                        foreach ($val['cells'] as $k => $row) {
                            if ($k >= 13) {
                                $rows[$k] =
                                        array(
                                            !empty($val['cells']['4']['3']) ? $val['cells']['4']['3'] : '-',
                                            !empty($row['2']) ? $row['2'] : '-',
                                            !empty($row['3']) ? $row['3'] : '-'
                                );
                            }
                        }
                    }
                    if ($key == 1) {
                        foreach ($val['cells'] as $key => $value) {
                            if ($key >= 3) {
                                $fields[$key] =
                                        array(
                                            !empty($value['2']) ? $value['2'] : '-',
                                            !empty($value['8']) ? $value['8'] : '-',
                                            !empty($value['3']) ? $value['3'] : '-',
                                            !empty($value['4']) ? $value['4'] : '-',
                                            !empty($value['5']) ? $value['5'] : '-',
                                            !empty($value['6']) ? $value['6'] : '-',
                                            !empty($value['7']) ? $value['7'] : '-',
                                            !empty($value['9']) ? $value['9'] : '-'
                                );
                            }
                        }
                    }
                }
            }
        } else {
            // jika gagal
            $this->tnotification->set_error_message('File not Found');
            $this->tnotification->sent_notification("error", "Process fails");
        }
        //assign rs output
        $this->smarty->assign('rs_output', $rs_output);
        //assign rows
        $this->smarty->assign('rows', $rows);
        //assign field
        $this->smarty->assign('fields', $fields);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // index
    public function process_update() {
        // set page rules
        $this->_set_page_rule("U");
        // plugins
        require_once( BASEPATH . 'plugins/excelreader/excelreader.php');
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'trim|required');
        if ($this->tnotification->run() !== FALSE) {
            // read data
            $file_path = "resource/doc/importupdate/output/" . $this->input->post('file_name') . '.xls';
            if (is_file($file_path)) {
                // load excel reader
                $obj_excel_reader = new Spreadsheet_Excel_Reader();
                $obj_excel_reader->setOutputEncoding('CP1251');
                // read excel
                $obj_excel_reader->read($file_path);
                // read
                $output_id = '';
                if (!empty($obj_excel_reader->sheets)) {
                    foreach ($obj_excel_reader->sheets as $key => $val) {
                        if ($key == 0) {
                            if (!empty($val['cells']['4']['3']) && !empty($val['cells']['5']['3']) && !empty($val['cells']['6']['3'])) {
                                if (is_numeric($val['cells']['4']['3'])) {
                                    $output_id = $val['cells']['4']['3'];
                                } else {
                                    // jika gagal
                                    $this->tnotification->set_error_message('File Format does not match');
                                    $this->tnotification->sent_notification("error", "Process fails");
                                    //-- default redirect
                                    redirect("output/opimportupdate");
                                }
                                $params = array(
                                    $val['cells']['4']['3'],
                                    $val['cells']['5']['3'],
                                    $val['cells']['6']['3'],
                                    !empty($val['cells']['7']['3']) ? $val['cells']['7']['3'] : '',
                                    !empty($val['cells']['8']['3']) ? $val['cells']['8']['3'] : '',
                                    !empty($val['cells']['9']['3']) ? $val['cells']['9']['3'] : ''
                                );
                                //delete
                                $this->m_output->delete($val['cells']['4']['3']);
                                //insert
                                $this->m_output->insert_update($params);
                                //insert row
                                foreach ($val['cells'] as $k => $row) {
                                    if ($k >= 13) {
                                        if (!empty($row['2']) && !empty($row['3'])) {
                                            $params = array(
                                                $val['cells']['4']['3'], $row['2'], $row['3']);
                                            $this->m_output->insert_rows($params);
                                        }
                                    }
                                }
                            } else {
                                // jika gagal
                                $this->tnotification->set_error_message('File Format does not match');
                                $this->tnotification->sent_notification("error", "Process fails");
                                //-- default redirect
                                redirect("output/climportupdate");
                            }
                        }
                        if ($key == 1) {
                            foreach ($val['cells'] as $key => $value) {
                                if ($key >= 3) {
                                    if (!empty($output_id) && !empty($value['2']) && !empty($value['8'])) {
                                        $params = array(
                                            $output_id,
                                            $value['2'],
                                            $value['8'],
                                            !empty($value['3']) ? trim(mb_convert_encoding($value['3'], "UTF-8", "ISO-8859-9")) : '',
                                            !empty($value['4']) ? trim(mb_convert_encoding($value['4'], "UTF-8", "ISO-8859-9")) : '',
                                            !empty($value['5']) ? $value['5'] : '',
                                            !empty($value['6']) ? intval($value['6']) : 0,
                                            !empty($value['7']) ? $value['7'] : '',
                                            !empty($value['9']) ? trim(mb_convert_encoding($value['9'], "UTF-8", "ISO-8859-9")) : ''
                                        );
                                        $this->m_output->insert_output($params);
                                    }
                                }
                            }
                        }
                        //default success
                        $this->tnotification->sent_notification("success", "Data saved successfully");
                    }
                } else {
                    // default error
                    $this->tnotification->set_error_message('File is empty');
                    $this->tnotification->sent_notification("error", "Process fails");
                }
                if (is_file($file_path)) {
                    unlink($file_path);
                }
            } else {
                // jika gagal
                $this->tnotification->set_error_message('File not Found');
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        //-- default redirect
        redirect("output/climportupdate");
    }

    // process import updates
    public function process_upload() {
        // set page rules
        $this->_set_page_rule("C");
        // load
        $this->load->library('tupload');
        // cek file
        if (empty($_FILES['upload_file']['tmp_name'])) {
            $this->tnotification->set_error_message('File not found');
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // upload
        if (!empty($_FILES['upload_file']['tmp_name'])) {
            // upload config
            $config['upload_path'] = 'resource/doc/importupdate/output/';
            $config['allowed_types'] = 'xls';
            $config['file_name'] = date("Ymds");
            $this->tupload->initialize($config);
            // process upload
            if ($this->tupload->do_upload('upload_file')) {
                //default notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data uploaded successfully");
                //redirect if succes upload
                redirect("output/climportupdate/update/" . $config['file_name']);
            } else {
                // jika gagal
                $this->tnotification->set_error_message($this->tupload->display_errors());
                $this->tnotification->sent_notification("error", "Process fails");
            }
        }
        //-- default redirect
        redirect("output/climportupdate");
    }

}