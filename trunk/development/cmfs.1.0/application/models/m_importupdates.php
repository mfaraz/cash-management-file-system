<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_importupdates extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get primary id
    function get_primary_id($params) {
        $result = 1000 + intval($params);
        return $result;
    }

}