<?php
class ErrorMessageLabel extends HTMLLabel{

    function getVisible(){
    	return (strlen($this->getText()) > 0);
    }
}

class NumberFormatLabel extends HTMLLabel{

	function getObject(){
		$str = parent::getObject();
		return (strlen($str) > 0) ? number_format($str) : "";
	}
}

function tstrlen($str){
	return strlen(trim($str));
}

function isValidEmail($email){
	$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
	$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
	$d3     = '\d{1,3}';
	$ip     = $d3 . '\.' . $d3 . '\.' . $d3 . '\.' . $d3;
	$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

	if(! preg_match('/' . $validEmail . '/i', $email) ) {
		return false;
	}

	return true;
}

/**
 * Cart Base Class
 */
class MainCartPageBase extends WebPage{

    /**
     * @override HTMLPage::getTemplateFilePath()
     */
    function getTemplateFilePath(){
		if(file_exists(SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html")){
			return SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html";
		}

		if(DEBUG_MODE){
			echo "<p>Custom Template Not Found: " . SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html</p>";
		}

		return SOYSHOP_DEFAULT_CART_TEMPLATE_DIR . get_class($this) . ".html";
    }
    
    /* convert */
    
	function _trim($str){
		return trim($str);
	}
	
	function convertKana($str){
		$str = trim($str);
		return mb_convert_kana($str, "CK", "UTF-8");
	}
	
	function buildModules(){
		$plugin = new SOYShopPageModulePlugin();

		while(true){
			list($tag, $line, $innerHTML, $outerHTML, $value, $suffix, $skipendtag) =
				$plugin->parse("module", "[a-zA-Z0-9\.\_]+", $this->_soy2_content);

			if(!strlen($tag)) break;

			$plugin->_attribute = array();

			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin, $this->_soy2_content);
		}
	}
}
?>