<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_site extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get site data
    function get_site_data_by_id($id_group) {
        $sql = "SELECT * FROM com_portal WHERE portal_id = ?";
        $query = $this->db->query($sql, $id_group);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    // get current page
    function get_current_page($params) {
        $sql = "SELECT * FROM com_menu WHERE nav_url = ? ORDER BY nav_no DESC LIMIT 0,1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    // get menu by id
    function get_menu_by_id($params) {
        $sql = "SELECT * FROM com_menu WHERE nav_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    //get  navigation user
    function get_navigation_user_by_parent($params) {
        $sql = "SELECT * FROM com_menu a
                INNER JOIN com_role_menu b ON a.nav_id = b.nav_id
                INNER JOIN com_role_user c ON b.role_id = c.role_id
                WHERE a.portal_id = ? AND c.user_id = ? AND parent_id = ? AND active_st = '1' AND display_st = '1'
                ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    //get navigation by parent
    function get_navigation_by_parent($params) {
        $params = $params;
        $sql = "SELECT * FROM com_menu
                WHERE portal_id = ? AND parent_id = ? AND active_st = '1' AND display_st = '1'
                ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    // get parent group
    function get_parent_group_by_idnav($int_parent, $limit) {
        $sql = "SELECT a.nav_id, a.parent_id FROM com_menu a WHERE a.nav_id = ?
                ORDER BY a.nav_no DESC LIMIT 0, 1";
        $query = $this->db->query($sql, array($int_parent));
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['parent_id'] == $limit) {
                return $result['nav_id'];
            } else {
                return self::get_parent_group_by_idnav($result['parent_id'], $limit);
            }
        } else {
            return $int_parent;
        }
    }

    // get user detail
    function get_user_detail_by_username($username) {
        $sql = "SELECT a.*, c.role_id,role_nm, d.*
                FROM com_user a
                LEFT JOIN com_role_user b ON a.user_id = b.user_id
                LEFT JOIN com_role c ON b.role_id = c.role_id
                LEFT JOIN client_info d ON a.user_id = d.client_id
                WHERE user_name = ? LIMIT 0, 1";
        $query = $this->db->query($sql, $username);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return false;
        }
    }

    // get user authority
    function get_user_authority($user_id, $id_group) {
        $sql = "SELECT a.user_id FROM com_user a
                INNER JOIN com_role_user b ON a.user_id = b.user_id
                INNER JOIN com_role c ON b.role_id = c.role_id
                WHERE a.user_id = ? AND c.portal_id = ?";
        $query = $this->db->query($sql, array($user_id, $id_group));
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['user_id'];
        } else {
            return false;
        }
    }

    // get user authority by navigation
    function get_user_authority_by_nav($params) {
        $sql = "SELECT b.* FROM com_menu a
                INNER JOIN com_role_menu b ON a.nav_id = b.nav_id
                INNER JOIN com_role c ON b.role_id = c.role_id
                INNER JOIN com_role_user d ON c.role_id = d.role_id
                WHERE d.user_id = ? AND b.nav_id = ? AND active_st = '1' AND a.portal_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['role_tp'];
        } else {
            return false;
        }
    }

    // get login
    function get_user_login($username, $password, $portal_id) {
        // load encrypt
        $this->load->library('encrypt');
        // process
        // get hash key
        $result = $this->get_user_detail_by_username($username);
        if (!empty($result)) {
            $password_decode = $this->encrypt->decode($result['user_pass'], $result['user_key']);
            // get user
            if ($password_decode === $password) {
                // cek authority then return id
                $authority = $this->get_user_authority($result['user_id'], $portal_id);
                if (!empty($authority)) {
                    return $result;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    // save log login
    function save_user_login($params) {
        $sql = "INSERT INTO com_user_login (user_id, login_date, ip_address) VALUES (?, NOW(), ?)";
        return $this->db->query($sql, $params);
    }

    // delete log login
    function delete_user_login_by_date($user_id) {
        $sql = "DELETE FROM com_user_login WHERE user_id = ? AND DATE(login_date) = DATE(NOW())";
        return $this->db->query($sql, $user_id);
    }

    // tambahan
    // get detail user by id
    function get_detail_admin_by_id($id_user) {
        $sql = "SELECT a.*, admin_nm, admin_phone, admin_photo
                FROM com_user a
                INNER JOIN administrator b ON a.user_id = b.user_id
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

}