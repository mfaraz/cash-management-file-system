<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// controller for list format
class cloutputversions extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model("m_output");
        $this->load->model("m_read_output");
        $this->load->model("m_specialfield");
        // load library
        $this->load->library('tnotification');
        // set global variable
    }

    // list information update form
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "output/cloutputversions/list.html");
        // get search parameter
        $search = $this->session->userdata('session_search');
        $this->smarty->assign("search", $search);
        $search_params = !empty($search['output_format_type']) ? $search['output_format_type'] : '%';
        // get list input versions
        $rs_id = $this->m_output->get_list_version_by_type($search_params);
        $this->smarty->assign("rs_list", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // review field
    public function review($output_id = "") {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "output/cloutputversions/review.html");
        // detail result
        $result = $this->m_output->get_detail_by_id($output_id);
        $this->smarty->assign("result", $result);
        // get output version field by output id
        $rs_output_field = $this->m_output->get_cmfs_output_field_by_id($output_id);
        $this->smarty->assign("rs_list", $rs_output_field);
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
        $this->tnotification->set_rules('search_type', 'Format Types');
        // process
        if ($this->tnotification->run() !== FALSE) {
            if ($this->input->post('search') == 'Display') {
                $search = array("output_format_type" => $this->input->post('search_type'));
                $this->session->set_userdata('session_search', $search);
            } else {
                $this->session->unset_userdata('session_search');
            }
        }
        // default redirect
        redirect("output/cloutputversions");
    }

}