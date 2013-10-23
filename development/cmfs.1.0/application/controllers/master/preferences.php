<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class preferences extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_preferences');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "master/data/preferences/index.html");
        // Special karakter
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'special_char'));
        $special_char_rep = str_replace('"', '&quot;', $result['pref_value']);
        $this->smarty->assign("special_char", $special_char_rep);
        // Nominal Parameter
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'nominal_parameter'));
        $this->smarty->assign("nominal_parameter", $result['pref_value']);
        // Bank Name Parameter
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'bank_name'));
        $this->smarty->assign("bank_name", $result['pref_value']);
        // Inhouse Code
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'inhouse_code'));
        $this->smarty->assign("inhouse_code", $result['pref_value']);
        // Clearing Code
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'clearing_code'));
        $this->smarty->assign("clearing_code", $result['pref_value']);
        // RTGS code
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'rtgs_code'));
        $this->smarty->assign("rtgs_code", $result['pref_value']);
        // Intenational Code
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'international_code'));
        $this->smarty->assign("international_code", $result['pref_value']);
        //--
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
        $this->tnotification->set_rules('special_char', 'Remove Special Character', 'trim|required');
        $this->tnotification->set_rules('nominal_parameter', 'Nominal Parameter', 'trim|required');
        $this->tnotification->set_rules('bank_name', 'Bank Name Limit', 'trim|required');
        $this->tnotification->set_rules('inhouse_code', 'Inhouse Code', 'trim|required');
        $this->tnotification->set_rules('clearing_code', 'Clearing Code', 'trim|required');
        $this->tnotification->set_rules('rtgs_code', 'RTGS Code', 'trim|required');
        $this->tnotification->set_rules('international_code', 'International Code', 'trim|required');

        // get preferences
        $special_char = $this->m_preferences->get_preferences_by_group_name(array('settings', 'special_char'));
        $nominal_parameter = $this->m_preferences->get_preferences_by_group_name(array('settings', 'nominal_parameter'));
        $bank_name = $this->m_preferences->get_preferences_by_group_name(array('settings', 'bank_name'));

        $inhouse_code = $this->m_preferences->get_preferences_by_group_name(array('settings', 'inhouse_code'));
        $clearing_code = $this->m_preferences->get_preferences_by_group_name(array('settings', 'clearing_code'));
        $rtgs_code = $this->m_preferences->get_preferences_by_group_name(array('settings', 'rtgs_code'));
        $international_code = $this->m_preferences->get_preferences_by_group_name(array('settings', 'international_code'));
        //process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array(
                array('settings', 'special_char', $this->input->post('special_char'), $this->com_user['user_id'], $special_char['pref_id']),
                array('settings', 'nominal_parameter', $this->input->post('nominal_parameter'), $this->com_user['user_id'], $nominal_parameter['pref_id']),
                array('settings', 'bank_name', $this->input->post('bank_name'), $this->com_user['user_id'], $bank_name['pref_id']),
                array('settings', 'inhouse_code', $this->input->post('inhouse_code'), $this->com_user['user_id'], $inhouse_code['pref_id']),
                array('settings', 'clearing_code', $this->input->post('clearing_code'), $this->com_user['user_id'], $clearing_code['pref_id']),
                array('settings', 'rtgs_code', $this->input->post('rtgs_code'), $this->com_user['user_id'], $rtgs_code['pref_id']),
                array('settings', 'international_code', $this->input->post('international_code'), $this->com_user['user_id'], $international_code['pref_id'])
            );
            foreach ($params as $value) {
                if ($this->m_preferences->update($value)) {
                    // default notification
                    $this->tnotification->delete_last_field();
                    $this->tnotification->sent_notification("success", "Data saved successfully");
                } else {
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                }
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("master/preferences");
    }

}