<?php

class PremiumBackgrounds {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_backgrounds';

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

    function getByIDAndUser($id, $user_id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByUserID($id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('user_id', $id);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    function deleteByIdAndUser($id, $user_id) {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        if($this->db->delete('droppy_pm_backgrounds')) {
            return true;
        }
        return false;
    }

    function insert($data) {
        if($this->db->insert($this->_tablename, $data)) {
            return true;
        }
        return false;
    }
}