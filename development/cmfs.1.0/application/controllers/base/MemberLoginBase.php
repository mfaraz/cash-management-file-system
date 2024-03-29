<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class ApplicationBase extends CI_Controller {

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;

    public function __construct() {
        // load basic controller
        parent::__construct();
        // load app data
        $this->base_load_app();
        // view app data
        $this->base_view_app();
    }

    /*
     * Method pengolah base load
     * diperbolehkan untuk dioverride pada class anaknya
    */

    protected function base_load_app() {
        // load themes (themes default : default)
        $this->smarty->load_themes("login");
        // load base models
        // load javascript
        // load style
    }

    /*
     * Method pengolah base view
     * diperbolehkan untuk dioverride pada class anaknya
    */

    protected function base_view_app() {
        $this->smarty->assign("config", $this->config);
        // display site title
        self::_display_site_title();
        // get session member
        self::_get_member_session();
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
    */

    protected function display($tmpl_name = 'base/member/document-login.html') {
        // set template
        $this->smarty->display($tmpl_name);
    }

    //
    // base private method here
    // prefix ( _ )
    // site title
    private function _display_site_title() {
        $this->portal_id = $this->config->item('portal_member');
        // site data
        $this->com_portal = $this->m_site->get_site_data_by_id($this->portal_id);
        if (!empty($this->com_portal)) {
            $this->smarty->assign("site", $this->com_portal);
        }
    }

    // get session member
    private function _get_member_session() {
        // session member
        $this->com_user = $this->session->userdata('session_member');
    }

}

