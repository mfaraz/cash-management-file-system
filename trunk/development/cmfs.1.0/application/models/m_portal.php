<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class m_portal extends CI_Model {

    function  __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get total data
    function get_total_data () {
        $sql = "SELECT COUNT(*)'total' FROM com_portal";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        }else {
            return 0;
        }
    }

    // get all portal
    function get_all_portal () {
        $sql = "SELECT * FROM com_portal";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail data by id
    function get_portal_by_id ($portal_id) {
        $sql = "SELECT * FROM com_portal WHERE portal_id = ?";
        $query = $this->db->query($sql, $portal_id);
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
        $sql = "INSERT INTO com_portal (portal_nm, site_title, site_desc, meta_desc, meta_keyword, mdb, mdd)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // update
    function update ($params) {
        $sql = "UPDATE com_portal SET portal_nm = ?, site_title = ?, site_desc = ?, meta_desc = ?, meta_keyword = ?,
                mdb = ?, mdd = NOW()
                WHERE portal_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete ($params) {
        $sql = "DELETE FROM com_portal WHERE portal_id = ?";
        return $this->db->query($sql, $params);
    }
}