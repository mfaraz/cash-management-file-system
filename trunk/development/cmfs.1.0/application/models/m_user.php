<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_user extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * Users Management
    */

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get total user
    function get_total_users() {
        $sql = "SELECT COUNT(*)'total' FROM com_user WHERE user_id <> 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get all user limit
    function get_all_users($params) {
        $sql = "SELECT * FROM com_user WHERE user_id <> 1 ORDER BY user_name ASC LIMIT ?, ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail user by id
    function get_detail_user_by_id($id_user) {
        $sql = "SELECT a.*, b.role_id, c.role_nm, d.*
                FROM com_user a
                INNER JOIN com_role_user b ON a.user_id = b.user_id
                INNER JOIN com_role c ON b.role_id = c.role_id
                LEFT JOIN client_info d ON a.user_id = d.client_id
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $id_user);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // check username
    function is_exist_username($username) {
        $sql = "SELECT COUNT(*)'total' FROM com_user WHERE user_name = ?";
        $query = $this->db->query($sql, $username);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }

    // check email
    function is_exist_email($email) {
        $sql = "SELECT COUNT(*)'total' FROM com_user WHERE user_mail = ?";
        $query = $this->db->query($sql, $email);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }

    // get all role by portal
    function get_all_role_by_portal($portal_id) {
        $sql = "SELECT * FROM com_role WHERE portal_id = ?";
        $query = $this->db->query($sql, $portal_id);
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
        $sql = "INSERT INTO com_user (user_name, user_pass, user_key, lock_st, user_mail, mdb, mdd)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // update
    function update($params) {
        $sql = "UPDATE com_user SET user_name = ?, user_pass = ?, user_key = ?, lock_st = ?, user_mail = ?, mdb = ?, mdd = NOW()
                WHERE user_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete($params) {
        $sql = "DELETE FROM com_user WHERE user_id = ?";
        return $this->db->query($sql, $params);
    }

    // insert role
    function insert_role($params) {
        $sql = "INSERT INTO com_role_user (role_id, user_id)
                VALUES (?, ?)";
        return $this->db->query($sql, $params);
    }

    // delete role
    function delete_role($params) {
        $sql = "DELETE FROM com_role_user WHERE user_id = ?";
        return $this->db->query($sql, $params);
    }

}