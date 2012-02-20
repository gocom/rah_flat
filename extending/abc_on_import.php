<?php

/**
 * This is an example plugin. It showcases rah_flat's extending capabilities.
 * The plugin will remove whitespace from all imported forms.
 *
 * @package rah_flat
 * @author Jukka Svahn
 * @date 2012-
 * @link https://github.com/gocom/rah_flat
 * @license GPLv2
 */

/**
 * Hook to rah_flat.importing callback event.
 */

	if(@txpinterface == 'public' || @txpinterface == 'admin') {
		register_callback('abc_on_import', 'rah_flat.importing');
	}

/**
 * Removes whitespace from forms.
 * @param string $event Callback event.
 * @param string $step Callback step.
 * @param string $table DB table imported to.
 * @return nothing
 * @see rah_flat::row()  
 */

	function abc_on_import($event='', $step='', $table='') {
		
		/*
			End here if table is something else than txp_form
		*/
		
		if($table != 'txp_form')
			return;
		
		/*
			Get data using rah_flat::row(). It retuns an array.
		*/
		
		$data = rah_flat::row();
		
		/*
			Modify data
		*/
		
		$data['Form'] = str_replace(array("\n", "\r", "\t"), '', $data['Form']);
		
		/*
			Set data using rah_flat::row()
		*/
		
		rah_flat::row($data):
	}

?>