<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class bank extends ApplicationBase {

    // constructor
    public function __construct() {
        //-- parent constructor
        parent::__construct();
        //-- load
        $this->load->model('m_bank');
        $this->load->library('tnotification');
        $this->load->library('pagination');
    }

    //-- index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "master/data/bank/list.html");
        // get search parameter
        $search = $this->session->userdata('search_bank_name');
        $this->smarty->assign("search_bank_name", $search);
        $search = !empty($search) ? ('%' . $search . '%') : '%';
        /* start of pagination --------------------- */
        $config['base_url'] = site_url("master/bank/index");
        $config['total_rows'] = $this->m_bank->get_total_bank_nasional(array($search, $search));
        $config['uri_segment'] = 4;
        $config['per_page'] = 50;
        $this->pagination->initialize($config);
        $pagination['data'] = $this->pagination->create_links();
        // pagination parameter
        $start = $this->uri->segment(4, 0) + 1;
        $end = $this->uri->segment(4, 0) + $config['per_page'];
        $end = (($end > $config['total_rows']) ? $config['total_rows'] : $end);
        $pagination['start'] = empty($config['total_rows']) ? 0 : $start;
        $pagination['end'] = $end;
        $pagination['total'] = $config['total_rows'];
        // pagination assign value
        $this->smarty->assign("pagination", $pagination);
        $this->smarty->assign("no", $start);
        /* end of pagination ---------------------- */
        // get data
        $params = array($search, $search, intval($this->uri->segment(4, 0)), $config['per_page']);
        $this->smarty->assign("rs_id", $this->m_bank->get_list_bank_nasional($params));
        // export path
        $file_path = "resource/excel/bank/data_bank.xls";
        if (is_file($file_path)) {
            $this->smarty->assign("file_export", "show");
        } else {
            $this->smarty->assign("file_export", "hide");
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // import
    public function import() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "master/data/bank/import.html");
        //--
        $file_path = "resource/excel/bank/data_bank.xls";
        if (is_file($file_path)) {
            $this->smarty->assign("file_export", "show");
        } else {
            $this->smarty->assign("file_export", "hide");
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    /*
     * Process
     */

    // search process
    public function process_search() {
        // set page rules
        $this->_set_page_rule("R");
        // cek input
        $this->tnotification->set_rules('search_bank_name', 'Nama Bank / Keyword');
        // process
        if ($this->tnotification->run() !== FALSE) {
            if ($this->input->post('search') == 'View') {
                $this->session->set_userdata('search_bank_name', $this->input->post('search_bank_name'));
            } else {
                $this->session->unset_userdata('search_bank_name');
            }
        }
        // default redirect
        redirect("master/bank");
    }

    // process import
    public function process_import() {
        // set page rules
        $this->_set_page_rule("C");
        // load
        $this->load->library('tupload');
        // plugins
        require_once( BASEPATH . 'plugins/excelreader/excelreader.php');
        // cek file
        if (empty($_FILES['import_file']['tmp_name'])) {
            $this->tnotification->set_error_message('File not found');
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // upload
        if (!empty($_FILES['import_file']['tmp_name'])) {
            // upload config
            $config['upload_path'] = 'resource/excel/bank/';
            $config['allowed_types'] = 'xls';
            $config['file_name'] = 'data_bank';
            $this->tupload->initialize($config);
            // process upload
            if ($this->tupload->do_upload('import_file')) {
                $uploaded = $this->tupload->data();
                $data = array();
                // read data
                $file_path = "resource/excel/bank/" . $uploaded['orig_name'];
                if (is_file($file_path)) {
                    // load excel reader
                    $obj_excel_reader = new Spreadsheet_Excel_Reader();
                    $obj_excel_reader->setOutputEncoding('UTF-8');
                    // read excel
                    $obj_excel_reader->read($file_path);
                    // read
                    if (!empty($obj_excel_reader->sheets)) {
                        $index = 1;
                        foreach ($obj_excel_reader->sheets[0]['cells']as $key => $val) {
                            if ($key >= 4) {
                                $data[$index] = isset($val) ? $val : '';
                                $index++;
                            }
                        };
                    };
                }
                if (!empty($data)) {
                    //delete all data
                    $this->m_bank->delete_all();
                    // insert
                    $bank_id = 1;
                    foreach ($data as $value) {
                        $params = array($bank_id,
                            trim(mb_convert_encoding(isset($value[2]) ? substr($value[2], 0, 100) : '', "UTF-8", "ISO-8859-9")),
                            trim(mb_convert_encoding(isset($value[3]) ? substr($value[3], 0, 100) : '', "UTF-8", "ISO-8859-9")),
                            isset($value[4]) ? trim(mb_convert_encoding(substr($value[4], 0, 50), "UTF-8", "ISO-8859-9")) : '',
                            isset($value[5]) ? trim(mb_convert_encoding(substr($value[5], 0, 50), "UTF-8", "ISO-8859-9")) : '',
                            isset($value[6]) ? trim(mb_convert_encoding(substr($value[6], 0, 50), "UTF-8", "ISO-8859-9")) : ''
                        );
                        //-- insert
                        if (!empty($value[2])) {
                            if ($this->m_bank->insert($params)) {
                                $this->tnotification->delete_last_field();
                                $this->tnotification->sent_notification("success", "Data saved successfully");
                            } else {
                                // default error
                                $this->tnotification->sent_notification("error", "Process fails");
                            }
                        }
                        $bank_id++;
                    }
                }
            } else {
                // jika gagal
                $this->tnotification->set_error_message($this->tupload->display_errors());
                $this->tnotification->sent_notification("error", "Process fails");
            }
        }
        // default redirect
        redirect("master/bank/import");
    }

    // process export
    public function process_export() {
        // set page rules
        $this->_set_page_rule("C");
        // file path
        $file_path = "resource/excel/bank/data_bank.xls";
        // read data
        if (is_file($file_path)) {
            header('Content-type: application/vnd.ms-excel');
            header("Content-Length:" . filesize($file_path));
            header("Content-Disposition: attachment; filename=bank.xls");
            readfile($file_path);
            exit();
        } else {
            // default redirect
            redirect("master/bank/");
        }
    }

}