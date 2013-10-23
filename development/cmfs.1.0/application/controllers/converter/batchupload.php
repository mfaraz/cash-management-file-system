<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class batchupload extends ApplicationBase {

    // global variable
    private $input_format_type = 'Batch Upload';
    private $pagination_per_page = 50;

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_convert');
        $this->load->model('m_convert_file');
        $this->load->model('m_convert_field_batch');
        $this->load->model('m_download_batch');
        $this->load->model('m_input');
        $this->load->model('m_output');
        $this->load->model('m_utility');
        $this->load->model('m_specialfield');
        $this->load->model('m_preferences');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // load javascript
        $this->smarty->load_javascript('resource/js/jquery/jquery-ui-1.8.13.custom.min.js');
        // load css
        $this->smarty->load_style('jquery.ui/ui-lightness/jquery.ui.all.css');
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "converter/batchupload/upload.html");
        // get detail input
        $result = $this->result_default();
        $this->smarty->assign("detail", $result);
        if (empty($result)) {
            // get list input versions
            $rs_id = $this->m_input->get_list_version_by_type($this->input_format_type);
            $this->smarty->assign("rs_id", $rs_id);
        }
        // get list debitted account
        $rs_id = $this->m_preferences->get_preferences_by_group(array('debitted_account'));
        $autocomplete_data = '';
        $delimeter = '';
        foreach ($rs_id as $rec) {
            $autocomplete_data .= $delimeter . '"' . $rec['pref_value'] . '"';
            $delimeter = ',';
        }
        $this->smarty->assign("autocomplete_data", $autocomplete_data);
        // get debitted account
        $this->smarty->assign("debitted_account", isset($_SESSION['debitted_account']) ? $_SESSION['debitted_account'] : '');
        // gpg encryption
        $result = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_decryption'));
        $this->smarty->assign("gpg_decrypt", 'no');
        if (!empty($result)) {
            $this->smarty->assign("gpg_decrypt", $result['pref_value']);
        }
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // <editor-fold defaultstate="collapsed" desc="review and update">
    public function result($file = "") {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "converter/batchupload/result.html");
        // get detail input
        $result = $this->result_default();
        $this->smarty->assign("detail", $result);
        // gpg encryption
        $encrypt = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_encryption'));
        $this->smarty->assign("gpg_encrypt", 'no');
        if (!empty($encrypt)) {
            $this->smarty->assign("gpg_encrypt", $encrypt['pref_value']);
        }
        // create header
        $th_data = "<th width='50px'>No</th>";
        $width = 50;
        // get input field fy id
        $total_column = 0;
        $rs_th = $this->m_input->get_all_field_output_by_versions(array($result['output_id']));
        foreach ($rs_th as $th) {
            $width += $this->set_width($th['field_length']);
            $th_data .= "<th width='" . $this->set_width($th['field_length']) . "px'>";
            $th_data .= $th['field_name'];
            $th_data .= "</th>";
            $total_column++;
        }
        $this->smarty->assign("header_table", $th_data);
        $this->smarty->assign("width", $width);
        /*
         * Read file input
         */
        $transaksi = $this->m_convert_file->read_data($result);
        // pagination manual
        $pagination['total_record'] = count($transaksi);
        $pagination['page'] = $this->uri->segment(4, 0);
        $pagination['per_page'] = $this->pagination_per_page;
        $pagination['total_page'] = ceil($pagination['total_record'] / $pagination['per_page']);
        $start = $this->uri->segment(4, 0) + 1;
        $start = (($start > $pagination['total_record']) ? $pagination['total_record'] : $start);
        $end = $this->uri->segment(4, 0) + $pagination['per_page'];
        $end = (($end > $pagination['total_record']) ? $pagination['total_record'] : $end);
        $pagination['start'] = $start;
        $pagination['end'] = $end;
        $pagination['page_num'] = ceil($start / $pagination['per_page']);
        $pagination['prev'] = ($this->uri->segment(4, 0) - $pagination['per_page']);
        $pagination['prev'] = ($pagination['prev'] < 0) ? 0 : $pagination['prev'];
        $pagination['next'] = ($this->uri->segment(4, 0) + $pagination['per_page']);
        $pagination['next'] = ($pagination['next'] > $pagination['total_record']) ? $pagination['page'] : $pagination['next'];
        $this->smarty->assign("pagination", $pagination);
        /*
         * Read output mapping
         */
        // get field output by id
        $rs_output_data = array();
        $rs_output_field = $this->m_input->get_all_field_mapping_by_id(array($result['input_id'], $result['output_id']));
        $temp = "";
        $map = "";
        foreach ($rs_output_field as $field_output) {
            // field_number, field_required, field_length, field_type, field_default_value, special_cd
            $id = $field_output['field_number'];
            $rs_output_data[$id]['field_number'] = $id;
            $rs_output_data[$id]['field_required'] = $field_output['field_required'];
            $rs_output_data[$id]['field_length'] = $field_output['field_length'];
            $rs_output_data[$id]['field_type'] = $field_output['field_type'];
            $rs_output_data[$id]['field_default_value'] = $field_output['field_default_value'];
            $rs_output_data[$id]['special_cd'] = $field_output['special_cd'];
            $rs_output_data[$id]['delimiter'] = $result['output_delimiter'];
            $rs_output_data[$id]['delimiter_input'] = $result['input_delimiter'];
            // join string
            if ($temp != $id) {
                $delimiter = "";
                // buat field number
                $rs_output_data[$id]['mapping'] = $field_output['input_field_number'];
                if (!empty($field_output['alternatif'])) {
                    $rs_output_data[$id]['mapping'] .= '|' . $field_output['alternatif'];
                }
                $temp = $id;
                $delimiter = ";";
            } else {
                $rs_output_data[$id]['mapping'] .= $delimiter . $field_output['input_field_number'];
                if (!empty($field_output['alternatif'])) {
                    $rs_output_data[$id]['mapping'] .= '|' . $field_output['alternatif'];
                }
            }
        }
        /*
         *  group transaksi
         */
        // get field mapping tanggal transaksi
        $index_date = $this->m_specialfield->get_field_by_spesial_code(array($result['output_id'], $result['input_id'], 'batch_id'));
        // get field mapping debitted account
        $index_account = $this->m_specialfield->get_field_by_spesial_code(array($result['output_id'], $result['input_id'], 'batch_da'));
        // get field mapping total amount
        $index_total = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_ta'));
        // get field mapping transfer amount
        $index_amount = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tra'));
        // get field number total record
        $index_record = $this->m_specialfield->get_output_field_by_spesial_cd(array($result['output_id'], 'batch_tr'));
        // transaksi di group kan berdasarkan 2 parameter khusus tersebut
        $group_transaksi = array();

        foreach ($transaksi as $data) {
            // get index value by param
            $tanggal_value = isset($data[$index_date[0] - 1]) ? $data[$index_date[0] - 1] : '';
            $tanggal = $this->m_utility->get_cmfs_date($index_date[1], $tanggal_value);
            // jika tanggal kurang dari sekarang
            if (strtotime(date('Ymd', strtotime($tanggal))) < strtotime(date('Ymd'))) {
                $tanggal = date('Ymd');
            }
            //--
            $account = isset($data[$index_account[0] - 1]) ? $data[$index_account[0] - 1] : '1';

            if (empty($account)) {
                $account = isset($_SESSION['debitted_account']) ? $_SESSION['debitted_account'] : '1';
            }
            if (!empty($tanggal) && !empty($account)) {
                $group_transaksi[$tanggal . $account][] = $data;
            }
        }
        $this->smarty->assign("total_file", count($group_transaksi));
        // loop group transaksi
        $nr = 1;
        $tr_data = "";
        foreach ($group_transaksi as $detail_transaksi) {
            $total_record = count($detail_transaksi);
            $total_amount = 0;
            // loop transaksi
            foreach ($detail_transaksi as $input) {
                // create row
                // tampilkan
                if ($nr >= $start && $nr <= $end) {
                    $tr_data .= "<tr>";
                    $tr_data .= "<td align='center'>" . $nr . ".</td>";
                }
                // start column
                $nd = 1;
                // loop column
                foreach ($rs_output_data as $data_output) {
                    $class = "input" . $nd;
                    $size = $this->set_size($data_output['field_length']);
                    $name = 'trx_data[' . $nr . '][' . $nd . ']';
                    // convert value
                    $value = $this->m_convert_field_batch->convert_field($data_output, $input);
                    // required
                    $red_style = $value['status'];
                    // jika data dalam session ditemukan
                    if (isset($_SESSION['converter_data'][$nr][$nd])) {
                        $value = $_SESSION['converter_data'][$nr][$nd];
                    } else {
                        $_SESSION['converter_data'][$nr][$nd] = $value;
                    }
                    // write column
                    $enable = '';
                    // total record
                    if ($nd == $index_record) {
                        $value['value'] = '';
                        $enable = 'disabled';
                    }
                    // spesial amount
                    if ($nd == $index_amount) {
                        $total_amount += $value['value'];
                    }
                    if ($nd == $index_total) {
                        $value['value'] = '';
                        $enable = 'disabled';
                    }
                    // --
                    // tampilkan
                    if ($nr >= $start && $nr <= $end) {
                        $tr_data .= "<td align='center'>";
                        $tr_data .= '<input type="hidden" name="' . $name . '[status]' . '" value="' . $value['status'] . '" />';
                        $tr_data .= "<input type='text' name='" . $name . '[value]' . "' value='" . trim($value['value']) . "' maxlength='" . $data_output['field_length'] . "' size='" . $size . "' style='text-align:center; " . $red_style . "' " . $enable . " class='" . $class . "' />";
                        $tr_data .= "</td>";
                    }
                    $nd++;
                }
                // tampilkan
                if ($nr >= $start && $nr <= $end) {
                    $tr_data .= "</tr>";
                }
                $nr++;
            }
            // buat batasan
            $tr_data .= "<tr>";
            $tr_data .= "<td colspan='" . ($total_column + 1) . "'><b>Total Record : " . $total_record . ", Total Amount : " . number_format($total_amount, 2, '.', '') . "</b><hr /></td>";
            $tr_data .= "</tr>";
        }
        // view
        $this->smarty->assign("tr_data", $tr_data);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // </editor-fold>

    /*
     * Process
     */

    // <editor-fold defaultstate="collapsed" desc="process upload">
    public function process_upload() {
        // set page rules
        $this->_set_page_rule("C");
        // check input
        $this->tnotification->set_rules('status', 'Status', 'trim|required');
        $this->tnotification->set_rules('debitted_account', 'Debitted Account', 'trim');
        // cek file
        if (empty($_FILES['file_upload']['tmp_name'])) {
            $this->tnotification->set_error_message('File not found');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $da = trim($this->input->post('debitted_account'));
            // delete debitted account
            $this->m_preferences->delete_by_group_nm(array('debitted_account', $da));
            // get status decryption
            $decrypt_status = $this->m_preferences->get_preferences_by_group_name(array('settings', 'gpg_decryption'));
            // insert debitted account
            if (!empty($da)) {
                $this->m_preferences->insert(array('debitted_account', $da, $da, $this->com_user['user_id']));
            }
            // set debitted account
            $_SESSION['debitted_account'] = $da;
            // load library
            $this->load->library('tupload');
            // upload file
            if (!empty($_FILES['file_upload']['tmp_name'])) {
                // get detail input
                $result = $this->result_default();
                // upload config
                $config['upload_path'] = 'resource/doc/converter/input/' . date("Ymd");
                //cek if decrypt status ok let only gpg extensions
                if ($decrypt_status['pref_value'] == 'yes') {
                    //set path for encyption key
                    $this->m_utility->set_path();
                    $config['allowed_types'] = 'gpg';
                } else {
                    $config['allowed_types'] = $result['input_file_type'];
                }
                $config['file_name'] = date("YmdHis");
                $this->tupload->initialize($config);
                // process upload images
                if (!$this->tupload->do_upload('file_upload')) {
                    // jika gagal
                    $this->tnotification->set_error_message($this->tupload->display_errors());
                    // default error
                    $this->tnotification->sent_notification("error", "Process fails");
                } else {
                    $data = $this->tupload->data();
                    if ($decrypt_status['pref_value'] == 'yes') {
                        // decryption command
                        $base_command = FCPATH . "system\plugins\gpgengine\gpg";
                        //key file
                        $pubring = FCPATH . 'resource\\doc\\encryption\\gpg\\key\\pubring.gpg';
                        $secring = FCPATH . 'resource\\doc\\encryption\\gpg\\key\\secring.gpg';
                        //--
                        if (is_file($pubring) && is_file($secring)) {
                            //decrypt gpg
                            $key_file = FCPATH . 'resource\\doc\\encryption\\gpg\\passphrase\\clientpassphrase.txt';
                            $rs_passphrase = $this->m_preferences->get_preferences_by_group_name(array('gpg', 'clientpassphrase'));
                            $passphrase = $rs_passphrase['pref_value'];
                            if (is_file($key_file)) {
                                unlink($key_file);
                            }
                            if (!$handle = fopen($key_file, 'a')) {
                                
                            }
                            if (fwrite($handle, $passphrase) === FALSE) {
                                
                            }
                            fclose($handle);
                            // location file
                            $encrypted_file = FCPATH . 'resource\\doc\\converter\\input\\' . date("Ymd") . '\\' . $data['file_name'];
                            $plain_text = FCPATH . 'resource\\doc\\converter\\input\\' . date("Ymd") . '\\' . $data['raw_name'] . '.' . $result['input_file_type'];
                            //decrypt command
                            exec("$base_command --passphrase-fd 0 < $key_file --output $plain_text --decrypt $encrypted_file");
                            exec("taskkill /f /im gpg.exe");
                        } else {
                            //notification if fail
                            $this->tnotification->set_error_message("Key File for Encryption not Found");
                            $this->tnotification->sent_notification("error", "Process fails");
                            // redirect page
                            redirect("converter/batchupload");
                        }
                    }
                    // clear session
                    if (isset($_SESSION['converter_data'])) {
                        unset($_SESSION['converter_data']);
                    }
                    if (isset($_SESSION['converter_file'])) {
                        unset($_SESSION['converter_file']);
                    }
                    // set file
                    $_SESSION['converter_file'] = $config['upload_path'] . '/' . $data['raw_name'] . '.' . $result['input_file_type'];
                    // notification
                    $this->tnotification->delete_last_field();
                    $this->tnotification->sent_notification("success", "File uploaded successfully");
                    // redirect page
                    redirect("converter/batchupload/result/");
                }
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // redirect page
        redirect("converter/batchupload");
    }

    // </editor-fold>
    // process update
    public function process_update() {
        // set page rules
        $this->_set_page_rule("C");
        // get detail input
        $result = $this->result_default();
        // check input
        $this->tnotification->set_rules('status', 'Status', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // reset
            $action = $this->input->post('action');
            if ($action == 'Reset') {
                unset($_SESSION['converter_data']);
                // redirect page
                redirect("converter/batchupload/result/");
            }
            // save to session
            $trx_data = $this->input->post('trx_data');
            if (is_array($trx_data)) {
                foreach ($trx_data as $index => $data) {
                    $_SESSION['converter_data'][$index] = $data;
                }
            }
            // notification
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "File updated successfully");
            // download
            if ($action == 'Download') {
                $this->process_download();
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // redirect page
        redirect("converter/batchupload/result/");
    }

    // <editor-fold defaultstate="collapsed" desc="process pagination">
    public function process_pagination() {
        // set page rules
        $this->_set_page_rule("R");
        // cek input
        $this->tnotification->set_rules('page_num', 'Nomor Halaman');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $page_num = ($this->input->post('page_num') - 1) * $this->pagination_per_page;
        }
        // default redirect
        redirect("converter/batchupload/result/" . $page_num);
    }

    // </editor-fold>
    // process update default
    public function process_default() {
        // set page rules
        $this->_set_page_rule("U");
        // check input
        $this->tnotification->set_rules('default', 'Default Input', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // update all
            $this->m_input->update_default_status(array('no', $this->input_format_type));
            // update
            $input_id = $this->input->post('default');
            $this->m_input->update_default_status_by_id(array('yes', $input_id));
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // redirect page
        redirect("converter/batchupload/");
    }

    // process update default clear
    public function process_default_clear() {
        // update all
        $this->m_input->update_default_status(array('no', $this->input_format_type));
        // redirect page
        redirect("converter/batchupload/");
    }

    // process download
    public function process_download() {
        // transaksi
        $transaksi = array();
        if (isset($_SESSION['converter_data'])) {
            $transaksi = $_SESSION['converter_data'];
        }
        // get detail input
        $result = $this->result_default();
        // get baris
        $rs_baris = $this->m_convert->get_all_output_row_by_id($result['output_id']);
        // export to file
        $prefix = 'batch_';
        $this->m_download_batch->export_file($result, $rs_baris, $transaksi, $prefix);
        // notification
        $this->tnotification->delete_last_field();
        $this->tnotification->sent_notification("success", "File created successfully");
    }

    /*
     * Validation
     */

    public function result_default() {
        $result = array();
        // get default format
        $params = array($this->input_format_type, 'yes');
        $result = $this->m_convert->get_default_input($params);
        // validate
        $segment = $this->uri->segment(3);
        if (empty($result) && $segment == 'index') {
            // default redirect
            redirect("converter/batchupload");
        }
        // return
        return $result;
    }

    /*
     * Width Formula
     */

    public function set_width($length = 0) {
        $width = $length;
        // minimal width
        if ($length <= 10) {
            $width = 50;
        }
        // maksimal width
        if ($length > 45) {
            $width = 150;
        }
        return $width;
    }

    public function set_size($length = 0) {
        $size = $length;
        // normal width
        if ($length > 25) {
            $size = 25;
        }
        // max width
        if ($length > 100) {
            $size = 50;
        }
        return $size;
    }

}