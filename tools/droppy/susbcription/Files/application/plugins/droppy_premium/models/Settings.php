<?php

class PremiumSettings {
    private $CI, $db, $_tablename;

    function __construct()
    {
        $this->_tablename = 'droppy_pm_settings';

        // Get codeigniter
        $this->CI =& get_instance();
        // Set DB to codeigniter DB variable
        $this->db = $this->CI->db;
    }

    function getSettings() {
        $this->db->select('*');
        $this->db->from($this->_tablename);
        $this->db->where('id', 1);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function getDroppySettings() {
        $this->db->select('*');
        $this->db->from('droppy_settings');
        $this->db->where('id', 1);
        $this->db->limit(1);

        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    function checkSettings() {
        if($this->db->table_exists($this->_tablename)) {
            return true;
        }
        return false;
    }

    function update($data) {
        $this->db->where('id', 1);
        if($this->db->update($this->_tablename, $data)) {
            return true;
        }
        return false;
    }

    function installPremium() {
        return run_sql_file(dirname(__FILE__) . '/../install.sql', $this->db);
    }

    function updatePremium($version = false, $current_version = false) {
        if($version !== false) {
            if($current_version < '2.0.6') {
                run_sql_file(dirname(__FILE__) . '/../updates/update.sql', $this->db);
            }
            if($current_version < '2.0.8') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.0.8.sql', $this->db);
            }
            if($current_version < '2.0.9') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.0.9.sql', $this->db);
            }
            if($current_version < '2.0.10') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.0.10.sql', $this->db);
            }
            if($current_version < '2.1.0') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.0.sql', $this->db);
            }
            if($current_version < '2.1.1') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.1.sql', $this->db);
            }
            if($current_version < '2.1.2') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.2.sql', $this->db);
            }
            if($current_version < '2.1.3') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.3.sql', $this->db);
            }
            if($current_version < '2.1.4') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.4.sql', $this->db);
            }
            if($current_version < '2.1.5') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.5.sql', $this->db);
            }
            if($current_version < '2.1.6') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.6.sql', $this->db);
            }
            if($current_version < '2.1.7') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.7.sql', $this->db);
            }
            if($current_version < '2.1.8') {
                run_sql_file(dirname(__FILE__) . '/../updates/update_2.1.8.sql', $this->db);
            }

            return true;
        }
        else
        {
            return run_sql_file(dirname(__FILE__) . '/../updates/update.sql', $this->db);
        }
    }
}