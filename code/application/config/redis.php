<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

switch (ENVIRONMENT) {
    case 'production':
        $config['redis'] = array(
            'host' => '172.18.194.20',
            'port' => 6379,
        );
        break;
    case 'development':
    case 'testing':
    default:
        $config['redis'] = array(
            'host' => '172.18.194.20',
            'port' => 6379,
        );
        break;
}