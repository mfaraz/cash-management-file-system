<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class m_bank extends CI_Model {

    function __construct() {
        //-- Call the Model constructor
        parent::__construct();
        //--
        $this->load->model("m_preferences");
    }

    /*
     * Nasional
     */

    // get total data bank
    function get_total_bank_nasional($params) {
        $sql = "SELECT COUNT(*)'total' FROM bank WHERE bank_name LIKE ? OR bank_keyword LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get data data bank by limit
    function get_list_bank_nasional($params) {
        $sql = "SELECT * FROM bank
                WHERE bank_name LIKE ? OR bank_keyword LIKE ?
                ORDER BY bank_name ASC
                LIMIT ?, ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert
    function insert($params) {
        $sql = "INSERT INTO bank (bank_id, bank_name, bank_keyword, rtgs_code, kliring_code, int_code)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // delete data bank
    function delete_all() {
        $sql = "DELETE FROM bank";
        return $this->db->query($sql);
    }

    /*
     * Internasional
     */

    // get total data bank
    function get_total_bank_internasional($params) {
        $sql = "SELECT COUNT(*)'total' FROM bankinternasional WHERE bank_name LIKE ? OR bank_keyword LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get data data bank by limit
    function get_list_bank_internasional($params) {
        $sql = "SELECT * FROM bankinternasional
                WHERE bank_name LIKE ? OR bank_keyword LIKE ?
                ORDER BY bank_name ASC
                LIMIT ?, ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert
    function insert_internasional($params) {
        $sql = "INSERT INTO bankinternasional (bank_id, bank_name, bank_keyword, rtgs_code, kliring_code)
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // delete data bank
    function delete_all_internasional() {
        $sql = "DELETE FROM bankinternasional";
        return $this->db->query($sql);
    }

    // get all data bank by keyword
    function search_bank_by_bank_name($sql) {
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return '';
        }
    }

    // services code
    function get_services_code($output, $input) {
        // result
        $result['services_code'] = '';
        $result['rtgs_code'] = '';
        $result['kliring_code'] = '';
        $result['int_code'] = '';
        $result['amount'] = 0;
        $result['nominal_limit'] = 50000000;
        // begin
        $mapping = explode(';', $output['mapping']);
        // jika persyaratan mapping terpenuhi
        if (count($mapping) >= 4) {
            // get kode bank 3 digit di awal
            $map_index = $mapping[0] - 1;
            $kode_bank = isset($input[$map_index]) ? $input[$map_index] : '';
            $kode_bank = substr($kode_bank, 0, 5);
            // get nama bank
            $bank_name_limit = $this->m_preferences->get_preferences_by_group_name(array("settings", "bank_name"));
            $limit = $bank_name_limit['pref_value'];
            $map_index = explode('|', $mapping[1]);
            if (isset($map_index[1])) {
                // alternatif
                $limit = $map_index[1];
            }
            $map_index = $map_index[0] - 1;
            $nama_bank = isset($input[$map_index]) ? $input[$map_index] : '';
            // get transfer currency
            $map_index = $mapping[2] - 1;
            $currency = isset($input[$map_index]) ? $input[$map_index] : 'IDR';
            $currency = trim($currency);
            $currency = !empty($currency) ? $currency : 'IDR';
            $currency = strtoupper($currency);
            // get transfer amount
            $amount = 0;
            $map_index = explode('|', $mapping[3]);
            if (count($map_index) == 2) {
                // get value
                $map_amount = $map_index[0] - 1;
                $amount = isset($input[$map_amount]) ? $input[$map_amount] : '';
                // get format currency client
                $format_currency = explode('#', $map_index[1]);
                if (count($format_currency) == 2) {
                    $amount = str_replace($format_currency[0], '', $amount);
                    $amount = str_replace($format_currency[1], '.', $amount);
                } else {
                    $amount = str_replace($format_currency[0], '', $amount);
                    $amount = intval($amount);
                }
            }
            $result['amount'] = $amount;
            // decimal validation
            $clear = explode('.', $amount);
            if (count($clear) == 2) {
                if (isset($clear[1]) && empty($clear[1])) {
                    $amount = intval($amount);
                }
            }
            // direct services code
            $service_code = '';
            if (isset($mapping[4])) {
                $map_index = $mapping[4] - 1;
                $service_code = isset($input[$map_index]) ? $input[$map_index] : '';
            }
            /*
             * Process
             */
            // cari di database
            $mandiri = false;
            /*
             * cari di database
             */
            // parameter bank_code
            $bank_code_params = '';
            $kode_bank = trim(preg_replace('/[^a-zA-Z0-9]/', '', $kode_bank));
            if (!empty($kode_bank)) {
                $bank_code_params = "(rtgs_code LIKE '" . $kode_bank . "%' OR kliring_code LIKE '" . $kode_bank . "%' OR int_code LIKE '" . $kode_bank . "%')";
            }
            // parameter bank name
            $bank_name_params = '';
            $nama_bank = trim($nama_bank);
            $bank_params = explode(' ', $nama_bank, ($limit + 1));
            if (count($bank_params) > $limit) {
                $bank_params = array_pop($bank_params);
            }
            if (is_array($bank_params)) {
                $and = '';
                $kurung = '(';
                foreach ($bank_params as $param) {
                    $param = preg_replace('/[^a-zA-Z0-9]/', '', $param);
                    if (!empty($param)) {
                        $bank_name_params .= $kurung . $and . " nama_bank LIKE '%" . $param . "%'";
                        $and = ' AND';
                        $kurung = '';
                    }
                }
                if (!empty($bank_name_params)) {
                    $bank_name_params .= ')';
                }
            }
            // get data
            $data = array();
            if (!empty($bank_code_params)) {
                // cek kode dan nama
                $sql = "SELECT DISTINCT * FROM (
                        SELECT DISTINCT rtgs_code, kliring_code, int_code, CONCAT_WS('',bank_name, bank_keyword)'nama_bank'
                        FROM bank) rs WHERE " . $bank_code_params;
                $data = $this->search_bank_by_bank_name($sql);
                // cek nama
                if (empty($data) && !empty($bank_name_params)) {
                    $sql = "SELECT DISTINCT * FROM (
                        SELECT DISTINCT rtgs_code, kliring_code, int_code, CONCAT_WS('',bank_name, bank_keyword)'nama_bank'
                        FROM bank) rs WHERE " . $bank_name_params;
                    $data = $this->search_bank_by_bank_name($sql);
                }
            } else {
                if (!empty($bank_name_params)) {
                    $sql = "SELECT DISTINCT * FROM (
                        SELECT DISTINCT rtgs_code, kliring_code, int_code, CONCAT_WS('',bank_name, bank_keyword)'nama_bank'
                        FROM bank) rs WHERE " . $bank_name_params;
                    $data = $this->search_bank_by_bank_name($sql);
                }
            }
            // jika  data ditemukan
            if (!empty($data)) {
                // fill data
                $result['rtgs_code'] = $data['rtgs_code'];
                $result['kliring_code'] = $data['kliring_code'];
                $result['int_code'] = $data['int_code'];
                // cek data bank mandiri
                if ($data['rtgs_code'] == 'BMRIIDJA') {
                    $mandiri = true;
                }
            } else {
                $map_index = $mapping[0] - 1;
                $bank_cd = isset($input[$map_index]) ? $input[$map_index] : '';
                // jika tidak ditemukan juga maka isikan bank code dr inputnya
                $result['rtgs_code'] = $bank_cd;
                $result['kliring_code'] = $bank_cd;
                $result['int_code'] = $bank_cd;
            }
            // jika diisi dari field input
            if (!empty($service_code)) {
                // service code is not empty
                $service_code = strtoupper($service_code);
                // di sesuaikan dengan jangkauannya
                $arr_valid = array('IBU', 'LBU', 'RBU', 'INU', 'OBU');
                if (!in_array($service_code, $arr_valid)) {
                    switch ($service_code) {
                        case 'MANDIRI':
                            $result['services_code'] = 'IBU';
                            break;
                        case 'INHOUSE':
                            $result['services_code'] = 'IBU';
                            break;
                        case 'RTGS':
                            $result['services_code'] = 'RBU';
                            break;
                        case 'KLIRING':
                            $result['services_code'] = 'LBU';
                            break;
                        case 'SKN':
                            $result['services_code'] = 'LBU';
                            break;
                        case 'LLG':
                            $result['services_code'] = 'LBU';
                            break;
                        case 'INTERNASIONAL':
                            $result['services_code'] = 'INU';
                            break;
                        case 'TT':
                            $result['services_code'] = 'INU';
                            break;
                        default :
                            // diasumsikan sebagai default yaitu diisi nama bank nya
                            $result['services_code'] = 'LBU';
                    }
                } else {
                    $result['services_code'] = $service_code;
                }
            } else {
                // jika bank mandiri
                if ($mandiri) {
                    $result['services_code'] = 'IBU';
                } else {
                    // get parameter nominal
                    $nominal = $this->m_preferences->get_preferences_by_group_name(array('settings', 'nominal_parameter'));
                    $result['nominal_limit'] = $nominal['pref_value'];
                    // --
                    if ($currency == 'IDR') {
                        if ($amount > $nominal['pref_value']) {
                            $result['services_code'] = 'RBU';
                        } else {
                            $result['services_code'] = 'LBU';
                        }
                    } else {
                        $result['services_code'] = 'INU';
                    }
                }
            }
            // return result
            return $result;
        }
    }

}