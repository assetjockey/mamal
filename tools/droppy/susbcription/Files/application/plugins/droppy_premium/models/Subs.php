<?php

class PremiumSubs {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_subs';

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

    function getBySubID($id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('sub_id', $id);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByPaypalID($id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('paypal_id', $id);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByStripeID($id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('stripe_id', $id);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByEmail($email) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('email', $email);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByToken($token) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('paypal_token', $token);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getForCron() {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('status','active');
        $this->db->or_where('status','ready');
        $this->db->or_where('status', 'suspended');
        $this->db->or_where('status', 'canceled_end');

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
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

    function updateBySubID($data, $id) {
        $this->db->where('sub_id', $id);
        if($this->db->update($this->_tablename, $data)) {
            return true;
        }
        return false;
    }

    function updateByStripeID($data, $id) {
        $this->db->where('stripe_id', $id);
        if($this->db->update($this->_tablename, $data)) {
            return true;
        }
        return false;
    }

    function updateByEmail($data, $email) {
        $this->db->where('email', $email);
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