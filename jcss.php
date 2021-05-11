<?php

/*******************************************************************
*  JCSS, PHP Javascript and CSS file cache and compression control.
*  Copyright, Turab Garip
*  turabgarip@gmail.com
*  GNU GPL v3.0
*******************************************************************/

include_once './config.php';

// This file can be cached yes.
header('Cache-Control: public');

if (!isset($_GET['t']) || !in_array($_GET['t'], array('c', 'j')) || !isset($_GET['f']))
		exit; // Nothing to serve here.

$type = $_GET['t'] == 'c' ? 'css' : 'js';

if (!is_file($env['cache'] . '/' . $type . '-' . $_GET['f']))
		exit; // Again nothing to serve

$files = explode("\n", str_replace(array('{base}', '{template}'), array($env['path'], $env['template']),
			file_get_contents($env['cache'] . '/' . $type . '-' . $_GET['f']))
);

// Find the newest modified file
$lastmodified = 0;
foreach ($files as $file)
	$lastmodified = max($lastmodified, filemtime($file));

// Send Etag hash and last modified header for these specific files
$hash = $lastmodified . '-' . $_GET['f'];
header("Etag: $hash");
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastmodified) . ' GMT');

if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == $hash) ||
		(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastmodified))
{
	// Tell the browser nothing has changed
	header('HTTP/1.0 304 Not Modified');
	header('Content-Length: 0');
	exit;
}
else
{
	if ($env['compress_jcss'])
	{
		// Which method to compress with
		if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
			$encoding = 'gzip';
		elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false)
			$encoding = 'deflate';
	}
	elseif (!$env['compress_jcss'] || !isset($_SERVER['HTTP_ACCEPT_ENCODING']) || !isset($encoding))
		$encoding = 'none';

	if ($encoding != 'none')
		header ("Content-Encoding: $encoding");
	header("Content-Type: text/$type");

	// First time visit or files were modified
	if ($env['cache_jcss'])
	{
		// Try the cache first to see if the combined files were already generated
		$cachefile = $env['cache'] . '/' . $hash . ($encoding != 'none' ? '.' . $encoding : '') . '.' . $type;
		if (is_file($cachefile))
		{
			if ($fp = fopen($cachefile, 'rb'))
			{
				header('Content-Length: ' . filesize($cachefile));
				fpassthru($fp);
				fclose($fp);
				exit;
			}
		}
	}

	// Not cached?
	$contents = '';
	reset($files);
	foreach ($files as $file)
		$contents .= '/*' . basename($file) . '*/' . PHP_EOL . file_get_contents($file) . PHP_EOL;

	// Send compressed or plain
	if ($encoding != 'none')
		$contents = gzencode($contents, 9, $encoding == 'gzip' ? FORCE_GZIP : FORCE_DEFLATE);
	header('Content-Length: ' . strlen($contents));
	echo $contents;

	// Store cached content
	if ($env['cache_jcss']) {
		// Purge the old cache of this exact set first
		$old_cache = glob($env['cache'] . '/*-' . $_GET['f'] . '*' . $type);
		if (!empty($old_cache))
			array_map('unlink', $old_cache);

		// Then save the new one
		$fp = fopen($cachefile, 'wb');
		fwrite($fp, $contents);
		fclose($fp);
	}
}
