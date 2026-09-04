<?php

class PremiumForgot {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_forgot';

        // Get codeigniter
        $this->CI =& get_instance();
        // Set DB to codeigniter DB variable
        $this->db = $this->CI->db;
    }

    function getByID($id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('id', $id);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByResetCode($code) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('reset', $code);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function insert($data) {
        if($this->db->insert($this->_tablename, $data)) {
            return true;
        }
        return false;
    }

    function deleteByResetCode($code) {
        $this->db->delete($this->_tablename, array('reset' => $code));
    }
}