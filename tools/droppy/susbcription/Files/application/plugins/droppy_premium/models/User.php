<?php

class PremiumUser {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_users';

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
        $this->db->order_by('id', 'desc');

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

    function getBySubIDAndID($id, $sub_id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('id', $id);
        $this->db->where('sub_id', $sub_id);
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

    function getByEmailPassword($email, $password) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getByParentID($parent_id) {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('parent_id', $parent_id);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    function getTotalStorage($user_ID) {
        $user = $this->getByID($user_ID);

        if($user !== false) {
            $this->db->select('sum(size) AS `total_size`');
            $this->db->from('droppy_uploads');
            $this->db->join('droppy_pm_users', "droppy_pm_users.id = droppy_uploads.pm_email");
            $this->db->where('droppy_pm_users.sub_id', $user['sub_id']);
            $this->db->where("droppy_uploads.status", 'ready');

            $query = $this->db->get();

            if ($query === false) {
                return 0;
            }

            if ($query->num_rows() > 0) {
                return $query->row_array()['total_size'];
            }
        }
        return false;
    }

    function updateByID($data, $id) {

    }

    function updateBySubID($data, $id) {
        $this->db->where('sub_id', $id);
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

    function deleteByID($id) {
        $clsBackgrounds = new PremiumBackgrounds();

        $data = $clsBackgrounds->getByUserID($id);

        foreach ($data as $row) {
            unlink(FCPATH . $row['src']);
            $clsBackgrounds->deleteByIdAndUser($row['id'], $id);
        }

        $this->db->delete($this->_tablename, array('id' => $id));
    }

    function deleteBySubID($id) {
        $this->db->delete($this->_tablename, array('sub_id' => $id));
    }
}