<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_role extends CI_Model {

    function  __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get all roles
    function get_all_roles () {
        $sql = "SELECT * FROM com_role ORDER BY portal_id ASC, role_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get all role
    function get_all_role () {
        $sql = "SELECT b.portal_nm, a.*
                FROM com_role a
                INNER JOIN com_portal b ON a.portal_id = b.portal_id
                ORDER BY b.portal_nm ASC, role_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail role by id
    function get_detail_by_id ($id_role) {
        $sql = "SELECT a.*, b.portal_nm
                FROM com_role a
                INNER JOIN com_portal b ON a.portal_id = b.portal_id
                WHERE role_id = ?";
        $query = $this->db->query($sql, $id_role);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // insert
    function insert ($params) {
        $sql = "INSERT INTO com_role (portal_id, role_nm, role_desc)
                VALUES (?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // update
    function update ($params) {
        $sql = "UPDATE com_role SET portal_id = ?, role_nm = ?, role_desc = ?
                WHERE role_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete ($params) {
        $sql = "DELETE FROM com_role WHERE role_id = ?";
        return $this->db->query($sql, $params);
    }

    // insert
    function insert_role_menu ($params) {
        $sql = "INSERT INTO com_role_menu (role_id, nav_id, role_tp) VALUES (?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete_role_menu ($params) {
        $sql = "DELETE FROM com_role_menu WHERE role_id = ?";
        return $this->db->query($sql, $params);
    }
}