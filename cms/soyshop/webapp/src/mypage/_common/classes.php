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

	if(! preg_match('/' . $validEmail . '/i', $email) ) {
		return false;
	}

	return true;
}

/**
 * MyPage Base Class
 */
class MainMyPagePageBase extends WebPage{

	public $errors = array();

    /**
     * @override HTMLPage::getTemplateFilePath()
     */
    function getTemplateFilePath(){

    	if(defined("SOYSHOP_MYPAGE_PATH")){
    		$html = SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR . self::createDirectory(SOYSHOP_MYPAGE_PATH) . ".html";
    	}else{
    		$html = SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html";
    	}

		if(file_exists($html)){
			return $html;
		}

		if(DEBUG_MODE){
			echo "<p>Custom Template Not Found: " . $html . "</p>";
		}

		return SOYSHOP_DEFAULT_MYPAGE_TEMPLATE_DIR . get_class($this) . ".html";
    }

    function createDirectory($path){
    	$array = explode(".", $path);
    	return implode("/", $array);
    }

   	function addForm($id, $arguments = array()){

   		$url = (!isset($arguments["action"])) ? @$_SERVER["REQUEST_URI"] : $arguments["action"];

   		$arguments["action"] = $url;
   		$this->createAdd($id, "HTMLForm", $arguments);
   	}

	//常に同じマイページロジックを取得できるように
	function getMyPage(){
		static $mypage;
		if(is_null($mypage)) $mypage = MyPageLogic::getMyPage();
		return $mypage;
	}

	//ログインを確認して、ログインしていなければloginへジャンプする
	function checkIsLoggedIn(){
		if(!self::getIsLoggedIn()) self::jump("login");
	}

	function getIsLoggedIn(){
		return self::getMyPage()->getIsLoggedin();
	}

	function getUserId(){
		return self::getMyPage()->getUserId();
	}

	function jump($addr=""){
		$url = soyshop_get_mypage_url() . "/" . $addr;
		if(isset($_GET["r"])){
			$url .= "?r=" . $_GET["r"];
		}
		SOY2PageController::redirect($url);
   		exit;
	}
	/**
	 * top
	 */
	function jumpToTop(){
		$top = SOYShop_DataSets::get("config.mypage.top", "top");
		SOY2PageController::redirect(soyshop_get_mypage_url() . "/" . $top);
   		exit;
	}

	function getUser(){
		return self::getMyPage()->getUser();
	}

	function getUserByProfileId($profileId){
		return self::getMyPage()->getProfileUser($profileId);
	}

	/**
	 * @TODO 実装箇所の見直し
	 */
   	function __call($func,$arguments){
		if(preg_match('/^add([A-Za-z]+)$/', $func, $tmp) && count($arguments) > 0){
			$class = "HTML" . $tmp[1];
			if(class_exists($class)){
				$id = array_shift($arguments);
				$arguments  = (isset($arguments[0]) && count($arguments) > 0 && is_array($arguments[0])) ? $arguments[0] : array();
				$this->createAdd($id,$class,$arguments);

				//automatic add XXX_text
				if(array_key_exists("value", $arguments)){
					$this->createAdd($id . "_text", "HTMLLabel", array(
						"text" => $arguments["value"]
					));
				}

				//automatic add XXX_text for textarea
				if(($func == "addTextarea") && isset($arguments["text"])){
					$this->createAdd($id . "_text", "HTMLLabel", array(
						"text" => $arguments["text"]
					));
				}

				return;
			}
		}
	}

	function getOrderByIdAndUserId($orderId, $userId){
		$order = soyshop_get_order_object($orderId);
		if((int)$order->getUserId() !== (int)$userId) $order = new SOYShop_Order();
		return $order;
	}

	function getItemOrdersByOrderId($orderId){
		static $itemOrders;
		if(is_null($itemOrders)){
			try{
	            $itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($orderId);
	        }catch(Exception $e){
	            $itemOrders = array();
	        }
		}
		return $itemOrders;
	}

	function getItemCodeByItemId($itemId){
		return soyshop_get_item_object($itemId)->getCode();
	}

	function getModuleByOrderIdAndUserId($orderId, $userId){
		static $module;
		if(is_null($module)){
			$moduleId = null;
			foreach(self::getOrderByIdAndUserId($orderId, $userId)->getModuleList() as $modId => $mod){
				if($mod->getType() === "delivery_module") {
					$moduleId = $modId;
					break;
				}
			}
			$module = soyshop_get_plugin_object($moduleId);
		}
		return $module;
	}

	/* check */
	function checkUnDeliveried($orderId, $userId){
		$order = self::getOrderByIdAndUserId($orderId, $userId);
        if(!$order->isOrderDisplay()) return false;

		//新規受付2、受付完了3のみtrue
		$status = (int)$order->getStatus();
		return ($status === SOYShop_Order::ORDER_STATUS_REGISTERED || $status === SOYShop_Order::ORDER_STATUS_RECEIVED);
	}

	function checkUsedDeliveryModule($orderId, $userId){
		$module = self::getModuleByOrderIdAndUserId($orderId, $userId);
		return (!is_null($module->getPluginId()));
	}

	/** mypage edit common **/
	function getHistoryText($label, $old, $new){
		return $label . "を『" . $old . "』から『" . $new . "』に変更しました";
	}

	/* convert */

	function _trim($str){
		return trim($str);
	}

	function convertKana($str){
		return mb_convert_kana(self::_trim($str), "CK", "UTF-8");
	}

	function convertDate($date){
		return mktime(0, 0, 0, $date["month"], $date["day"], $date["year"]);
	}

	function convertDateText($date){
		return date("Y-m-d", $date);
	}

	/* error */

	function getErrors(){
		return $this->errors;
	}

	function setErrors($errors){
		$this->errors = $errors;
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

class MainMyPageErrorList extends HTMLList{

	function populateItem($entity){

		$this->addLabel("error_message", array(
			"text" => $entity
		));
	}
}
