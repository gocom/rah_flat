<?php

/**
 * @const rah_flat_cfg Path to configuration file.
 *
 * Sets where the configuration file, rah_flat.config.xml, is located.
 * If undefined, rah_flat.config.xml is looked from Textpattern's installation 
 * directory, e.g. /textpattern/, the directory that hosts config.php and publish.php
 * files.
 *
 * If rah_flat.config.xml is located somewhere else (which it should) place the line
 * below to your Textpattern's configuration file (/textpattern/config.php). Used value
 * should be an absolute filesystem path.
 *
 * <code>
 *		define('rah_flat_cfg', '/home/user/path/to/rah_flat.config.xml');
 * </code>
 *
 * If the constant is set to FALSE, rah_flat's functionality is disabled.
 */

	define('rah_flat_cfg', '/absolute/path/to/rah_flat.config.xml');

?>