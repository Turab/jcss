<?php

// You better have a config file to define env variables.
$env['cache'] = '/tmp/cache';
$env['base'] = 'https://example.com';

// Dynamically adjust the JS and CSS file list per page, per your wish
$js_files = array(
	'{base}/js/jquery.min.js',
	'{base}/js/spinner.js',
	'{template}/js/somejs_file.js',
);

$css_files = array(
	'{base}/css/jquery-ui.css',
	'{template}/css/nightwolf.css',
);

// CSS and JS cache
foreach (array('css', 'js') as $type) {
	${$type . '_cache'} = md5(implode(',', ${$type . '_files'}));

	// This specific js/css list is not cached before
	if (!is_file($env['cache'] . '/' . $type . '-' . ${$type . '_cache'}))
		file_put_contents($env['cache'] . '/' . $type . '-' . ${$type . '_cache'}, implode("\n", ${$type . '_files'}));
}

?>
<html>
<head>
	<link rel="stylesheet" href="<?=$env['base'];?>/jcss.php?t=c&f=<?=$css_cache;?>">
</head>
<body>
	Site Content
	<script src="<?=$env['base'];?>/jcss.php?t=j&f=<?=$js_cache;?>"></script>
</body>
</html>	
		
