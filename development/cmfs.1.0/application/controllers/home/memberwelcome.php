<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --
class memberwelcome extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load global
        $this->load->model('m_preferences');
        $this->load->model('m_convert');
        $this->load->library('tnotification');
        $this->load->library('pagination');
    }

    // view
    public function index() {
        // set template content
        $this->smarty->assign("template_content", "home/member/welcome.html");
        // load javascript
        $this->smarty->load_javascript('resource/js/jquery/jquery-ui-1.8.13.custom.min.js');
        // convert history
        $params = array(0, 50);
        $rs_id = $this->m_convert->get_all_convert_history($params);
        $this->smarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // process create
    public function process_delete() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('history', 'History not selected', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $history = $this->input->post('history');
            // update default status
            foreach ($history as $params) {
                // get detail by date
                $result = $this->m_convert->get_detail_history($params);
                if (!empty($result)) {
                    $this->m_convert->delete_history($params);
                    if (!empty($result['convert_file_input'])) {
                        $raw_name = explode('.', $result['convert_file_input']);
                        $gpg_file = $raw_name[0] . '.gpg';
                        if (is_file($gpg_file)) {
                            unlink($gpg_file);
                        }
                    }
                    // unlink
                    if (is_file($result['convert_file_input'])) {
                        unlink($result['convert_file_input']);
                    }
                    if (is_file($result['convert_file_output'])) {
                        unlink($result['convert_file_output']);
                    }
                }
            }
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data deleted successfully");
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("home/memberwelcome");
    }

}