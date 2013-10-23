<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_convert extends CI_Model {

    function  __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * Convert History
    */

    // get total history
    function get_total_convert_history () {
        $sql = "SELECT COUNT(*)'total' FROM convert_history";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        }else {
            return 0;
        }
    }

    // get history by limit
    function get_all_convert_history ($params) {
        $sql = "SELECT * FROM convert_history ORDER BY convert_date DESC LIMIT ?, ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail history
    function get_detail_history ($params) {
        $sql = "SELECT * FROM convert_history WHERE convert_date = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // insert history
    function insert_history($params) {
        $sql = "INSERT INTO convert_history (convert_date, convert_file_input, convert_file_output, input_version, output_version, log_info)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, $params);
    }


    // delete history
    function delete_history($params) {
        $sql = "DELETE FROM convert_history WHERE convert_date = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * Input
    */

    // get input format by type
    function get_all_input_by_type ($params) {
        $sql = "SELECT * FROM cmfs_input WHERE input_format_type = ? ORDER BY input_version DESC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail input format by id
    function get_detail_input_by_id ($params) {
        $sql = "SELECT * FROM cmfs_input WHERE input_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get default input format by id
    function get_default_input ($params) {
        $sql = "SELECT a.*, b.output_version, b.output_file_type, b.output_delimiter
                FROM cmfs_input a
                LEFT JOIN cmfs_output b ON a.output_id = b.output_id
                WHERE input_format_type = ? AND default_status = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get default input format by id
    function get_default_input_by_id ($params) {
        $sql = "SELECT a.*, b.output_version, b.output_file_type, b.output_delimiter
                FROM cmfs_input a
                LEFT JOIN cmfs_output b ON a.output_id = b.output_id
                WHERE a.input_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    /*
     * Output
    */

    // get output format
    function get_all_output () {
        $sql = "SELECT * FROM cmfs_output ORDER BY output_format_type ASC, output_version DESC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get output format by type
    function get_all_output_by_type ($params) {
        $sql = "SELECT * FROM cmfs_output WHERE output_format_type = ? ORDER BY output_version DESC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail output format by id
    function get_detail_output_by_id ($params) {
        $sql = "SELECT * FROM cmfs_output WHERE output_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get output row by id
    function get_all_output_row_by_id ($params) {
        $sql = "SELECT * FROM cmfs_output_rows WHERE output_id = ? ORDER BY row_number ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

}