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

	if(@txpinterface == 'public' || @txpinterface == 'admin')
		new rah_flat();

class rah_flat {

	private $cfg = array();
	private $db_cache = array();
	private $xml_config = '';
	private $sync = array();
	private $db_columns = array();
	private $format = '';

	/**
	 * Initialize importer
	 */

	public function __construct($task='import') {
		
		$cfg = defined('rah_flat_cfg') ? rah_flat_cfg : txpath . '/rah_flat.config.xml';
		
		if(empty($cfg) || !file_exists($cfg) || !is_readable($cfg) || !is_file($cfg))
			return;
		
		$this->xml_config = file_get_contents($cfg);
		
		if(!$this->xml_config)
			return false;
		
		$r = new SimpleXMLElement($this->xml_config);
		
		if(!$r || !$r->options->enabled || !$r->sync->directory[0])
			return false;

		$this->cfg = $this->lAtts(array(
			'enabled' => 0,
			'delete' => 0,
			'create' => 1,
			'ignore_empty' => 1,
			'callback_uri' => array('key' => '', 'enabled' => 0),
		), $this->xml_array($r->options));
		
		foreach($r->sync->directory as $p) {
		
			if(($p = $this->xml_array($p)) && $p && is_array($p)) {
			
				$this->sync[] = $this->lAtts(array(
					'enabled' => 1,
					'path' => NULL,
					'extension' => 'txp',
					'database' => array('table' => '', 'primary' => '', 'contents' => ''),
					'filename' => array(),
					'ignore' => array(),
					'disable_event' => '',
					'format' => 'flat'
				), $p);
			}
		}
	
		if(
			$this->cfg['enabled'] == 1 ||
			(
				$this->cfg['callback_uri']['enabled'] == 1 && 
				$this->cfg['callback_uri']['key'] == gps('rah_flat')
			)
		)
			$this->import();
	}

	/**
	 * Converts SimpleXML's object to multidimensional array
	 * @param obj $obj
	 * @param array $out
	 * @return array
	 */

	protected function xml_array($obj, $out = array()) {
		foreach((array) $obj as $key => $node)
			$out[$key] = is_object($node) || is_array($node) ? $this->xml_array($node) : $node;
		return $out;
	}

	/**
	 * Imports flat static files to the database
	 * @param array $p Configuration options.
	 */
	
	protected function import($p=NULL) {
	
		if($p === NULL) {
			foreach($this->sync as $p) {
				$this->import($p);
			}
			return;
		}
		
		extract($p);
		
		$this->format = $format;
		
		if(
			$enabled != 1 || 
			!$path || 
			!$filename || 
			!$extension || 
			!preg_match('/^[a-z0-9]+$/i', $extension)
		)
			return;
		
		$this->collect_items(
			$database['table'], 
			$database['primary'], 
			$database['contents']
		);
		
		if(!$this->db_columns)
			return;
		
		if($disable_event && txpinterface == 'admin')
			unset($GLOBALS['txp_permissions'][$disable_event]);

		$f = new rah_flat_files();

		foreach($filename as $var => $att) {
			$att = $this->lAtts(array(
				'@attributes' => array('starts' => NULL, 'ends' => NULL),
			), $att);

			$f->map($var, $att['@attributes']['starts'], $att['@attributes']['ends']);
		}
		
		foreach($f->read($path, $extension) as $file => $data) {
			$d = $f->parse($file);
			
			if(in_array($d[$database['primary']], (array) $ignore))
				continue;

			$status = 
				$this->requires_task(
					$database['table'],
					$d[$database['primary']],
					$data
				);
			
			if(!$status)
				continue;
			
			if($format == 'xml') {
				$r = new SimpleXMLElement($data, LIBXML_NOCDATA);
				
				if(!$r)
					continue;
				
				$d = $this->xml_array($r);
			}
			else
				$d[$database['contents']] = $data;
			
			if($format == 'flat_meta'){
			
				if(
					!file_exists($file.'.meta.xml') || 
					!is_readable($file.'.meta.xml') || 
					!is_file($file.'.meta.xml')
				)
					continue;
			
				$r = new SimpleXMLElement(file_get_contents($file.'.meta'), LIBXML_NOCDATA);
				
				if(!$r)
					continue;
				
				$d = $this->xml_array($r);
			}
			
			$sql = array();
			
			foreach($d as $name => $value)
				if(!is_array($value) && in_array(strtolower($name), $this->db_columns))
					$sql[$name] = "`{$name}`='".doSlash($value)."'";
			
			if(!$sql)
				continue;

			if($status == 'insert' && $this->cfg['create'] == 1)
				safe_insert(
					$database['table'],
					implode(',', $sql)
				);
			
			if($status == 'update')
				safe_update(
					$database['table'],
					implode(',', $sql),
					$sql[$database['primary']]
				);
			
			$site_updated = true;
		}
		
		if(isset($site_updated))
			update_lastmod();
		
		if($this->cfg['delete'] != 1)
			return;
		
		foreach($this->db_cache[$database['table']] as $name => $md5) {
			if(($md5 !== false || $this->cfg['ignore_empty'] != 1) && !in_array($name, (array) $ignore))
				$delete[] = "'".doSlash($name)."'";
		}
		
		if(!isset($delete))
			return;
		
		safe_delete(
			$database['table'],
			$database['primary'].' in('. implode(',', $delete) . ')'
		);
	}

	/**
	 * Get current data from the database
	 * @param string $table
	 * @param string $name
	 * @param string $content
	 * @return array
	 */

	protected function collect_items($table, $name, $content) {
		
		$this->db_columns = doArray((array) @getThings('describe '.$table), 'strtolower');
		
		$rs = 
			safe_rows(
				$name.','.$content,
				$table,
				'1=1'
			);
		
		foreach($rs as $a)
			$this->db_cache[$table][$a[$name]] = 
				trim($a[$content]) === '' ? false : md5($a[$content]);
		
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
		
		if($this->format == 'xml')
			return 'update';
		
		$md5 = trim($content) === '' ? false : md5($content);
		
		if($md5 === false && $this->cfg['ignore_empty'] == 1)
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
	 */
	
	public function parse($filename) {
		$f = array_slice(explode($this->delimiter, basename($filename)), 0, -1);

		foreach($this->map as $var => $cord)
			$this->vars[$var] = implode($this->delimiter, array_slice($f, $cord[0], $cord[1]));
		
		return $this->vars;
	}

	/**
	 * Safely read files from a directory
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
		
		$dir = $this->glob_escape($dir);
		
		foreach(glob($dir.'*.'.$ext , GLOB_NOSORT) as $file)
			if(is_file($file) && is_readable($file))
				$this->files[$file] = file_get_contents($file);
				
		return $this->files;
	}

	/**
	 * Safely write an array of files
	 * @param $dir string Target directory.
	 * @param $files array Array of files to write.
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