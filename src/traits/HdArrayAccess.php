<?php namespace hdphp\traits;
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
trait HdArrayAccess {
	public function offsetSet( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	public function offsetGet( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : NULL;
	}

	public function offsetExists( $key ) {
		return isset( $this->data[ $key ] );
	}

	public function offsetUnset( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}
	}

	function rewind() {
		reset( $this->data );
	}

	public function current() {
		return current( $this->data );
	}

	public function next() {
		return next( $this->data );
	}

	public function key() {
		return key( $this->data );
	}

	public function valid() {
		return current( $this->data );
	}
}