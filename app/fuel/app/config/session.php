<?php

return array(
	'driver' => 'file',

	'file' => array(
		'path' => APPPATH.'tmp'.DS.'sessions'.DS,
	),

	'expiration_time' => 7200,
);