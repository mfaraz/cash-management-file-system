<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );
// --

class opnewformat extends ApplicationBase {

    // constructor
    public function   __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_input');
        $this->load->model('m_convert');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opnewformat/create.html");
        // get list output
        $this->smarty->assign("rs_output", $this->m_convert->get_all_output());
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    /*
     * Process
    */

    // process create
    public function process_create() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('input_version', 'Versi Format Input', 'trim|required|max_length[30]');
        $this->tnotification->set_rules('input_format_type', 'Jenis Format Input', 'trim|required');
        $this->tnotification->set_rules('input_file_type', 'Jenis File Input', 'trim|required');
        $this->tnotification->set_rules('output_id', 'Versi Format Output ', 'trim|required');
        $this->tnotification->set_rules('default_status', 'Default', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // cek output dan jenis format input
            $params = array($this->input->post('output_id'));
            $output = $this->m_convert->get_detail_output_by_id ($params);
            if(empty($output)) {
                $this->tnotification->set_error_message('Output format is not available');
                $this->tnotification->sent_notification("error", "Process fails");
                redirect("input/opnewformat");
            }
            if($output['output_format_type'] != $this->input->post('input_format_type')) {
                $this->tnotification->set_error_message('Output format is different from the input');
                $this->tnotification->sent_notification("error", "Process fails");
                redirect("input/opnewformat");
            }
            // update default status
            $params = array('no', $this->input->post('input_format_type'));
            $this->m_input->update_default_status($params);
            // parameter
            $params = array($this->input->post('output_id'), $this->input->post('input_version'), $this->input->post('input_format_type'),
                    $this->input->post('input_file_type'), $this->input->post('default_status'));
            // insert
            if ($this->m_input->insert($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // get last inserted id
                $id = $this->m_input->get_last_inserted_id ();
                // default redirect
                redirect("input/opinputversions/rows/" . $id);
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        }else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("input/opnewformat");
    }
}