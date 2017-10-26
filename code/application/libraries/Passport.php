<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Passport {

    private $CI;
    private $_cache = null;

    function __construct() {
        $this->CI = & get_instance();
    }

    function get($item_name) {
        $this->CI->config->load('passport', true);
        $item = $this->CI->config->item($item_name, 'passport');
        return $item;
    }

    function get_config($item_name) {
        $this->CI->config->load('config', true);
        $item = $this->CI->config->item($item_name, 'config');
        return $item;
    }
}
