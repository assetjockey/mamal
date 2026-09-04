<?php

class PremiumUploads {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_uploads';

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

    function getBySessionID($id, $parent_id = '') {
        $this->db->select('*');
        $this->db->from('droppy_uploads');
        $this->db->join('droppy_pm_users', $this->_tablename . '.pm_email = droppy_pm_users.id');
        $this->db->where_in('droppy_uploads.status', array('ready', 'processing'));
        $this->db->where('droppy_uploads.pm_email', $id);
        if(!empty($parent_id)) {
            $this->db->or_where('droppy_uploads.pm_email', $parent_id);
            $this->db->where_in('droppy_uploads.status', array('ready', 'processing'));
        }
        $this->db->or_where('droppy_pm_users.parent_id', $id);
        $this->db->where_in('droppy_uploads.status', array('ready', 'processing'));

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    function getTotalUploadByUser($user_ID) {
        $this->db->select('sum(size) AS `total_size`');
        $this->db->from($this->_tablename);
        $this->db->where('pm_email', $user_ID);
        $this->db->where('`time` > UNIX_TIMESTAMP(DATE_SUB(curdate(), INTERVAL 1 MONTH))');

        $query = $this->db->get();

        if($query === false) {
            return 0;
        }

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }
}