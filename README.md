
# JCSS: Combine and Compress CSS &amp; JS Files with PHP.

### What is it used for?
- It combines JS/CSS files into one file to lessen HTTP requests made to the server.
- It compresses the combined file with gzip to lower the transfer size.

For example if you have 10 JS files and 12 CSS files served on your web site, your web server will serve 22 requests for these static files. With JCSS, this will be reduced to only 2 requests. (One for JS and one for CSS)

### How does it work?
- It takes a dynamic list of JS and CSS files and combine them into a single file.
- Compresses that single file to decrease the HTTP transfer size.
- Caches the compressed content so it shouldn't load the server at each request. It sends a "HTTP 304 Not Modified" header to the clients.
- It scans the files and in case of a modification, it automatically rebuilds a new cache deleting the old ones.

### Why would I need it while there is already HTTP/2.0?
HTTP/2.0 allows concurrent downloading of static files. Hence combining JS and CSS files are not as useful as it is with HTTP/1.0. It is true that combining JS and CSS files while serving with HTTP/2.0 wouldn't make a huge difference on the site load time, if only a simple combination applied. Nonetheless, not only the load time for the client is important, but using server resources efficiently is also imporant. Whether if load time is not effected that much, your server will still have served 2 requests instead of 22. Additionally, JCSS not only combines but also serves compressed content to the client.

Today, all of the modern browsers support compressed content. So in our example, 22 static files would make up lets say a total of 4MBs. With JCSS compression enabled, it will be served like 1MBs. That means it will decrease the load time too, even if you are using HTTP/2.0. Shortly, JCSS will lower the transfer size and request count which will both improve load performance and efficiency of the server.

### Why use this while there are already other similar tools?
There are a quite number of good tools which attain similar results but;
- JCSS has a very much smaller footprint.
- It doesn't depend on external libraries.
- Other tols usually don't serve content compressed with gzip, instead they minify the code.
- Most of the other tools work statically, so you have to create combined files prior to site publishing and repeat the process each time you add a new file or change anything in the existing files. On the other hand, JCSS works dynamically to load JS and CSS for each page which can be altered at runtime. That means you can have as many as JS and CSS files listed for each page dynamically, without the need of producing a combined file for each list. So with JCSS, you don't have to run CLI commands or manually merge files before you can use and you don't have to repeat this whole process each time you change anything in combined JS and CSS files.

## How to use?
Your web site might have different JS and CSS files to load in different pages. With JCSS, you can serve them dynamically according to the current page, without creating combined files for each page separately. See config.php to set your env variables. Then as in example.php, define a list of JS and CSS files for that specific page like;

```php
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

// Create file list for this page. (Do NOT Modify)
foreach (array('css', 'js') as $type) {
	${$type . '_cache'} = md5(implode(',', ${$type . '_files'}));
	// This specific js/css list is not cached before
	if (!is_file($env['cache'] . '/' . $type . '-' . ${$type . '_cache'}))
		file_put_contents($env['cache'] . '/' . $type . '-' . ${$type . '_cache'}, implode("\n", ${$type . '_files'}));
}
```
This will create a "file list" in your cache folder. Now you can include JCSS as your stylesheet and as your script in an appropriate place like:
```html
<link rel="stylesheet" href="<?=$env['base'];?>/jcss.php?t=c&f=<?=$css_cache;?>">
<script src="<?=$env['base'];?>/jcss.php?t=j&f=<?=$js_cache;?>"></script>
```
### Variables
You can use two variables (or even add more per your wish) in the file list:
- `{base}` will match your main path.
- `{template}` will match your template path.

## Notes
- Combined content will have file name of each block at its top. So when debugging, you can check in which file that code is by looking at the top of that block.
- JCSS will not "minify" the JS and CSS content. It is better to use minified files with JCSS.
- JCSS can be used with remote files as well; but this is almost senseless if they are hosted on a CDN.
- JCSS is influenced by the idea of "Combine" script of [Rakaz](http://rakaz.nl/code/combine).
- JCSS is licensed under GNU GPL v3.0
