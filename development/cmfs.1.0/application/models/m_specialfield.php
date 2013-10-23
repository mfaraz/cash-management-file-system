<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_specialfield extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get all mcm special field
    function get_all_cmfs_special_field() {
        $sql = "SELECT * FROM cmfs_special_field ORDER BY order_num";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all mcm special field by format
    function get_all_cmfs_special_field_by_format($params) {
        if ($params == 'Batch Upload') {
            $format = 'batch_%';
        } elseif ($params == 'Single Mixed Upload') {
            $format = 'single_%';
        } else {
            $format = '%';
        }
        $sql = "SELECT * FROM cmfs_special_field
                WHERE special_cd LIKE ?
                ORDER BY order_num";
        $query = $this->db->query($sql, $format);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all mcm special field
    function get_all_cmfs_special_field_limit($params) {
        $sql = "SELECT * FROM cmfs_special_field ORDER BY order_num LIMIT ?,?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail mcm special field special code
    function get_detail_by_id($params) {
        $sql = "SELECT * FROM cmfs_special_field  WHERE special_cd = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get total data mcm special field
    function get_total_data() {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_special_field";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return array();
        }
    }

    // get spesial field mapping
    function get_field_by_spesial_code($params) {
        $sql = "SELECT c.input_field_number, c.alternatif
                FROM cmfs_output a
                INNER JOIN cmfs_output_field b ON a.output_id = b.output_id
                INNER JOIN cmfs_input_mapping c ON b.output_id = c.output_id AND b.field_number = c.output_field_number
                WHERE a.output_id = ? AND c.input_id = ? AND b.special_cd = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return array($result['input_field_number'], $result['alternatif']);
        } else {
            return array('', '');
        }
    }

    // get output field number by param
    function get_output_field_by_spesial_cd($params) {
        $sql = "SELECT field_number
                FROM cmfs_output_field
                WHERE output_id = ? AND special_cd = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['field_number'];
        } else {
            return '';
        }
    }

    // insert mcm special field
    function insert($params) {
        $sql = "INSERT INTO cmfs_special_field (special_cd, special_nm, special_desc, order_num) VALUES (?, ?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // update mcm special field
    function update($params) {
        $sql = "UPDATE cmfs_special_field
                SET special_nm = ?, special_desc = ?,
                order_num = ?
                WHERE special_cd = ?";
        return $this->db->query($sql, $params);
    }

    // delete mcm special field
    function delete($params) {
        $sql = "DELETE FROM cmfs_special_field WHERE special_cd = ?";
        return $this->db->query($sql, $params);
    }

}