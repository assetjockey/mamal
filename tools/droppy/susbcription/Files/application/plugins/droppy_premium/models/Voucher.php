<?php

class PremiumVoucher {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_vouchers';

        // Get codeigniter
        $this->CI =& get_instance();

        // Set DB to codeigniter DB variable
        $this->db = $this->CI->db;
    }

    function insert($data) {
        if($this->db->insert($this->_tablename, $data)) {
            return true;
        }
        return false;
    }

    function getAll() {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->order_by('id', 'DESC');

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

    function getByCode($code) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('code', $code);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function deleteByID($id) {
        $this->db->delete($this->_tablename, array('id' => $id));
    }
}