<?php

/**
* Cookie
* @author Curitis Niewei
* @version 2.0
* @created 07-04-2012
*/

class Cookie {
	/**
	* 设置Cookie，支持一维数组
	*/
	function setCookie( $key, $val, $expire = null, $path = '/' ) {
		if ( is_array( $val ) ) {
			foreach ( $val as $k => $v ) {
				self::setCookie( $key.'['.$k.']', $v, $expire, $path );
			}
		}
		else {
			if ( is_null( $expire ) ) {
				if ( defined( 'CMS_COOKIE_LIFE_TIME' ) ) {
					$expire = CMS_COOKIE_LIFE_TIME * 60;
				}
				else {
					$expire = 60 * 60;
				}
			}
			$expire = time() + (int)$expire;
			if ( $val ) {
				setcookie( $key, $val, $expire, $path );
			}
			else {
				setcookie( $key, $val, -1, $path );
			}
		}
	}

	/**
	* 清除Cookie，支持数组
	*/
	function clearCookie( $key ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $v ) {
				self::clearCookie( $v );
			}
		}
		else {
			self::setCookie( $key, null, -1 );
		}
	}

	/**
	* 清除数组形式的Cookie，$key不支持数组，$val支持一维数组
	*/
	function clearArrayCookie( $key, $array_key ) {
		if ( is_array( $array_key ) ) {
			foreach ( $array_key as $k => $v ) {
				self::setCookie( $key.'['.$k.']', null, -1 );
			}
		}
		else {
			self::setCookie( $key.'['.$array_key.']', null, -1 );
		}
	}

	/**
	* 获取Cookie
	*/
	function getCookie( $key ) {
		return isset( $_COOKIE[$key] ) ? $_COOKIE[$key] : false;
	}
}
?>