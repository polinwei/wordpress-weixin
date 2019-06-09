<?php

function wpwx_plugin_url( $path = '' ) {
	$url = plugins_url( $path, WPWX_PLUGIN );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}

function wpwx_array_flatten( $input ) {
	if ( ! is_array( $input ) ) {
		return array( $input );
	}

	$output = array();

	foreach ( $input as $value ) {
		$output = array_merge( $output, wpwx_array_flatten( $value ) );
	}

	return $output;
}
