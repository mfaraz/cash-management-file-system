<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberLoginBase.php' );

// --

class memberlogin extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load global
        $this->load->library('tnotification');
    }

    // view
    public function index($status = "") {
        // set template content
        $this->smarty->assign("template_content", "login/member/form.html");
        // bisnis proses
        if (!empty($this->com_user)) {
            // still login
            $this->smarty->assign("login_st", 'still');
        } else {
            $this->smarty->assign("login_st", $status);
        }
        // output
        parent::display();
    }

    // login process
    public function login_process() {
        // set rules
        $this->tnotification->set_rules('username', 'Username', 'trim|required');
        $this->tnotification->set_rules('pass', 'Password', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $username = trim($this->input->post('username'));
            $password = trim($this->input->post('pass'));
            // get user detail
            $result = $this->m_site->get_user_login($username, $password, $this->config->item('portal_member'));
            // check
            if (!empty($result)) {
                // cek lock status
                if ($result['lock_st'] == '1') {
                    // output
                    redirect('login/memberlogin/index/locked');
                }
                // set session
                $users = array("user_id" => $result['user_id'], "client_name" => $result['client_name'], "user_name" => $result['user_name'], "role_nm" => $result['role_nm'], "role_id" => $result['role_id']);
                $this->session->set_userdata('session_member', $users);
                // delete login time
                $this->m_site->delete_user_login_by_date($result['user_id']);
                // insert login time
                $params = array($result['user_id'], $_SERVER['REMOTE_ADDR']);
                $this->m_site->save_user_login($params);
                // redirect
                redirect('home/memberwelcome');
            } else {
                // output
                redirect('login/memberlogin/index/error');
            }
        } else {
            // default error
            redirect('login/memberlogin/index/error');
        }
        // output
        redirect('login/memberlogin/index/error');
    }

    // logout process
    public function logout_process() {
        // ci sessions
        $this->session->unset_userdata('session_member');
        $this->session->unset_userdata('session_search');
        // php session
        unset($_SESSION['batch_upload']);
        // output
        redirect('login/memberlogin');
    }

}