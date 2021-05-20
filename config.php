<?php

$env = array(
	'cache_jcss' => true, // Cache combined CSS & JS files for performance
	'compress_jcss' => true, // Compress CSS & JSS files with gzip to lower HTTP traffic
	'cache' => '/tmp/cache', // Where to store cache files
	'path' => '/var/www/vhosts/example.com/html', // Your base path
	'template' => '/var/www/vhosts/example.com/html/templates/nightwolf', // Your template files
	'base' => 'https://example.com'; // You sites base URL
);
