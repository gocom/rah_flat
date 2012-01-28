<?php

/**
 * @const rah_flat_cfg Path to configuration file.
 *
 * Sets where the configuration file, rah_flat.config.xml, is located.
 * If set to FALSE or undefined, rah_flat.config.xml is looked from
 * textpattern installation directory, e.g. /textpattern/, the directory host
 * config.php and publish.php etc.
 *
 * If rah_flat.config.xml is located somewhere else, which it should, place the line
 * below to your Textpattern's configuration file (/textpattern/config.php). Used value
 * should be an absolute filesystem path.
 *
 * <code>
 *		define('rah_flat_cfg', '/home/user/path/to/rah_flat.config.xml');
 * </code>
 */

	define('rah_flat_cfg', false);

?>