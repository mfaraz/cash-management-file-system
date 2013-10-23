<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_convert_field_batch extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        // load model
        $this->load->model('m_utility');
    }

    /*
     * Read File Logic
    */

    // convert field
    public function convert_field($output, $input) {
        // result
        $value = array('value' => '', 'status' => '');
        // switch type file
        switch ($output['special_cd']) {
            case 'batch_nr':
                $value = $this->convert_normal($output, $input);
                break;
            case 'batch_id':
                $value = $this->convert_td($output, $input);
                break;
            case 'batch_da':
                $value = $this->convert_da($output, $input);
                break;
            case 'batch_rek':
                $value = $this->convert_rek($output, $input);
                break;
            case 'batch_tr':
            // total record

                break;
            case 'batch_ta':
            // total amount

                break;
            case 'batch_cr':
                $value = $this->convert_cr($output, $input);
                break;
            case 'batch_tra':
                $value = $this->convert_tra($output, $input);
                break;
            case 'batch_sc':
                $value = $this->convert_sc($output, $input);
                break;
            case 'batch_bc':
                $value = $this->convert_bc($output, $input);
                break;
            case 'batch_nf':
                $value = $this->convert_nf($output, $input);
                break;
            case 'batch_ne':
                $value = $this->convert_ne($output, $input);
                break;
            default:
                $value = $this->convert_normal($output, $input);
        }
        //replace if contains spesial character
        $result = $this->m_utility->replace_special_char($value, $output['delimiter']);
        // trim
        $result['value'] = trim($result['value']);
        //return
        return $result;
    }

    // <editor-fold defaultstate="collapsed" desc="convert normal field">
    public function convert_normal($output, $input) {
        /*
         * Normal field converter form merge, splitting and normal mode
        */
        $value['value'] = '';
        $value['status'] = '';
        // loop for merge
        $delimeter = '';
        $mapping = explode(';', $output['mapping']);
        foreach($mapping as $map) {
            // splitting
            $map_index = explode('|', $map);
            $input_value = isset($input[$map_index[0] - 1])?$input[$map_index[0] - 1]:'';
            // convert splitting
            if(isset($map_index[1])) {
                $trim = explode(',', $map_index[1]);
                $input_value = substr($input_value, (isset($trim[0])?$trim[0]:0), (isset($trim[1])?$trim[1]:strlen($input_value)));
                $value['value'] .= $delimeter . $input_value;
            } else {
                $value['value'] .= $delimeter . $input_value;
            }
            $delimeter = ' ';
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if($output['field_required'] == 'yes') {
            if(empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert transaction date">
    public function convert_td($output, $input) {
        /*
         * instruction date field, 1 input. input 1 untuk date
        */
        $value['value'] = date('Ymd');
        $value['status'] = '';
        // get instruction date
        $map = explode('|', $output['mapping']);
        if(count($map) == 2) {
            // cek tanggal
            if(isset($map[1])) {
                $tanggal = $this->m_utility->get_cmfs_date($map[1], $input[$map[0] - 1]);
                $value['value'] = $tanggal;
            }
        }
        // jika tanggal kurang dari sekarang
        if (strtotime(date('Ymd', strtotime($value['value']))) < strtotime(date('Ymd'))) {
            $value['value'] = date('Ymd');
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert debitted account">
    public function convert_da($output, $input) {
        /*
         * instruction date field, 1 input. input 1 untuk date
        */
        $value['value'] = '';
        $value['status'] = '';
        // get value
        $input_value = isset($input[$output['mapping'] - 1])?$input[$output['mapping'] - 1]:'';
        $value['value'] = trim($input_value);
        // field_default_value
        if(empty($value['value'])) {
            $value['value'] = isset($_SESSION['debitted_account'])?$_SESSION['debitted_account']:$output['field_default_value'];
        }
        // remoce char
        $value['value'] = preg_replace('/[^a-zA-Z0-9]/', '', $value['value']);
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert no rekening">
    public function convert_rek($output, $input) {
        /*
         * instruction date field, 1 input. input 1 untuk date
        */
        $value['value'] = '';
        $value['status'] = '';
        // get value
        $input_value = isset($input[$output['mapping'] - 1])?$input[$output['mapping'] - 1]:'';
        $value['value'] = $input_value;
        // remoce char
        $value['value'] = preg_replace('/[^a-zA-Z0-9]/', '', $value['value']);
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert Currency">
    public function convert_cr($output, $input) {
        /*
         * transfer currency field, 1 input. untuk di konversi jadi huruf besar
        */
        $value['value'] = '';
        $value['status'] = '';
        // mapping
        $mapping = $output['mapping'] - 1;
        $value['value'] = isset($input[$mapping])?$input[$mapping]:'';
        // validasi
        $value['value'] = strtoupper($value['value']);
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // default value
        if(empty ($value['value'])) {
            $value['value'] = 'IDR';
        }
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert transfer ammount">
    public function convert_tra($output, $input) {
        /*
         * transfer amount field, 1 input. dengan alternatif pemisah ribuan dan desimal
        */
        $value['value'] = '';
        $value['status'] = '';
        // splitting
        $map_index = explode('|', $output['mapping']);
        if(count($map_index) == 2) {
            // get value
            $mapping = $map_index[0] - 1;
            $value['value'] = isset($input[$mapping])?$input[$mapping]:'';
            // get format currency client
            $format_currency = explode('#', $map_index[1]);
            if(count($format_currency) == 2) {
                $value['value'] = str_replace($format_currency[0], '', $value['value']);
                $value['value'] = str_replace($format_currency[1], '.', $value['value']);
            } else {
                $value['value'] = str_replace($format_currency[0], '', $value['value']);
                $value['value'] = intval($value['value']);
            }

        }
        // decimal validation
        $clear = explode('.', $value['value']);
        if(count($clear) == 2) {
            if(isset($clear[1]) && empty($clear[1])) {
                $value['value'] = intval($value['value']);
            }
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert service code">
    public function convert_sc($output, $input) {
        // load model bank
        $this->load->model('m_bank');
        /*
         * Services Code field, 5 input.
         * input 1 untuk kode bank, input 2 untuk Nama Bank, input 3 untuk currency, input 4 untuk transfer amount
         * input 5 optional (diisi = pengisian langsung) (tidak diisi = cek sesuai parameter)
        */
        $value['value'] = '';
        $value['status'] = '';
        // get services code
        $result = $this->m_bank->get_services_code ($output, $input);
        $value['value'] = $result['services_code'];
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="convert bank code">
    public function convert_bc($output, $input) {
        // load model bank
        $this->load->model('m_bank');
        /*
         * Bank Code seperti services code
        */
        $result = $this->m_bank->get_services_code ($output, $input);
        // --
        $value['value'] = '';
        $value['status'] = '';
        // get input
        $mapping = explode(';', $output['mapping']);
        if (count($mapping) >= 4) {
            // find code
            switch ($result['services_code']) {
                case 'IBU':
                    if ($result['amount'] > $result['nominal_limit']) {
                        // ambil kode rtgs
                        $value['value'] = 'BMRIIDJA';
                    } else {
                        // ambil kliring
                        $value['value'] = '0080017';
                    }
                    break;
                case 'LBU':
                    $value['value'] = $result['kliring_code'];
                    break;
                case 'RBU':
                    $value['value'] = $result['rtgs_code'];
                    break;
                case 'INU':
                    $value['value'] = $result['int_code'];
                    break;
                default:

            }
            // jika kosong
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        } else {
            $value['status'] = 'background-color: #FFE1E1;';
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="notification flag">
    public function convert_nf($output, $input) {
        /*
         * Notification Email
        */
        $value['value'] = '';
        $value['status'] = '';
        // get email
        $map_index = $output['mapping'] - 1;
        $email = isset($input[$map_index]) ? $input[$map_index] : '';
        $email = trim($email);
        // validasi
        if (!empty($email)) {
            $value['value'] = 'Y';
        } else {
            $value['value'] = 'N';
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="notification email">
    public function convert_ne($output, $input) {
        /*
         * Notification Email field, 2 input. input 1 untuk Notification Flag, input 2 untuk Email
        */
        $value['value'] = '';
        $value['status'] = '';
        // get email
        $map_index = $output['mapping'] - 1;
        $value['value'] = isset($input[$map_index]) ? $input[$map_index] : '';
        // if input delimiter is @
        if($output['delimiter_input'] == '@') {
            // combine
            if(!empty($value['value'])) {
                $value['value'] = $this->m_utility->gdff_format_email($value['value']);
            }
        }
        // if output delimiter is @
        if($output['delimiter'] == '@') {
            // explode
            $value['value'] = $this->m_utility->format_email_to_gdff($value['value']);
        }
        // field_default_value
        if (empty($value['value'])) {
            $value['value'] = $output['field_default_value'];
        }
        // field_length
        $value['value'] = substr($value['value'], 0, $output['field_length']);
        // field_required
        if ($output['field_required'] == 'yes') {
            if (empty($value['value'])) {
                $value['status'] = 'background-color: #FFE1E1;';
            }
        }
        // return
        return $value;
    }
    // </editor-fold>

}