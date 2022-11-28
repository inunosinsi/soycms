<?php
class ErrorMessageLabel extends HTMLLabel{

    function getVisible(){
    	return (strlen($this->getText()) > 0);
    }
}

class NumberFormatLabel extends HTMLLabel{

	function getObject(){
		$str = parent::getObject();
		return (strlen($str) > 0) ? soy2_number_format($str) : "";
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
 * MyPage Base Class
 */
class MobileMyPagePageBase extends WebPage{

	public $errors = array();

    /**
     * @override HTMLPage::getTemplateFilePath()
     */
//    function getTemplateFilePath(){
//		if(file_exists(SOYSHOP_MOBILE_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html")){
//			return SOYSHOP_MOBILE_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html";
//		}
//
//		if(DEBUG_MODE){
//			echo "<p>Custom Template Not Found: " . SOYSHOP_MOBILE_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html</p>";
//		}
//
//		return SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html";
//    }

   	function addForm($id,$arguments = array()){

   		$url = (!isset($arguments["action"])) ? @$_SERVER["REQUEST_URI"] : $arguments["action"];

   		$arguments["action"] = $url;
   		$this->createAdd($id,"HTMLForm",$arguments);
   	}


	function getIsLoggedIn(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getIsLoggedin();
	}

	function getUserId(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getUserId();
	}

	function jump($addr,$session=false){
		if(!$session){
			$session = $this->addSessionId();
		}

		$url = soyshop_get_mypage_url() . "/" . $addr;
		if($session){
			$url = $url."?".session_name() . "=" . session_id();
		}
		SOY2PageController::redirect($url);
   		exit;
	}
	/**
	 * top
	 */
	function jumpToTop($session=false){
		if(!$session){
			$session = $this->addSessionId();
		}
		$top = SOYShop_DataSets::get("config.mypage.top","top");
		$url = soyshop_get_mypage_url() . "/" . $top;
		if($session){
			$url = $url."?".session_name() . "=" . session_id();
		}
		SOY2PageController::redirect($url);
   		exit;
	}

	/*
	 * モバイルでのアクセス、かつ設定で「Cookie非対応機種でのURLにセッションID付加」を許可、かつキャリアがdocomoの場合はセッションIDを付ける
	 */
	protected function addSessionId(){
		$session = false;
		if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
			if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
				$session = true;
			}
		}
		return $session;
	}


	function getUser(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getUser();
	}

	/**
	 * @TODO 実装箇所の見直し
	 */
   	function __call($func,$arguments){
		if(preg_match('/^add([A-Za-z]+)$/',$func,$tmp) && count($arguments) > 0){
			$class = "HTML" . $tmp[1];
			if(class_exists($class)){
				$id = array_shift($arguments);
				$arguments  = (count($arguments)>0 && is_array($arguments[0])) ? @$arguments[0] : array();
				$this->createAdd($id,$class,$arguments);

				//automatic add XXX_text
				if(array_key_exists("value",$arguments)){
					$this->createAdd($id . "_text","HTMLLabel", array(
						"text" => $arguments["value"]
					));
				}

				//automatic add XXX_text for textarea
				if(($func == "addTextarea") && isset($arguments["text"])){
					$this->createAdd($id . "_text","HTMLLabel", array(
						"text" => $arguments["text"]
					));
				}

				return;
			}
		}
	}

	/* convert */

	function _trim($str){
		return trim($str);
	}

	function convertKana($str){
		$str = trim($str);
		return mb_convert_kana($str,"CK","UTF-8");
	}

	/* error */

	function getErrors(){
		return $this->errors;
	}

	function setErrors($errors){
		$this->errors = $errors;
	}

}

class MobileMyPageErrorList extends HTMLList{

	function populateItem($entity){

		$this->createAdd("error_message","HTMLLabel", array(
			"text" => $entity
		));

	}

}

?>
