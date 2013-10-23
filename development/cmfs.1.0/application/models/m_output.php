<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_output extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get all output
    function get_all_cmfs_output() {
        $sql = "SELECT * FROM cmfs_output ORDER BY output_id,output_version,output_format_type";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all output limit
    function get_all_cmfs_output_limit($params) {
        $sql = "SELECT * FROM cmfs_output ORDER BY output_id,output_version,output_format_type LIMIT ?,?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail data by version id
    function get_detail_by_id($params) {
        $sql = "SELECT * FROM cmfs_output WHERE output_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get total data
    function get_total_data() {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_output";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total data
    function get_total_data_by_type($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_output WHERE output_format_type LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get list input versions by type
    function get_list_version_by_type($params) {
        $sql = "SELECT * FROM cmfs_output WHERE output_format_type LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // check mcm output  by id
    function is_exist_cmfs_output($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_output WHERE output_id = ?";
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

    // insert mcm output
    function insert($params) {
        $sql = "INSERT INTO cmfs_output (output_version, output_format_type, output_file_type, create_date, update_date) VALUES (?, ?, ?, NOW(), NOW())";
        return $this->db->query($sql, $params);
    }

    // insert mcm output from updates
    function insert_update($params) {
        $sql = "INSERT INTO cmfs_output (output_id, output_version, output_format_type, output_file_type, output_row, output_delimiter, create_date, update_date) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        return $this->db->query($sql, $params);
    }

    // update versi
    function update_versi($params) {
        $sql = "UPDATE cmfs_output SET output_version = ?, output_format_type = ?, output_file_type = ?
                WHERE output_id = ?";
        return $this->db->query($sql, $params);
    }

    // update upload file
    function update_upload($params) {
        $sql = "UPDATE cmfs_output SET output_row = ?, output_delimiter = ?, update_date = NOW() WHERE output_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete mcm output
    function delete($params) {
        $sql = "DELETE FROM cmfs_output WHERE output_id = ?";
        return $this->db->query($sql, $params);
    }

    // update mcm input file path
    function update_file_path($params) {
        $sql = "UPDATE cmfs_output SET output_file_path = ? WHERE output_id = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * Management data mcm output rows
     * 
     */

    // get total data
    function get_total_column($params) {
        $sql = "SELECT SUM(total_column)'total' FROM cmfs_output_rows WHERE output_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total data
    function get_total_data_rows($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_output_rows WHERE output_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total rows by id and row number
    function is_exist_output_rowid($params) {
        $sql = "SELECT COUNT(*)'total' FROM cmfs_output_rows WHERE output_id = ? AND row_number = ?";
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

    //get output row
    function get_output_rows($params) {
        $sql = "SELECT * FROM cmfs_output_rows WHERE output_id = ? ORDER BY row_number ASC ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get mcm output rows by output id and row number
    function get_output_rows_detail_by_id($params) {
        $sql = "SELECT * FROM cmfs_output_rows WHERE output_id = ? AND row_number = ? ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mcm output rows
    function insert_rows($params) {
        $sql = "INSERT INTO cmfs_output_rows (output_id, row_number, total_column) VALUES (?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // update mcm input rows
    function update_rows($params) {
        $sql = "UPDATE cmfs_output_rows SET row_number = ?, total_column = ? WHERE output_id = ? AND row_number = ?";
        return $this->db->query($sql, $params);
    }

    // delete mcm input rows
    function delete_rows($params) {
        $sql = "DELETE FROM cmfs_output_rows WHERE output_id = ? AND row_number = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * Management data mcm output field
     * 
     */

    //get all data mcm output field order by number
    function get_all_cmfs_output_field() {
        $sql = "SELECT * FROM cmfs_output_field ORDER BY field_number";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    //get data mcm output field by output id
    function get_cmfs_output_field_by_id($params) {
        $sql = "SELECT * FROM cmfs_output_field WHERE output_id = ? ORDER BY field_number";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    //get data mcm output field by output id join special field
    function get_all_cmfs_output_field_by_id($params) {
        $sql = "SELECT a.* ,b.special_nm FROM cmfs_output_field a INNER JOIN cmfs_special_field b 
            ON a.special_cd = b.special_cd WHERE output_id = ? ORDER BY field_number";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mcm output field
    function insert_output($params) {
        $sql = "INSERT INTO cmfs_output_field (output_id, field_number, special_cd, field_name, field_desc,
                field_required, field_length, field_type, field_default_value, mdd)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // delete mcm output field
    function delete_output($params) {
        $sql = "DELETE FROM cmfs_output_field WHERE output_id = ?";
        return $this->db->query($sql, $params);
    }

}