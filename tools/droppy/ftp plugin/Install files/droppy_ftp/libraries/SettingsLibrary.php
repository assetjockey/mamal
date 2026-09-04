<?php

class SettingsLibrary {
    public $_settings;

    function __construct()
    {
        $jsonSettings = file_get_contents(dirname(__FILE__) . '/../settings.json');

        if(!empty($jsonSettings)) {
            $this->_settings = json_decode($jsonSettings, true);
        }
    }
}