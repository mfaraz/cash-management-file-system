<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// controller for list format
class opoutputversions extends ApplicationBase {

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
        $this->load->library("pagination");
        // set global variable
    }

    // list information update form
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/list.html");
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

    // edit data
    public function edit($output_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/edit.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // delete data
    public function delete($output_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/delete.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("result", $output_detail);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage row
    public function rows($output_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/rows.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("rs_list", $rs_rows);
        $this->smarty->assign("jumlah_baris", count($rs_rows));

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage rows - add row
    public function rows_add($output_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/rows_add.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // manage rows - update data
    public function rows_update($output_id = "", $row_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/rows_update.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // get detail
        $detail = $this->m_output->get_output_rows_detail_by_id(array($output_id, $row_id));
        $this->smarty->assign("row", $detail);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field('field');
        // output
        parent::display();
    }

    // upload file
    public function upload($output_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/upload.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file
        $this->smarty->assign("download", "false");
        $file_path = 'resource/doc/format/output/' . $output_detail['output_id'] . '/' . $output_detail['output_file_path'];
        if (is_file($file_path)) {
            $this->smarty->assign("download", "true");
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    //show form manage output mcm
    public function manage($output_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->smarty->assign("template_content", "output/opoutputversions/manage.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file validation
        $file_path = $this->result_file($output_detail);
        // get field data by id
        $rs_field = $this->m_output->get_cmfs_output_field_by_id($output_id);
        // get all mcm special field
        $rs_special_fl = $this->m_specialfield->get_all_cmfs_special_field_by_format($output_detail['output_format_type']);
        $this->smarty->assign("rs_special_fl", $rs_special_fl);
        /*
         * Create Parameter
         */
        $params = array('output_detail' => $output_detail,
            'rs_rows' => $rs_rows,
            'file_path' => $file_path,
            'rs_field' => $rs_field);
        // read file
        $rs_data = $this->m_read_output->read_data($params);
        // show data
        $this->smarty->assign("rs_data", $rs_data);
        // jika sudah tersimpan
        $preview_status = false;
        if (!empty($rs_field)) {
            $preview_status = true;
        }
        $this->smarty->assign("preview_status", $preview_status);
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
        $this->smarty->assign("template_content", "output/opoutputversions/riview.html");
        /*
         * Validation
         */
        // input validation
        $output_detail = $this->result_output($output_id);
        $this->smarty->assign("detail", $output_detail);
        $this->smarty->assign("result", $output_detail);
        // rows validation
        $rs_rows = $this->result_row($output_id);
        $this->smarty->assign("jumlah_baris", count($rs_rows));
        // file validation
        $file_path = $this->result_file($output_detail);
        // get output version field by output id
        $rs_output_field = $this->m_output->get_cmfs_output_field_by_id($output_id);
        $this->smarty->assign("rs_list", $rs_output_field);
        if (empty($rs_output_field)) {
            redirect("opoutputversions/manage/" . $output_detail['output_id']);
        }
        // get all mcm special field
        $rs_special_fl = $this->m_specialfield->get_all_cmfs_special_field();
        $this->smarty->assign("rs_special_fl", $rs_special_fl);
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
        redirect("output/opoutputversions");
    }

    // process edit
    public function process_edit() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('output_id', 'Versions', 'required');
        $this->tnotification->set_rules('output_version', 'Versi Format Output', 'trim|required|max_length[30]');
        $this->tnotification->set_rules('output_format_type', 'Jenis Format Output', 'trim|required');
        $this->tnotification->set_rules('output_file_type', 'Jenis File Output', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('output_version'), $this->input->post('output_format_type'),
                $this->input->post('output_file_type'), $this->input->post('output_id'));
            // insert
            if ($this->m_output->update_versi($params)) {
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
        redirect("output/opoutputversions/edit/" . $this->input->post('output_id'));
    }

    // process row add
    public function process_row_add() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        $this->tnotification->set_rules('row_number', 'Row Number', 'trim|required|max_lengh[11]');
        $this->tnotification->set_rules('total_column', 'Total Column', 'trim|required|max_lengh[11]');
        // if empty row number
        $output_id = $this->input->post('output_id');
        $row_number = $this->input->post('row_number');
        if(empty($output_id) || empty($row_number)) {
            $this->tnotification->set_error_message('Row Number is not valid');
        }
        // if exist row number
        if ($this->m_output->is_exist_output_rowid(array($this->input->post('output_id'), $this->input->post('row_number')))) {
            $this->tnotification->set_error_message('Row Number is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array($this->input->post('output_id'), intval($this->input->post('row_number')), intval($this->input->post('total_column')));
            // insert
            if ($this->m_output->insert_rows($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
            } else {
                // jika gagal (kembalikan pesan)
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/opoutputversions/rows_add/" . $this->input->post('output_id'));
    }

    // process row update
    public function process_row_update() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        $this->tnotification->set_rules('row_number_old', 'Baris Sebelumnya', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('row_number', 'Baris Ke', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('total_column', 'Jumlah Kolom', 'trim|required|max_length[4]');
        // if empty row number
        $input_id = $this->input->post('output_id');
        $row_number_id = $this->input->post('row_number_old');
        $row_number = $this->input->post('row_number');
        if(empty($input_id) || empty($row_number)) {
            $this->tnotification->set_error_message('Row Number is not valid');
        }
        // if exist row number
        if ($this->m_output->is_exist_output_rowid(array($this->input->post('output_id'), $this->input->post('row_number')))) {
            if($row_number_id != $row_number) {
                $this->tnotification->set_error_message('Row Number is not available');
            }
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // parameter
            $params = array($this->input->post('row_number'), $this->input->post('total_column'),
                $this->input->post('output_id'), $this->input->post('row_number_old'));
            // update
            if ($this->m_output->update_rows($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data saved successfully");
                // default redirect
                redirect("output/opoutputversions/rows_update/" . $this->input->post('output_id') . '/' . $this->input->post('row_number'));
            } else {
                // jika gagal (kembalikan pesan)
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // default redirect
        redirect("output/opoutputversions/rows_update/" . $this->input->post('output_id') . '/' . $this->input->post('row_number_old'));
    }

    // process rows delete
    public function process_rows_delete() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        $this->tnotification->set_rules('rows', 'Checkbox', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $rows = $this->input->post('rows');
            foreach ($rows as $row) {
                $params = array($this->input->post('output_id'), $row);
                $this->m_output->delete_rows($params);
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
        redirect("output/opoutputversions/rows/" . $this->input->post('output_id'));
    }

    // delete process
    public function process_delete() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array($this->input->post('output_id'));
            // delete process
            if ($this->m_output->delete($params)) {
                //delete directory of these versions
                $dirPath = "resource/doc/format/output/" . $this->input->post('output_id');
                if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                    $dirPath .= '/';
                }
                //list all file
                $files = glob($dirPath . '*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                if (is_dir($dirPath)) {
                    rmdir($dirPath);
                }

                //notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("output/opoutputversions");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("output/opoutputversions/delete" . $this->input->post('output_id'));
    }

    //upload process mcm output
    public function process_upload() {
        // set page rules
        $this->_set_page_rule("C");
        // load
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        $this->tnotification->set_rules('output_row', 'Start Row', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('output_delimiter', 'Delimiter', 'max_length[5]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // get output detail
            $output_detail = $this->result_output($this->input->post('output_id'));
            // parameter
            $params = array($this->input->post('output_row'), $this->input->post('output_delimiter'), $this->input->post('output_id'));
            // insert
            if ($this->m_output->update_upload($params)) {
                // upload
                if (!empty($_FILES['output_file_path']['tmp_name'])) {
                    // load
                    $this->load->library('tupload');
                    // upload config
                    $config['upload_path'] = 'resource/doc/format/output/' . $this->input->post('output_id');
                    $config['allowed_types'] = $output_detail['output_file_type'];
                    $this->tupload->initialize($config);
                    // process upload
                    if ($this->tupload->do_upload('output_file_path')) {
                        $data = $this->tupload->data();
                        $this->m_output->update_file_path(array($data['file_name'], $this->input->post('output_id')));
                        // redirect
                        redirect("output/opoutputversions/manage/" . $this->input->post('output_id'));
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
        redirect("output/opoutputversions/upload/" . $this->input->post('output_id'));
    }

    //manage process mcm output
    public function process_manage() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('output_id', 'Output ID', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // delete
            $params = array($this->input->post('output_id'));
            $this->m_output->delete_output($params);
            // process reset
            $reset = $this->input->post('action');
            if ($reset == 'Reset') {
                // default redirect
                redirect("output/opoutputversions/manage/" . $this->input->post('output_id'));
            }
            // read
            $data = $this->input->post('no');
            $data_name = $this->input->post('field_name');
            $data_desc = $this->input->post('field_desc');
            $data_required = $this->input->post('field_required');
            $data_length = $this->input->post('field_length');
            $data_type = $this->input->post('field_type');
            $special_cd = $this->input->post('special_cd');
            $data_default_value = $this->input->post('field_default_value');
            $i = 1;
            foreach ($data as $no) {
                // insert
                $field_name = isset($data_name[$i]) ? substr($data_name[$i], 0, 45) : '';
                $field_desc = isset($data_desc[$i]) ? $data_desc[$i] : '';
                $field_required = isset($data_required[$i]) ? $data_required[$i] : 'no';
                $field_length = isset($data_length[$i]) ? $data_length[$i] : '';
                $field_type = isset($data_type[$i]) ? $data_type[$i] : 'text';
                $field_special_cd = isset($special_cd[$i]) ? $special_cd[$i] : '';
                $field_default_value = isset($data_default_value[$i]) ? $data_default_value[$i] : '';
                $result = $this->m_specialfield->get_detail_by_id($field_special_cd);
                if (!empty($result)) {
                    $params = array($this->input->post('output_id'),
                        $no, $field_special_cd, trim($field_name), trim($field_desc),
                        trim($field_required), intval($field_length), trim($field_type), trim($field_default_value));
                    $this->m_output->insert_output($params);
                }
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
        redirect("output/opoutputversions/manage/" . $this->input->post('output_id'));
    }

    // process download output
    public function download($output_id = "") {
        // get input detail
        $result = $this->m_output->get_detail_by_id($output_id);
        if (!empty($result)) {
            // file path
            $file_path = 'resource/doc/format/output/' . $output_id . '/' . $result['output_file_path'];
            // download
            if (is_file($file_path)) {
                header('Content-type: application/octet-stream');
                header("Content-Length:" . filesize($file_path));
                header("Content-Disposition: attachment; filename=" . $result['output_file_path']);
                readfile($file_path);
                exit();
            } else {
                // default redirect
                redirect("output/opoutputversions/upload/" . $output_id);
            }
        } else {
            // default redirect
            redirect("input/opinputversions/upload/" . $output_id);
        }
    }

    /*
     * Validation
     */

    // output data validation
    public function result_output($output_id) {
        $result = $this->m_output->get_detail_by_id($output_id);
        if (empty($result)) {
            // default redirect
            redirect("output/opoutputversions");
        }
        return $result;
    }

    // rows validation
    public function result_row($output_id) {
        $rs_rows = $this->m_output->get_output_rows($output_id);
        if (empty($rs_rows)) {
            $segment = explode('_', $this->uri->segment(3), 2);
            if (($segment[0] != 'rows') && $segment[0] != 'edit') {
                // default redirect
                redirect("output/opoutputversions/rows/" . $output_id);
            }
        }
        return $rs_rows;
    }

    // file validation
    public function result_file($result) {
        $file_path = 'resource/doc/format/output/' . $result['output_id'] . '/' . $result['output_file_path'];
        return $file_path;
    }

}