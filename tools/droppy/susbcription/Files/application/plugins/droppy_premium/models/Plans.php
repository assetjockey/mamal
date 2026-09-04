<?php

class PremiumPlans {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_plans';

        // Get codeigniter
        $this->CI =& get_instance();
        // Set DB to codeigniter DB variable
        $this->db = $this->CI->db;
    }

    function getAll($start = 0, $total = 0) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        if($total != 0) {
            $this->db->limit($total, $start);
        }
        $this->db->order_by('id', 'ASC');

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    function getAllActive($start = 0, $total = 0) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('plan_active', 'true');
        if($total != 0) {
            $this->db->limit($total, $start);
        }
        $this->db->order_by('id', 'ASC');

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
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

    function updateByID($data, $id) {
        $this->db->where('id', $id);
        if($this->db->update($this->_tablename, $data)) {
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