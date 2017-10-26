<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

switch(ENVIRONMENT) {
	case 'production':
		$config['memcached'] = array(
			'hostname' => '10.126.78.11',
			'port'     => 11211,
			'weight'   => 1,
		);
		break;
	case 'testing':
		$config['memcached'] = array(
			'hostname' => '172.18.194.20',
			'port'     => 11211,
			'weight'   => 50,
		);
		break;
	case 'development':
	default:
		$config['memcached'] = array(
			'hostname' => '172.18.194.20',// 172.18.67.11
			'port'     => 11211,
			'weight'   => 50,
		);
		break;
}