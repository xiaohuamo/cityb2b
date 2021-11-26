<?php
//
// This code and all components (c) Copyright 2006 - 2016, Wowza Media Systems, LLC. All rights reserved.
// This code is licensed pursuant to the Wowza Public License version 1.0, available at www.wowza.com/legal.
// 
spl_autoload_register( function( $className ) {
	$arr = explode( '\\', $className );
	$className = array_pop( $arr );
	$dirs = array( 'lib', 'lib/entities/application', 'lib/entities', 'lib/entities/application/helpers' );
	foreach ( $dirs as $dir ) {
		$file = CORE_DIR.'broadcastAPI/'.$dir.'/class.'.strtolower( $className ).'.php';
		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
});
?>
