<?php
class ErrorMessageLabel extends HTMLLabel{

    function getVisible(){
    	return (strlen($this->getText()) > 0);
    }
}

class NumberFormatLabel extends HTMLLabel{

	function getObject(){
		return soy2_number_format(parent::getObject());
	}
}

function tstrlen($str){
	return strlen(trim($str));
}

function isValidEmail($email){
	$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
	$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
	$d3     = '\d{1,3}';
	$ip     = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
	$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

	if(! preg_match('/'.$validEmail.'/i', $email) ) {
		return false;
	}

	return true;
}

/**
 * Cart Base Class
 */
class MobileCartPageBase extends WebPage{

    /**
     * @override HTMLPage::getTemplateFilePath()
     */
    function getTemplateFilePath(){
		if(file_exists(SOYSHOP_MOBILE_CART_TEMPLATE_DIR . get_class($this) . ".html")){
			return SOYSHOP_MOBILE_CART_TEMPLATE_DIR . get_class($this) . ".html";
		}

		if(DEBUG_MODE){
			echo "<p>Custom Template Not Found: " . SOYSHOP_MOBILE_CART_TEMPLATE_DIR . get_class($this) . ".html</p>";
		}

		return SOYSHOP_DEFAULT_CART_TEMPLATE_DIR . get_class($this) . ".html";
    }

    /* convert */

	function _trim($str){
		return trim($str);
	}

	function convertKana($str){
		$str = trim($str);
		return mb_convert_kana($str,"CK","UTF-8");
	}
}
?>
