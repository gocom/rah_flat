<?php

/**
 * Rah_flat, a plugin for Textpattern CMS.
 * Edit data in database tables (forms, pages) as flat files.
 *
 * @package rah_flat
 * @author Jukka Svahn <http://rahforum.biz>
 * @copyright (c) 2012 Jukka Svahn
 * @license GNU GPLv2
 * @link https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Requires Textpattern v4.4.1 (or newer) and PHP v5 (or newer) 
 */

	if(defined('txpinterface')) {
		rah_flat::get()->import();
	}

class rah_flat {
	
	/**
	 * @var obj Stores instances
	 */
	
	static public $instance = NULL;

	/**
	 * @var array Current file
	 */

	static public $row_data = array();
	
	/**
	 * @var array Synced directories
	 */
	
	static public $sync = NULL;
	
	/**
	 * @var array Currently imported directory
	 */
	
	private $current = NULL;
	
	/**
	 * @var array Global configuration
	 */
	
	private $cfg = array();

	/**
	 * @var array Database cache containing MD5 checksums
	 */

	private $db_cache = array();

	/**
	 * Initialize importer
	 */

	public function __construct() {
		
		if(self::$sync !== NULL) {
			return;
		}
		
		self::$sync = array();
		
		if(!defined('rah_flat_cfg')) {
			define('rah_flat_cfg', txpath.'/rah_flat.config.xml');
		}
		
		if(!rah_flat_cfg || !file_exists(rah_flat_cfg) || !is_readable(rah_flat_cfg) || !is_file(rah_flat_cfg)) {
			return;
		}
		
		$cfg = file_get_contents(rah_flat_cfg);
		
		if(!$cfg) {
			return false;
		}
		
		try {
			@$r = new SimpleXMLElement($cfg);
		}
		catch(Exception $e){
			return false;
		}
		
		if(!$r->sync->directory[0]) {
			return false;
		}
		
		$this->cfg = $this->lAtts(array(
			'enabled' => 1,
			'callback_uri' => array('key' => '', 'enabled' => 0),
		), $this->xml_to_array($r->options));
		
		if(
			$this->cfg['enabled'] != 1 &&
			(
				$this->cfg['callback_uri']['enabled'] != 1 || 
				$this->cfg['callback_uri']['key'] != gps('rah_flat')
			)
		)
			return;
		
		foreach($r->sync->directory as $p) {

			$p = $this->lAtts(array(
				'enabled' => 1,
				'delete' => 0,
				'create' => 1,
				'ignore_empty' => 1,
				'exportable' => 1,
				'path' => NULL,
				'extension' => 'txp',
				'database' => array('table' => '', 'primary' => '', 'contents' => ''),
				'filename' => array(),
				'ignore' => array(),
				'disable_event' => '',
				'format' => 'flat',
			), $this->xml_to_array($p));
			
			if($p['enabled'] != 1 || !$p['path'] || !$p['filename']) {
				continue;
			}

			if(!empty($p['disable_event']) && txpinterface == 'admin') {
				unset($GLOBALS['txp_permissions'][$p['disable_event']]);
			}
			
			$filename = array();
			
			foreach($p['filename'] as $var => $att) {
				
				$att = $this->lAtts(array(
					'@attributes' => array('starts' => 0, 'ends' => NULL),
				), $att);
				
				$filename[$var] = $att['@attributes'];
			}
			
			$p['filename'] = $filename;
			
			self::$sync[] = $p;
		}
	}
	
	/**
	 * Gets an instance of the class
	 * @return obj
	 */
	
	static public function get() {
		
		if(self::$instance === NULL) {
			self::$instance = new rah_flat();
		}
		
		return self::$instance;
	}
	
	/**
	 * Returns and sets row data
	 * @param array $data
	 * @return array
	 */
	
	static public function row($data=NULL) {
		if(is_array($data)) {
			self::$row_data = $data;
		}
		return self::$row_data;
	}

	/**
	 * Converts SimpleXML's object to multidimensional array
	 * @param obj $obj
	 * @param array $out
	 * @return array
	 */

	protected function xml_to_array($obj, $out = array()) {
		foreach((array) $obj as $key => $node)
			$out[$key] = is_object($node) || is_array($node) ? $this->xml_to_array($node) : $node;
		return $out;
	}

	/**
	 * Converts array to XML
	 * @param array $input
	 * @param string $key
	 * @param int $indent
	 * @return string
	 */

	protected function array_to_xml($input, $key=NULL, $indent=1) {
		
		if($key !== NULL) {
		
			if(!$key || is_numeric($key)) {
				return false;
			}
		
			return $key;
		}
		
		if(is_scalar($input)) {
			
			if(
				strpos($input, '<![CDATA[') !== false || 
				strpos($input, ']]>') !== false ||
				is_null($input) ||
				is_bool($input)
			) {
				return false;
			}
			
			if(
				strpos($input, '<') !== false || 
				strpos($input, '>') !== false || 
				strpos($input, '&') !== false
			) {
				$input = '<![CDATA['.$input.']]>';
			}
			
			return $input;
		}
		
		elseif(!is_array($input)) {
			return false;
		}
		
		$out = $tab = array();
		
		foreach($input as $name => $value) {
			
			$name = $this->array_to_xml(NULL, $name);
			$value = $this->array_to_xml($value, NULL, $indent+1);
			
			if($name === false || $value === false) {
				return false;
			}
			
			$tab = array();
			
			for($i=0; $i < $indent; $i++) {
				$tab[] = '	';
			}
			
			$out[] = n.implode('', $tab).'<'.$name.'>'.$value.'</'.$name.'>';
		}
		
		if($out) {
			$out[] = n.implode('', array_slice($tab, 1));
		}
		
		if($indent == 1) {
			return '<item>'.implode('', $out).'</item>';
		}
		
		return implode('', $out);
	}

	/**
	 * Imports flat static files to the database
	 * @param array $p Configuration options.
	 * @return nothing
	 */
	
	public function import($p=NULL) {
	
		if($p === NULL) {
			foreach(self::$sync as $p) {
				$this->current = $p;
				$this->import($p);
			}
			return;
		}
		
		extract($p);

		$this->collect_items(
			$database['table'],
			$database['primary'], 
			$database['contents']
		);
		
		if(empty($this->current['db_columns'])) {
			return;
		}

		$f = new rah_flat_files();

		foreach($filename as $var => $att) {
			$f->map($var, $att['starts'], $att['ends']);
		}
		
		foreach($f->read($path, $extension) as $file => $data) {
			
			$d = $f->parse($file);
			
			if(in_array($d[$database['primary']], (array) $ignore)) {
				continue;
			}

			$status = 
				$this->requires_task(
					$database['table'],
					$d[$database['primary']],
					$data
				);
			
			if(!$status) {
				continue;
			}
			
			if($format != 'xml') {
				$d[$database['contents']] = $data;
			}
			
			if($format == 'flat_meta') {
				
				if(
					substr($file, -9) == '.meta.xml' ||
					!file_exists($file.'.meta.xml') || 
					!is_readable($file.'.meta.xml') || 
					!is_file($file.'.meta.xml')
				)
					continue;
				
				$data = file_get_contents($file.'.meta.xml');
			}
			
			/*
				Parse XML data
			*/
			
			if($format == 'flat_meta' || $format == 'xml') {
				
				try {
					@$r = new SimpleXMLElement($data, LIBXML_NOCDATA);
				}
				
				catch(Exception $e){
					trace_add('[rah_flat: Invalid XML document '.$file.']');
					continue;
				}
				
				if(!$r) {
					continue;
				}
				
				$d = array_merge((array) $d, $this->xml_to_array($r));
			}
			
			self::row($d);
			callback_event('rah_flat.importing', '', '', $database['table'], $status);
			
			$sql = array();
			
			foreach(self::row() as $name => $value) {
				if(!is_array($value) && in_array(strtolower($name), $this->current['db_columns'])) {
					$sql[$name] = "`{$name}`='".doSlash($value)."'";
				}
			}
			
			if(!$sql) {
				continue;
			}

			if($status == 'insert' && $p['create'] == 1) {
				safe_insert(
					$database['table'],
					implode(',', $sql)
				);
			}
			
			elseif($status == 'update') {
				safe_update(
					$database['table'],
					implode(',', $sql),
					$sql[$database['primary']]
				);
			}
			
			$site_updated = true;
			self::row(array());
		}
		
		if(isset($site_updated)) {
			update_lastmod();
		}
		
		if($p['delete'] == 1) {
			
			$delete = array();

			foreach($this->db_cache[$database['table']] as $name => $md5) {
				if(($md5 !== false || $p['ignore_empty'] != 1) && !in_array($name, (array) $ignore)) {
					callback_event('rah_flat.deleting', '', '', $database['table'], $name);
					$delete[] = "'".doSlash($name)."'";
				}
			}
			
			if($delete) {
				safe_delete(
					$database['table'],
					$database['primary'].' in('.implode(',', $delete).')'
				);
			}
		}
	}
	
	/**
	 * Exports rows to flat files
	 * @param array $p
	 * @return nothing
	 */
	
	public function export($p=NULL) {
		
		if($p === NULL) {
			
			foreach(self::$sync as $p) {
				$this->current = $p;
				$this->export($p);
			}
			
			return;
		}
		
		extract($p);
		
		if($exportable != 1)
			return;
		
		$in = implode(',', quote_list($ignore));
		
		@$rs = 
			safe_rows(
				'*',
				$database['table'],
				$in ? $database['primary'] . ' not in('.$in.')' : '1=1'
			);
		
		if(!$rs)
			return;
		
		$write = array();
		
		foreach($rs as $a) {

			self::row($a);
			$name = array();

			callback_event('rah_flat.exporting', '', '', $database['table']);
			$a = self::row();
			
			foreach($filename as $var => $att) {
			
				if(!isset($a[$var])) {
					$name = array();
					break;
				}
			
				$name[$var] = $a[$var];
				unset($a[$var]);
			}
			
			$name = implode('.', $name);
			
			if(!$name || !preg_match('=^[^/?*;:{}\\\\]+$=', $name)) {
				continue;
			}
			
			$name = $name.'.'.$extension;
			
			if($format == 'flat_meta' || $format == 'flat') {
				
				if(!isset($a[$database['contents']])) {
					continue;
				}
				
				$write[$name] = $a[$database['contents']];
				unset($a[$database['contents']]);
			}
			
			if($format == 'xml' || $format == 'flat_meta') {
				
				if($format == 'flat_meta') {
					$name += '.meta.xml';
				}
			
				$out = $this->array_to_xml($a);
				
				if(!$out) {
					continue;
				}
				
				$write[$name] = $out;
			}
		}
		
		$f = new rah_flat_files();
		$f->write($path, $write);
	}

	/**
	 * Get current data from the database
	 * @param string $table
	 * @param string $name
	 * @param string $content
	 * @return array
	 */

	protected function collect_items($table, $name, $content) {
		
		$this->current['db_columns'] = doArray((array) @getThings('describe '.$table), 'strtolower');
		
		$rs = 
			safe_rows(
				$name.','.$content,
				$table,
				'1=1'
			);
		
		foreach($rs as $a) {
			$this->db_cache[$table][(string) $a[$name]] = 
				trim($a[$content]) === '' ? false : md5($a[$content]);
		}
		
		return $this->db_cache;
	}

	/**
	 * Checks items status
	 * @param string $table
	 * @param string $name
	 * @param mixed $content
	 * @return mixed
	 */

	protected function requires_task($table, $name, $content) {
		if(!isset($this->db_cache[$table][$name]))
			return 'insert';
		
		$sum = $this->db_cache[$table][$name];
		unset($this->db_cache[$table][$name]);
		
		if($this->current['format'] == 'xml' || $this->current['format'] == 'flat_meta')
			return 'update';
		
		$md5 = trim($content) === '' ? false : md5($content);
		
		if($md5 === false && $this->current['ignore_empty'] == 1)
			return false;
		
		if($sum === $md5)
			return false;
		
		return 'update';
	}

	/**
	 * Merge and extract two arrays. Populates unset with defaults, discards unknown.
	 * @param $pairs array Defaults options.
	 * @param $atts array User provided options.
	 * @return array
	 */

	protected function lAtts($pairs, $atts) {
		$out = array();

		foreach($pairs as $name => $value) {
			if(!isset($atts[$name]))
				$out[$name] = $value;
			
			else {
				if(is_array($value)) {
					$atts[$name] = (array) $atts[$name];
					$out[$name] = empty($value) ? $atts[$name] : $this->lAtts($value, $atts[$name]);
				} else
					$out[$name] = $atts[$name];
			}
		}

		return $out;
	}
}

/**
 * Handle filesystem tasks, writing, reading.
 */

class rah_flat_files {

	protected $delimiter = '.';
	protected $map = array();
	protected $vars = array();
	protected $files = array();

	/**
	 * Maps filename parts to variables.
	 * @param string $var Variable name
	 * @param int $offset Offset.
	 * @param int $length Length.
	 */

	public function map($var, $offset=NULL, $length=NULL) {
		$this->map[$var] = array($offset, $length);
	}

	/**
	 * Extracts filename's parts as variables
	 * @param string $filename
	 * @return array
	 */
	
	public function parse($filename) {
		$f = array_slice(explode($this->delimiter, basename($filename)), 0, -1);

		foreach($this->map as $var => $cord)
			$this->vars[$var] = implode($this->delimiter, array_slice($f, $cord[0], $cord[1]));
		
		return $this->vars;
	}

	/**
	 * Safely reads files from a directory
	 * @param string $dir Directory to read.
	 * @param string $ext Searched file extension.
	 * @return array List of files
	 */

	public function read($dir, $ext) {
		
		if(strpos($dir, '../') === 0 || strpos($dir, './') === 0)
			$dir = txpath.'/'.$dir;

		$dir = rtrim($dir, '\\/') . '/';
		$ext = trim($ext, '.');
		
		if(
			!file_exists($dir) ||
			!is_readable($dir) ||
			!is_dir($dir)
		)
			return $this->files;

		$dir = glob($this->glob_escape($dir).'*.'.$ext, GLOB_NOSORT);
		
		if(empty($dir) || !is_array($dir))
			return $this->files;
		
		foreach($dir as $file) {
			if(is_file($file) && is_readable($file))
				$this->files[$file] = file_get_contents($file);
		}
		
		return $this->files;
	}

	/**
	 * Safely writes an array of files
	 * @param $dir string Target directory.
	 * @param $files array Array of files to write.
	 * @return bool
	 */

	public function write($dir, $files) {
		
		if(strpos($dir, '../') === 0 || strpos($dir, './') === 0)
			$dir = txpath.'/'.$dir;
		
		$dir = rtrim($dir, '\\/') . '/';
		
		if(
			!file_exists($dir) ||
			!is_readable($dir) ||
			!is_dir($dir)
		)
			return false;
		
		foreach($files as $file => $data) {
		
			if(
				file_exists($dir.$file) && 
				(
					!is_file($dir.$file) || !is_writable($dir.$file)
				)
			)
				continue;
			
			file_put_contents(
				$dir.$file,
				$data
			);
		}
	}

	/**
	 * Escape glob wildcard characters
	 * @param string $filename
	 * @return string
	 */

	public function glob_escape($filename) {
		return preg_replace('/(\*|\?|\[)/', '[$1]', $filename);
	}
}

?>