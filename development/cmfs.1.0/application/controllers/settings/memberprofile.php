<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class memberprofile extends ApplicationBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load
        $this->load->model('m_user');
        $this->load->model('m_preferences');
        $this->load->library('encrypt');
        $this->load->library('tnotification');
    }

    // edit profile
    public function index() {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "settings/profile/edit_member.html");
        // get data
        $result = $this->m_user->get_detail_user_by_id($this->com_user['user_id']);
        if (!empty($result)) {
            $result['user_pass'] = $this->encrypt->decode($result['user_pass'], $result['user_key']);
            $this->smarty->assign("result", $result);
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit process
    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('user_mail', 'User Email', 'trim|required|valid_email|max_length[50]');
        $this->tnotification->set_rules('user_name', 'User Name', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('user_pass', 'Password', 'trim|required|max_length[50]');

        $this->tnotification->set_rules('client_name', 'Password', 'trim|max_length[45]');
        $this->tnotification->set_rules('client_phone', 'Password', 'trim|max_length[45]');
        $this->tnotification->set_rules('client_address', 'Password', 'trim|max_length[100]');

        // check email
        $email = trim($this->input->post('user_mail'));
        if ($this->input->post('user_mail') != $this->input->post('user_mail_old')) {
            if ($this->m_user->is_exist_email($email)) {
                $this->tnotification->set_error_message('Email is not available');
            }
        }
        // check username
        $username = trim($this->input->post('user_name'));
        if ($this->input->post('user_name') != $this->input->post('user_name_old')) {
            if ($this->m_user->is_exist_username($username)) {
                $this->tnotification->set_error_message('Username is not available');
            }
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $password_key = crc32($this->input->post('user_pass'));
            $password = $this->encrypt->encode($this->input->post('user_pass'), $password_key);
            // parameter
            $params = array($this->input->post('user_name'), $password, $password_key, '0', $this->input->post('user_mail'),
                $this->com_user['user_id'], $this->com_user['user_id']);
            // update
            if ($this->m_user->update($params)) {
                $params = array($this->input->post('client_name'), $this->input->post('client_phone'), $this->input->post('client_address'), $this->com_user['user_id']);
                //update client information
                $this->m_preferences->update_client_info($params);
                //--
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("settings/memberprofile/");
    }

}