<?php

class CustomfieldAdvancedUtil {

	public static function createHash($v){
		return substr(md5($v), 0, 6);
	}
}
