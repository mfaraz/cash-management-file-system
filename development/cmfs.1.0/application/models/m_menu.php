<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_menu extends CI_Model {

    function  __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get all portal
    function get_all_portal () {
        $sql = "SELECT a.*, COUNT(b.nav_id)'total_menu' FROM com_portal a
                LEFT JOIN com_menu b ON a.portal_id = b.portal_id
                GROUP BY a.portal_id
                ORDER BY portal_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get all menu by parent
    function get_all_menu_by_parent ($params) {
        $sql = "SELECT * FROM com_menu
                WHERE portal_id = ? AND parent_id = ? ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get all menu by parent
    function get_all_menu_selected_by_parent ($params) {
        $sql = "SELECT a.*, b.role_id, b.role_tp
                FROM com_menu a
                LEFT JOIN (SELECT * FROM com_role_menu WHERE role_id = ?) b ON a.nav_id = b.nav_id
                WHERE portal_id = ? AND parent_id = ?
                ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail menu by id
    function get_detail_by_id ($id_role) {
        $sql = "SELECT * FROM com_menu WHERE nav_id = ?";
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
        $sql = "INSERT INTO com_menu (portal_id, parent_id, nav_title, nav_desc, nav_url, nav_no, active_st, display_st, mdb, mdd)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // update
    function update ($params) {
        $sql = "UPDATE com_menu
                SET portal_id = ?, parent_id = ?, nav_title = ?, nav_desc = ?, nav_url = ?, nav_no = ?, active_st = ?, display_st = ?, mdb = ?
                WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }

    // update icon
    function update_icon ($params) {
        $sql = "UPDATE com_menu SET nav_icon = ? WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete ($params) {
        $sql = "DELETE FROM com_menu WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }

    // update parent
    function update_parent ($params) {
        $sql = "UPDATE com_menu SET parent_id = ? WHERE parent_id = ?";
        return $this->db->query($sql, $params);
    }
}