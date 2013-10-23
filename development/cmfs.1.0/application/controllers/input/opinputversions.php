<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class opinputversions extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_input');
        $this->load->model('m_convert');
        $this->load->model('m_read_input');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/list.html");
        // get search parameter
        $search = $this->session->userdata('session_search');
        $this->smarty->assign("search", $search);
        $search_params = !empty($search['input_format_type']) ? $search['input_format_type'] : '%';
        // get list input versions
        $rs_id = $this->m_input->get_list_version_by_type($search_params);
        foreach ($rs_id as $key => $value) {
            if ($value['input_id'] == 11) {
                unset($rs_id[$key]);
            }
        }
        $this->smarty->assign("no", 1);
        $this->smarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit data
    public function edit($input_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/edit.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));

        // get list output
        $this->smarty->assign("rs_output", $this->m_convert->get_all_output());
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage rows
    public function rows($input_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/rows.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        $this->smarty->assign("rs_id", $rs_rows);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage rows - add data
    public function rows_add($input_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/rows_add.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field('field');
        // output
        parent::display();
    }

    // manage rows - edit data
    public function rows_edit($input_id = "", $row_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/rows_edit.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // get detail rows
        $detail = $this->m_input->get_input_rows_detail_by_id(array($input_id, $row_id));
        $this->smarty->assign("row", $detail);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field('field');
        // output
        parent::display();
    }

    // upload
    public function upload($input_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/upload.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file
        $this->smarty->assign("download", "false");
        $file_path = 'resource/doc/format/input/' . $input_detail['input_id'] . '/' . $input_detail['input_file_path'];
        if (is_file($file_path)) {
            $this->smarty->assign("download", "true");
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage
    public function manage($input_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/manage.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file validation
        $file_path = $this->result_file($input_detail);
        // get field data by id
        $rs_field = $this->m_input->get_all_field_by_versions($input_id);
        /*
         * Create Parameter
         */
        $params = array('input_detail' => $input_detail,
            'rs_rows' => $rs_rows,
            'file_path' => $file_path,
            'rs_field' => $rs_field);
        // read file
        $rs_data = $this->m_read_input->read_data($params);
        // show data
        $this->smarty->assign("rs_data", $rs_data);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // mapping
    public function mapping($input_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/mapping.html");
        // load javascript
        $this->smarty->load_javascript('resource/js/jquery/jquery-tipsy.js');
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file validation
        // $file_path = $this->result_file($input_detail);
        // get field output by id
        $rs_output_data = array();
        $rs_output_field = $this->m_input->get_all_field_mapping_by_id(array($input_detail['input_id'], $input_detail['output_id']));
        $temp = "";
        $map = "";
        $preview_status = true;
        foreach ($rs_output_field as $field_output) {
            $id = $field_output['field_number'];
            $rs_output_data[$id]['field_number'] = $id;
            $rs_output_data[$id]['field_name'] = $field_output['field_name'];
            $rs_output_data[$id]['field_required'] = $field_output['field_required'];
            $rs_output_data[$id]['special_desc'] = $field_output['special_desc'];
            // join string
            if ($temp != $id) {
                $delimiter = "";
                // buat field number
                if (!empty($field_output['input_field_number'])) {
                    $rs_output_data[$id]['mapping'] = 'B' . $field_output['input_field_number'];
                    if (!empty($field_output['alternatif'])) {
                        $rs_output_data[$id]['mapping'] .= '(' . $field_output['alternatif'] . ')';
                    }
                }
                $temp = $id;
                $delimiter = ";";
            } else {
                if (!empty($field_output['input_field_number'])) {
                    $rs_output_data[$id]['mapping'] .= $delimiter . 'B' . $field_output['input_field_number'];
                    if (!empty($field_output['alternatif'])) {
                        $rs_output_data[$id]['mapping'] .= '(' . $field_output['alternatif'] . ')';
                    }
                }
            }
            // if spesial field
            $rs_output_data[$id]['style'] = "";
            if (($field_output['special_cd'] != 'batch_nr') && ($field_output['special_cd'] != 'single_nr')) {
                $rs_output_data[$id]['style'] = 'class="red"';
            }
        }
        $this->smarty->assign("preview_status", $preview_status);
        $this->smarty->assign("rs_output_field", $rs_output_data);
        // get field input by id
        $rs_input_field = $this->m_input->get_all_field_by_versions($input_detail['input_id']);
        $this->smarty->assign("rs_input_field", $rs_input_field);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // review
    public function review($input_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "input/opinputversions/review.html");
        /*
         * Validation
         */
        // input validation
        $input_detail = $this->result_input($input_id);
        $this->smarty->assign("detail", $input_detail);
        $this->smarty->assign("result", $input_detail);
        $this->smarty->assign("default_users", $this->com_user['user_id']);
        //--
        // rows validation
        $rs_rows = $this->result_row($input_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // get field output by id
        $rs_output_data = array();
        $rs_output_field = $this->m_input->get_all_field_mapping_by_id(array($input_detail['input_id'], $input_detail['output_id']));
        $temp = "";
        $finish_status = "true";
        $map = "";
        foreach ($rs_output_field as $field_output) {
            $id = $field_output['field_number'];
            $rs_output_data[$id]['field_number'] = $id;
            $rs_output_data[$id]['field_name'] = $field_output['field_name'];
            $rs_output_data[$id]['field_required'] = $field_output['field_required'];
            $rs_output_data[$id]['field_default_value'] = $field_output['field_default_value'];
            $rs_output_data[$id]['field_desc'] = $field_output['field_desc'];
            // join string
            if ($temp != $id) {
                $delimiter = "";
                // buat field number
                if (!empty($field_output['input_field_number'])) {
                    $rs_output_data[$id]['mapping'] = 'B' . $field_output['input_field_number'] . ' : ' . $field_output['input_field_name'];
                }
                $temp = $id;
                $delimiter = " <br /> ";
            } else {
                if (!empty($field_output['input_field_number'])) {
                    $rs_output_data[$id]['mapping'] .= $delimiter . 'B' . $field_output['input_field_number'] . ' : ' . $field_output['input_field_name'];
                }
            }
            // status required
            // if required
            $rs_output_data[$id]['style'] = "";
            if ($field_output['field_required'] == 'yes') {
                if (empty($field_output['field_default_value'])) {
                    if (empty($rs_output_data[$id]['mapping'])) {
                        $rs_output_data[$id]['style'] = 'style="background-color: #FFF2F1;"';
                        $finish_status = 'false';
                    }
                }
            }
        }
        $this->smarty->assign("finish_status", 'true');
        $this->smarty->assign("rs_output_field", $rs_output_data);
        // get field input by id
        $rs_input_field = $this->m_input->get_all_field_by_versions($input_detail['input_id']);
        $this->smarty->assign("rs_input_field", $rs_input_field);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    /*
     * Process
     */

    // process search
    public function process_search() {
        // set page rules
        $this->_set_page_rule("R");
        // cek input
        $this->tnotification->set_rules('search_type', 'Format Types');
        // process
        if ($this->tnotification->run() !== FALSE) {
            if ($this->input->post('search') == 'Display') {
                $search = array("input_format_type" => $this->input->post('search_type'));
                $this->session->set_userdata('session_search', $search);
            } else {
                $this->session->unset_userdata('session_search');
            }
        }
        // default redirect
        redirect("input/opinputversions");
    }

    // process edit
    public function process_edit() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('input_id', 'Versions', 'required');
        $this->tnotification->set_rules('input_version', 'Versi Format Input', 'trim|required|max_length[30]');
        $this->tnotification->set_rules('input_format_type', 'Jenis Format Input', 'trim|required');
        $this->tnotification->set_rules('input_file_type', 'Jenis File Input', 'trim|required');
        $this->tnotification->set_rules('output_id', 'Versi Format Output ', 'trim|required');
        $this->tnotification->set_rules('default_status', 'Default', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // cek output dan jenis format input
            $params = array($this->input->post('output_id'));
            $output = $this->m_convert->get_detail_output_by_id($params);
            if (empty($output)) {
                $this->tnotification->set_error_message('Output format is not available');
                $this->tnotification->sent_notification("error", "Process fails");
                redirect("input/opinputversions/edit/" . $this->input->post('input_id'));
            }
            if ($output['output_format_type'] != $this->input->post('input_format_type')) {
                $this->tnotification->set_error_message('Output format is different from the input');
                $this->tnotification->sent_notification("error", "Process fails");
                redirect("input/opinputversions/edit/" . $this->input->post('input_id'));
            }
            // update default status
            $params = array('no', $this->input->post('input_format_type'));
            $this->m_input->update_default_status($params);
            // parameter
            $params = array($this->input->post('input_version'), $this->input->post('input_format_type'), $this->input->post('input_file_type'),
                $this->input->post('output_id'), $this->input->post('default_status'), $this->input->post('input_id'));
            // insert
            if ($this->m_input->update_versi($params)) {
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
        redirect("input/opinputversions/edit/" . $this->input->post('input_id'));
    }

    // process rows add
    public function process_rows_add() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('input_id', 'Versions', 'required');
        $this->tnotification->set_rules('row_number', 'Nomor Baris', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('total_column', 'Jumlah Kolom', 'trim|required|max_length[4]');
        // if empty row number
        $input_id = $this->input->post('input_id');
        $row_number = $this->input->post('row_number');
        if (empty($input_id) || empty($row_number)) {
            $this->tnotification->set_error_message('Row Number is not valid');
        }
        // if exist row number
        if ($this->m_input->is_exist_input_rowid(array($this->input->post('input_id'), $this->input->post('row_number')))) {
            $this->tnotification->set_error_message('Row Number is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('input_id'), $this->input->post('row_number'), $this->input->post('total_column'));
            // insert
            if ($this->m_input->insert_rows($params)) {
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
        redirect("input/opinputversions/rows_add/" . $this->input->post('input_id'));
    }

    // process rows update
    public function process_rows_update() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'required');
        $this->tnotification->set_rules('row_number_id', 'Nomor Baris', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('row_number', 'Nomor Baris', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('total_column', 'Jumlah Kolom', 'trim|required|max_length[4]');
        // if empty row number
        $input_id = $this->input->post('input_id');
        $row_number_id = $this->input->post('row_number_id');
        $row_number = $this->input->post('row_number');
        if (empty($input_id) || empty($row_number)) {
            $this->tnotification->set_error_message('Row Number is not valid');
        }
        // if exist row number
        if ($this->m_input->is_exist_input_rowid(array($this->input->post('input_id'), $this->input->post('row_number')))) {
            if ($row_number_id != $row_number) {
                $this->tnotification->set_error_message('Row Number is not available');
            }
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('row_number'), $this->input->post('total_column'),
                $this->input->post('input_id'), $this->input->post('row_number_id'));
            // insert
            if ($this->m_input->update_rows($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // default redirect
                redirect("input/opinputversions/rows_edit/" . $this->input->post('input_id') . '/' . $this->input->post('row_number'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("input/opinputversions/rows_edit/" . $this->input->post('input_id') . '/' . $this->input->post('row_number_id'));
    }

    // process rows delete
    public function process_rows_delete() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'required');
        $this->tnotification->set_rules('rows', 'Checkbox', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $rows = $this->input->post('rows');
            foreach ($rows as $row) {
                $params = array($this->input->post('input_id'), $row);
                $this->m_input->delete_rows($params);
            }
            // --
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data deleted successfully");
        } else {
            // default error
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("input/opinputversions/rows/" . $this->input->post('input_id'));
    }

    // process upload
    public function process_upload() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'required');
        $this->tnotification->set_rules('input_row', 'Start Row', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('input_delimiter', 'Delimiter', 'max_length[5]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // get input detail
            $input_detail = $this->result_input($this->input->post('input_id'));
            // parameter
            $params = array($this->input->post('input_row'), $this->input->post('input_delimiter'), $this->input->post('input_id'));
            // insert
            if ($this->m_input->update_upload($params)) {
                // upload icon
                if (!empty($_FILES['input_file_path']['tmp_name'])) {
                    // load
                    $this->load->library('tupload');
                    // upload config
                    $config['upload_path'] = 'resource/doc/format/input/' . $this->input->post('input_id');
                    $config['allowed_types'] = $input_detail['input_file_type'];
                    $this->tupload->initialize($config);
                    // process upload images
                    if ($this->tupload->do_upload('input_file_path')) {
                        $data = $this->tupload->data();
                        $this->m_input->update_file_path(array($data['file_name'], $this->input->post('input_id')));
                        // redirect
                        redirect("input/opinputversions/manage/" . $this->input->post('input_id'));
                    } else {
                        // jika gagal
                        $this->tnotification->set_error_message($this->tupload->display_errors());
                        $this->tnotification->sent_notification("error", "Process fails");
                    }
                } else {
                    $this->tnotification->set_error_message('File not found!');
                }
                // notification
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
        redirect("input/opinputversions/upload/" . $this->input->post('input_id'));
    }

    // process manage
    public function process_manage() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // delete
            $params = array($this->input->post('input_id'));
            $this->m_input->delete_field_by_versions($params);
            // read
            $data = $this->input->post('no');
            $data_name = $this->input->post('field_name');
            $data_desc = $this->input->post('field_desc');
            $i = 1;
            foreach ($data as $no) {
                // insert
                $field_name = isset($data_name[$i]) ? $data_name[$i] : '';
                $field_desc = isset($data_desc[$i]) ? $data_desc[$i] : '';
                $params = array($this->input->post('input_id'), $no, trim($field_name), trim($field_desc));
                $this->m_input->insert_field($params);
                $i++;
            }
            // --
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data saved successfully");
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("input/opinputversions/manage/" . $this->input->post('input_id'));
    }

    // process mapping
    public function process_mapping() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'required');
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $map_data = $this->input->post('map');
            if (!empty($map_data)) {
                // delete
                $params = array($this->input->post('output_id'), $this->input->post('input_id'));
                $this->m_input->delete_mapping_by_id($params);
                // insert
                $order_number = 1;
                $i = 1;
                foreach ($map_data as $data) {
                    $number = explode(';', $data);
                    if (count($number) > 1) {
                        // insert more than one
                        foreach ($number as $num) {
                            $mapping = substr($num, 1, strlen($num));
                            // split to alternatif
                            $alternatif = '';
                            if (strpos($mapping, '(')) {
                                $alternatif = explode('(', $mapping);
                                $mapping = isset($alternatif[0]) ? $alternatif[0] : '';
                                $alternatif = end($alternatif);
                                $alternatif = str_replace(')', '', $alternatif);
                            }
                            // params
                            $params = array($this->input->post('output_id'), $i, $this->input->post('input_id'), intval($mapping), $alternatif, $order_number);
                            $this->m_input->insert_mapping($params);
                            $order_number++;
                        }
                    } else {
                        // insert one
                        if (!empty($number[0])) {
                            $mapping = substr($number[0], 1, strlen($number[0]));
                            // split to alternatif
                            $alternatif = '';
                            if (strpos($mapping, '(')) {
                                $alternatif = explode('(', $mapping);
                                $mapping = isset($alternatif[0]) ? $alternatif[0] : '';
                                $alternatif = end($alternatif);
                                $alternatif = str_replace(')', '', $alternatif);
                            }
                            //input by paramS
//                            $params = array($this->input->post('output_id'), $i, $this->input->post('input_id'), $mapping);
//                            if (!$this->m_input->is_mapping_by_params($params)) {
                            // params
                            $params = array($this->input->post('output_id'), $i, $this->input->post('input_id'), intval($mapping), $alternatif, $order_number);
                            $this->m_input->insert_mapping($params);
                            $order_number++;
//                            }
                        }
                    }
                    $i++;
                }
            }
            // --
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data saved successfully");
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("input/opinputversions/mapping/" . $this->input->post('input_id'));
    }

    // process download input
    public function download_input($input_id = "") {
        // get input detail
        $result = $this->m_input->get_version_detail_by_id($input_id);
        if (!empty($result)) {
            // file path
            $file_path = 'resource/doc/format/input/' . $input_id . '/' . $result['input_file_path'];
            // download
            if (is_file($file_path)) {
                header('Content-type: application/octet-stream');
                header("Content-Length:" . filesize($file_path));
                header("Content-Disposition: attachment; filename=" . $result['input_file_path']);
                readfile($file_path);
                exit();
            } else {
                // default redirect
                redirect("input/opinputversions/upload/" . $input_id);
            }
        } else {
            // default redirect
            redirect("input/opinputversions/");
        }
    }

    /*
     * Validation
     */

    // input data validation
    public function result_input($input_id) {
        $result = $this->m_input->get_version_detail_by_id($input_id);
        if (empty($result)) {
            // default redirect
            redirect("input/opinputversions");
        }
        return $result;
    }

    // rows validation
    public function result_row($input_id) {
        $rs_rows = $this->m_input->get_all_input_rows_by_id($input_id);
        if (empty($rs_rows)) {
            $segment = explode('_', $this->uri->segment(3), 2);
            if (($segment[0] != 'rows') && $segment[0] != 'edit') {
                // default redirect
                redirect("input/opinputversions/rows/" . $input_id);
            }
        }
        return $rs_rows;
    }

    // file validation
    public function result_file($result) {
        $file_path = 'resource/doc/format/input/' . $result['input_id'] . '/' . $result['input_file_path'];
        return $file_path;
    }

}
