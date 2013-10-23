<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class specialfield extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model("m_output");
        $this->load->model("m_specialfield");
        // load library
        $this->load->library('tnotification');
        $this->load->library("pagination");
        // set global variable
    }

    // list information update form
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "output/specialfield/list.html");
        // get search parameter
        $search = $this->session->userdata('session_search');
        if ($search['special_field_format_type'] == 'Date Format') {
            // set template content
            $this->smarty->assign("template_content", "output/specialfield/list_date.html");
        } else {
            // set template content
            $this->smarty->assign("template_content", "output/specialfield/list.html");
        }
        $this->smarty->assign("search", $search);
        $search_params = !empty($search['special_field_format_type']) ? $search['special_field_format_type'] : '';
        // get list input versions
        $rs_id = $this->m_output->get_list_version_by_type($search_params);
        $this->smarty->assign("rs_list", $rs_id);
        // get spesial field data
        $rs_list = $this->m_specialfield->get_all_cmfs_special_field_by_format($search_params);
        $this->smarty->assign("rs_field", $rs_list);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function process_search() {
        // set page rules
        $this->_set_page_rule("R");
        // cek input
        $this->tnotification->set_rules('search_type', 'Format Types');
        // process
        if ($this->tnotification->run() !== FALSE) {
            if ($this->input->post('search') == 'Display') {
                $search = array("special_field_format_type" => $this->input->post('search_type'));
                $this->session->set_userdata('session_search', $search);
            } else {
                $this->session->unset_userdata('session_search');
            }
        }
        // default redirect
        redirect("output/specialfield");
    }

    //show form add mcm special field
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/specialfield/add.html");
        $this->smarty->assign("url_process", site_url("output/specialfield/process_add"));
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    //process add mcm special field
    public function process_add() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('special_cd', 'Code', 'trim|required|max_length[10]');
        $this->tnotification->set_rules('special_nm', 'Name', 'trim|required|max_length[45]');
        $this->tnotification->set_rules('special_desc', 'Description', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('order_num', 'Order Number', 'trim|max_length[11]|numeric');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('special_cd'), $this->input->post('special_nm'),
                $this->input->post('special_desc'), intval($this->input->post('order_num')));
            // insert
            if ($this->m_specialfield->insert($params)) {
                // default notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // redirect
                redirect("output/specialfield/add/");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/specialfield/add");
    }

    //show form update mcm special field
    public function update($special_cd = '') {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "output/specialfield/update.html");
        $this->smarty->assign("url_process", site_url("output/specialfield/process_update"));
        // get mcm special field & assign
        $this->smarty->assign('result', $this->m_specialfield->get_detail_by_id($special_cd));
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    //process edit mcm special field
    public function process_update() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('special_cd', 'Code', 'trim|required|max_length[10]');
        $this->tnotification->set_rules('special_nm', 'Name', 'trim|required|max_length[45]');
        $this->tnotification->set_rules('special_desc', 'Description', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('order_num', 'Order Number', 'trim|max_length[11]|numeric');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('special_nm'), $this->input->post('special_desc'),
                intval($this->input->post('order_num')), $this->input->post('special_cd'));
            // insert
            if ($this->m_specialfield->update($params)) {
                // default notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // redirect
                redirect("output/specialfield/update/" . $this->input->post('special_cd'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/specialfield/update/" . $this->input->post('special_cd'));
    }

    //show form delete mcm special field
    public function delete($special_cd = '') {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->smarty->assign("template_content", "output/specialfield/delete.html");
        $this->smarty->assign("url_process", site_url("output/specialfield/process_delete"));
        // get mcm special field & assign
        $this->smarty->assign('result', $this->m_specialfield->get_detail_by_id($special_cd));
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    //process delete mcm special field
    public function process_delete() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('special_cd', 'Code', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // insert
            if ($this->m_specialfield->delete($this->input->post('special_cd'))) {
                // default notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // redirect
                redirect("output/specialfield");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/specialfield/delete" . $this->input->post('special_cd'));
    }

}