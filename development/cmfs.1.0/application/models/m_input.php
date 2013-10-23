<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_input extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get detail by id
    function get_version_detail_by_id($params) {
        $sql = "SELECT a.*, b.output_version
                FROM cmfs_input a
                LEFT JOIN cmfs_output b ON a.output_id = b.output_id
                WHERE input_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list input versions by type
    function get_list_version_by_type($params) {
        $sql = "SELECT a.*, b.output_version
                FROM cmfs_input a
                LEFT JOIN cmfs_output b ON a.output_id = b.output_id
                WHERE input_format_type NOT LIKE 'ITM' AND input_format_type LIKE ?
                ORDER BY input_format_type ASC, input_version ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mcm input
    function insert($params) {
        $sql = "INSERT INTO cmfs_input (output_id, input_version, input_format_type, input_file_type, create_date, default_status)
                VALUES (?, ?, ?, ?, NOW(), ?)";
        return $this->db->query($sql, $params);
    }

    // insert mcm input from import update
    function insert_update($params) {
        $sql = "INSERT INTO cmfs_input (input_id, output_id, input_version, input_format_type, input_file_type, input_row, input_delimiter, create_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // update mcm input
    function update($params) {
        $sql = "UPDATE cmfs_input SET output_id = ?, input_file_type = ?, input_row = ?, input_delimiter = ?, update_date = NOW()
                WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // update default status
    function update_default_status($params) {
        $sql = "UPDATE cmfs_input SET default_status = ? WHERE input_format_type = ?";
        return $this->db->query($sql, $params);
    }

    // update default status by id
    function update_default_status_by_id($params) {
        $sql = "UPDATE cmfs_input SET default_status = ? WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // update versi
    function update_versi($params) {
        $sql = "UPDATE cmfs_input SET input_version = ?, input_format_type = ?, input_file_type = ?, output_id = ?, default_status = ?
                WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // update jenis file
    function update_jenis_file($params) {
        $sql = "UPDATE cmfs_input SET input_file_type = ? WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // update upload file
    function update_upload($params) {
        $sql = "UPDATE cmfs_input SET input_row = ?, input_delimiter = ?, update_date = NOW() WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // update mcm input file path
    function update_file_path($params) {
        $sql = "UPDATE cmfs_input SET input_file_path = ? WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    //delete
    // update mcm input file path
    function delete($params) {
        $sql = "DELETE FROM cmfs_input WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * manage rows
     */

    // get list input rows by id
    function get_all_input_rows_by_id($params) {
        $sql = "SELECT * FROM cmfs_input_rows WHERE input_id = ?
                ORDER BY row_number ASC ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail input rows by id
    function get_input_rows_detail_by_id($params) {
        $sql = "SELECT * FROM cmfs_input_rows WHERE input_id = ? AND row_number = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get total rows by id and row number
    function is_exist_input_rowid($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_input_rows WHERE input_id = ? AND row_number = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }

    // insert mcm input rows
    function insert_rows($params) {
        $sql = "INSERT INTO cmfs_input_rows VALUES (?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // update mcm input rows
    function update_rows($params) {
        $sql = "UPDATE cmfs_input_rows SET row_number = ?, total_column = ? WHERE input_id = ? AND row_number = ?";
        return $this->db->query($sql, $params);
    }

    // delete mcm input rows
    function delete_rows($params) {
        $sql = "DELETE FROM cmfs_input_rows WHERE input_id = ? AND row_number = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * manage field
     */

    // get list field mapping by id
    function get_all_field_mapping_by_id($params) {
        $sql = "SELECT c.*, d.field_name'input_field_name'
                FROM (SELECT a.output_id, a.field_name, a.field_number, a.field_required, a.field_type, a.field_default_value, a.field_desc, a.special_cd,
                b.input_id, b.input_field_number, a.field_length, b.alternatif, sf.special_desc
                FROM cmfs_output_field a
                INNER JOIN cmfs_special_field sf ON a.special_cd = sf.special_cd
                LEFT JOIN (SELECT * FROM cmfs_input_mapping WHERE input_id = ?) b ON a.output_id = b.output_id AND a.field_number = b.output_field_number
                WHERE a.output_id = ?
                ORDER BY a.field_number ASC, b.order_number ASC) c
                LEFT JOIN cmfs_input_field d ON d.input_id = c.input_id AND d.field_number = c.input_field_number;";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list field by versions
    function get_list_field_mapping_by_id($params) {
        $sql = "SELECT * FROM cmfs_input_mapping WHERE input_id = ? ORDER BY output_field_number,input_field_number";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list field by versions
    function get_all_field_output_by_versions($params) {
        $sql = "SELECT * FROM cmfs_output_field WHERE output_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list field by versions
    function get_all_field_by_versions($params) {
        $sql = "SELECT * FROM cmfs_input_field WHERE input_id = ? ORDER BY field_number ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // delete field by versions
    function delete_field_by_versions($params) {
        $sql = "DELETE FROM cmfs_input_field WHERE input_id = ?";
        return $this->db->query($sql, $params);
    }

    // insert field
    function insert_field($params) {
        $sql = "INSERT INTO cmfs_input_field (input_id, field_number, field_name, field_desc, mdd)
                VALUES (?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // delete mapping by id
    function delete_mapping_by_id($params) {
        $sql = "DELETE FROM cmfs_input_mapping WHERE output_id = ? AND input_id = ?";
        return $this->db->query($sql, $params);
    }

    // insert mapping
    function insert_mapping($params) {
        $sql = "INSERT INTO cmfs_input_mapping (output_id, output_field_number, input_id, input_field_number, alternatif, order_number)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    function is_mapping_by_params($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_input_mapping WHERE output_id = ?  AND  output_field_number = ? AND input_id = ? AND  input_field_number = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }

}