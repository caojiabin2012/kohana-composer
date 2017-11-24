<?php
defined('SYSPATH') or die('No direct script access.');
return array(
	'default' => array(
		'type'			=> 'PDO',
		'connection'	=> array(
            'dsn'	=> 'mysql:host=127.0.0.1;dbname=jiabin',
			'database'	=> 'jiabin',
			'username'	=> 'root',
			'password'	=> 'root',
			'persistent'=> FALSE,
            'options' => NULL,
		),
		'table_prefix'	=> 'jiabin_',
		'charset'		=> 'utf8',
		'caching'		=> FALSE,
		'profiling'		=> TRUE
	)

);
