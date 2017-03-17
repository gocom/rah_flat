<?php

// Either copy classTextile.php to your plugin directory, or uncomment the following
// line and edit it to give the location where classTextile.php can be found
#ini_set('include_path', ini_get('include_path') . ':/full/path/to/textile');

if (empty($test)) {
	exit(compile_plugin());
}

// -----------------------------------------------------

function extract_section($lines, $section) {
	$result = "";
	
	$start_delim = "# --- BEGIN PLUGIN $section ---";
	$end_delim = "# --- END PLUGIN $section ---";

	$start = array_search($start_delim, $lines) + 1;
	$end = array_search($end_delim, $lines);

	$content = array_slice($lines, $start, $end-$start);

	return join("\n", $content);

}

function compile_plugin($file='') {
	global $plugin;

	if (empty($file))
		$file = $_SERVER['SCRIPT_FILENAME'];

	if (!isset($plugin['name'])) {
		$plugin['name'] = basename($file, '.php');
	}

	# Read the contents of this file, and strip line ends
	$content = file($file);
	for ($i=0; $i < count($content); $i++) {
		$content[$i] = rtrim($content[$i]);
	}

	$plugin['help'] = trim(extract_section($content, 'HELP'));
	$plugin['code'] = extract_section($content, 'CODE');

	// textpattern will textile it, and encode html
	$plugin['help_raw'] = $plugin['help'];

	// This is for bc; and for help that needs to use
	@include('classTextile.php');
	if (class_exists('Textile')) {
		$textile = new Textile();
		$plugin['help'] = $textile->TextileThis($plugin['help']);
	}

	$plugin['md5'] = md5( $plugin['code'] );

	$header = <<<EOF
# {$plugin['name']} v{$plugin['version']}
# {$plugin['description']}
# {$plugin['author']}
# {$plugin['author_uri']}

# ......................................................................
# This is a plugin for Textpattern - http://textpattern.com/
# To install: textpattern > admin > plugins
# Paste the following text into the 'Install plugin' box:
# ......................................................................
EOF;

	$body = trim(chunk_split(base64_encode(gzencode(serialize($plugin))), 72));

	// to produce a copy of the plugin for distribution, load this file in a browser. 
	header('Content-type: text/plain');

	return $header."\n\n".$body;
}

?>