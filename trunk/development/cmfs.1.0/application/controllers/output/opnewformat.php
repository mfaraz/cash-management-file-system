<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// controller for create new format
class opnewformat extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model("m_output");
        // load library
        $this->load->library('tnotification');
        $this->load->library("pagination");
    }

    //show form add for mcm output
    public function index($output_id = '') {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/opnewformat/create.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    /*
     * Process
    */

    // proses create
    public function process_create() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('output_version', 'Version', 'trim|required|max_length[30]');
        $this->tnotification->set_rules('output_format_type', 'Format Type', 'trim|required');
        $this->tnotification->set_rules('output_file_type', 'File Type', 'trim|required|max_length[30]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array($this->input->post('output_version'), $this->input->post('output_format_type'), $this->input->post('output_file_type'));
            // insert
            if ($this->m_output->insert($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // redirect if success
                redirect("output/opoutputversions/rows/" . $this->m_output->get_last_inserted_id());
            } else {
                // jika gagal (kembalikan pesan)
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/opnewformat");
    }
}