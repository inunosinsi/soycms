<?php

class SOYShopDiscountCouponConfigFormPage extends WebPage{

	private $action = "config";//config issue/edit list
	private $config;


    function __construct() {}

    function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["config"])){
		    	if(SOYShopCouponUtil::saveConfig($_POST["config"])){
					$this->config->redirect("updated&action=config");
		    	}else{
		    		$this->config->redirect("error&action=config");
		    	}
	    	}

	    	if(isset($_POST["edit"])){
	    		if($this->checkEdit($_POST["edit"])){
		    		if(isset($_POST["edit"]["id"]) && strlen($_POST["edit"]["id"])){
			    		$id = $_POST["edit"]["id"];
			    		SOYShopCouponUtil::update($id,$_POST["edit"]);
						$this->config->redirect("updated&action=edit&id=" . $id);
		    		}else{
			    		$id = SOYShopCouponUtil::issue($_POST["edit"]);
						$this->config->redirect("issued&action=edit&id=" . $id);
		    		}
	    		}else{
	    			$this->config->redirect("issue_error&action=edit");
	    		}
	    	}

	    	if(isset($_POST["status"])){
	    		$couponList = SOYShopCouponUtil::getCouponList();
	    		foreach($_POST["status"] as $id => $codeStatus){
	    			if(!isset($couponList[$id])) continue;
	    			$coupon = $couponList[$id];
	    			$codes = $coupon->getCouponCodes();
	    			foreach($codeStatus as $code => $status){
	    				$couponCode = SOYShop_DataSets::get("discount.coupon.code." . $code);
	    				$couponCode->setStatus($status);
	    				SOYShop_DataSets::put("discount.coupon.code." . $code, $couponCode);
	    				$codes[$code] = $couponCode;
	    			}
	    			$coupon->setCouponCodes($codes);
	    			$couponList[$id] = $coupon;
	    		}
				SOYShop_DataSets::put("discount.coupon.list", $couponList);
	    	}
		}
    }

    function execute(){

		if(isset($_GET["action"])){
			$this->action = $_GET["action"];
		}

		WebPage::__construct();


		if(isset($_GET["download_list"])){
			$this->downloadList();
			exit;
		}
		if(isset($_POST["download_code"])){
			$this->downloadCode();
			exit;
		}



    	$this->showLink();

    	$this->createAdd("discount_coupon_config_form","HTMLForm", array(
    		"method" => (isset($_GET["action"]) AND $_GET["action"] == "list") ? "get" : "post"
    	));

    	$this->showConfig();
    	$this->showCouponList();
    	$this->showEditForm();




		$this->createAdd("issued", "HTMLModel", array(
			"visible" => isset($_GET["issued"])
		));
		$this->createAdd("updated", "HTMLModel", array(
			"visible" => isset($_GET["updated"])
		));
		$this->createAdd("error", "HTMLModel", array(
			"visible" => isset($_GET["error"])
		));
		$this->createAdd("issue_error", "HTMLModel", array(
			"visible" => isset($_GET["issue_error"])
		));

    }

    function showLink(){
		$this->createAdd("link_config","HTMLLink", array(
			"link" => ($this->action == "config") ? "" : SOY2PageController::createLink("Config.Detail?plugin=discount_coupon")
		));
		$this->createAdd("link_issue","HTMLLink", array(
			"link" => ($this->action == "issue" OR $this->action == "edit") ? "" : SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=issue")
		));
		$this->createAdd("link_list","HTMLLink", array(
			"link" => ($this->action == "list") ? "" : SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=list")
		));

    }

    function showConfig(){
		$obj = SOY2HTMLFactory::createInstance("SOYBodyComponentBase", array(
    		"visible" => (!isset($_GET["action"]) OR $_GET["action"] == "config")
    	));

    	$config = SOYShopCouponUtil::getConfig();

    	$obj->createAdd("config_accept_number","HTMLInput", array(
    		"name"  => "config[acceptNumber]",
			"value" => $config->getAcceptNumber()
    	));
    	$obj->createAdd("config_enable_amount_min","HTMLInput", array(
    		"name"  => "config[enableAmountMin]",
    		"value" => $config->getEnableAmountMin()
    	));
    	$obj->createAdd("config_enable_amount_max","HTMLInput", array(
    		"name"  => "config[enableAmountMax]",
    		"value" => $config->getEnableAmountMax()
    	));

		$this->add("config",$obj);
    }

    function showCouponList(){

		$this->createAdd("list","HTMLModel", array(
    		"visible" => (isset($_GET["action"]) AND ( $_GET["action"] == "list" OR  $_GET["action"] == "detail"))
		));

		$this->showSearchForm();

		if(isset($_GET["id"])){
			$coupon = SOYShopCouponUtil::getCoupon($_GET["id"]);
	    	$list = array($coupon);
		}elseif(isset($_GET["search"])){
	    	$coupon = new SOYShopCoupon();
	    	$list = SOYShopCouponUtil::searchCouponList($_GET["search"]);
		}else{
	    	$coupon = new SOYShopCoupon();
	    	$list = SOYShopCouponUtil::searchCouponList(array("expire_start"=>date("Y-m-d")));
		}

    	$this->createAdd("coupon_list","SOYShopDiscountCouponList", array(
    		"list" => array_reverse($list)
    	));

    	$this->showCouponCodes($coupon);

    }

    function downloadList(){
		if(isset($_GET["search"])){
	    	$list = SOYShopCouponUtil::searchCouponList($_GET["search"]);
		}else{
	    	$list = SOYShopCouponUtil::searchCouponList(array("expire_start"=>date("Y-m-d")));
		}

		SOYShopCouponUtil::exportList($list);

    }

    function downloadCode(){

		if(isset($_GET["id"])){
			$coupon = SOYShopCouponUtil::getCoupon($_GET["id"]);
			SOYShopCouponUtil::exportCode($coupon->getCouponCodes());
		}
    }

    function showSearchForm(){
    	$this->createAdd("search_form","HTMLModel", array(
    		"visible" => !isset($_GET["id"])
    	));

    	$this->createAdd("search_title","HTMLInput", array(
    		"name" => "search[title]",
    		"value" => @$_GET["search"]["title"]
    	));
    	$this->createAdd("search_memo","HTMLInput", array(
    		"name" => "search[memo]",
    		"value" => @$_GET["search"]["memo"]
    	));
    	$this->createAdd("search_expire_start","HTMLInput", array(
    		"name" => "search[expire_start]",
    		"value" => ( isset($_GET["search"]) && isset($_GET["search"]["expire_start"]) ) ? $_GET["search"]["expire_start"] : date("Y-m-d")
    	));
    	$this->createAdd("search_expire_end","HTMLInput", array(
    		"name" => "search[expire_end]",
    		"value" => @$_GET["search"]["expire_end"]
    	));
    }

    function showCouponCodes($coupon){

		$this->createAdd("coupon_code","HTMLModel", array(
    		"visible" => (isset($_GET["action"]) AND $_GET["action"] == "detail")
		));

		$this->createAdd("coupon_id","HTMLInput", array(
			"name" => "id",
			"value" => $coupon->getId(),
		));

    	$this->createAdd("coupon_code_list","SOYShopDiscountCouponCodeList", array(
    		"list" => $coupon->getCouponCodes()
    	));

    }

    function showEditForm(){
		$obj = SOY2HTMLFactory::createInstance("SOYBodyComponentBase", array(
    		"visible" => (isset($_GET["action"]) AND ($_GET["action"] == "edit" OR $_GET["action"] == "issue"))
    	));

    	if(isset($_GET["id"])){
    		$coupon = SOYShopCouponUtil::getCoupon($_GET["id"]);
    	}else{
    		$coupon = new SOYShopCoupon();
    	}

    	$obj->createAdd("cancel_edit","HTMLLink", array(
    		"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=detail&id=" . $coupon->getId()),
    		"visible" => strlen($coupon->getId()) >0
    	));

    	$obj->createAdd("edit_title","HTMLLabel", array(
    		"text" => strlen($coupon->getId()) ? "クーポン情報の編集" : "クーポンの発行"
    	));

    	$obj->createAdd("input_id","HTMLInput", array(
    		"type"  => "hidden",
			"name"  => "edit[id]",
    		"value" => $coupon->getId(),
    	));
    	$obj->createAdd("input_number","HTMLInput", array(
    		"name"  => "edit[number]",
    		"value" => $coupon->getNumber(),
    		"readOnly" => strlen($coupon->getId()) >0
    	));
    	$obj->createAdd("input_value","HTMLInput", array(
    		"name"  => "edit[value]",
    		"value" => $coupon->getValue(),
    	));
    	$obj->createAdd("input_title","HTMLInput", array(
    		"name"  => "edit[title]",
    		"value" => $coupon->getTitle(),
    	));
    	$obj->createAdd("input_memo","HTMLInput", array(
    		"name"  => "edit[memo]",
    		"value" => $coupon->getMemo(),
    	));
    	$obj->createAdd("input_expiration_date","HTMLInput", array(
    		"name"  => "edit[expirationDate]",
    		"value" => $coupon->getExpirationDate(),
    	));

		$this->add("edit",$obj);

    }

    function checkEdit($postedArray){
    	//全角→半角
    	foreach($postedArray as $key => $value){
    		if($key != "title" OR $key != "memo"){
    			$postedArray[$key] = mb_convert_kana($value, "a");
    		}
    	}

    	if(!isset($postedArray["number"]) OR !is_numeric($postedArray["number"])){
    		return false;
    	}
    	if(!isset($postedArray["value"]) OR !is_numeric($postedArray["value"])){
    		return false;
    	}
    	if(!isset($postedArray["title"])){
    		return false;
    	}
    	if(!isset($postedArray["expirationDate"])){
    		return false;
    	}

    	return true;
    }


	function setConfigObj($obj) {
		$this->config = $obj;
	}

}

class SOYShopDiscountCouponList extends HTMLList{

	public function populateItem($entity){
    	$this->createAdd("id","HTMLLink", array(
    		"text" => $entity->getId(),
    		"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=detail&id=" . $entity->getId())
    	));
    	$this->createAdd("number","HTMLLabel", array(
    		"text" => $entity->getNumber()
    	));
    	$this->createAdd("value","HTMLLabel", array(
    		"text" => $entity->getValue()
    	));
    	$this->createAdd("title","HTMLLabel", array(
    		"text" => $entity->getTitle()
    	));
    	$this->createAdd("memo","HTMLLabel", array(
    		"text" => $entity->getMemo()
    	));
    	$this->createAdd("expiration_date","HTMLLabel", array(
    		"text" => $entity->getExpirationDate()
    	));
    	$this->createAdd("real_expiration_date","HTMLLabel", array(
    		"text" => date("Y-m-d H:i:s", $entity->getExpirationDatetime())
    	));
    	$this->createAdd("issued_datetime","HTMLLabel", array(
    		"text" => $entity->getIssuedDate()
    	));

    	$this->createAdd("detail_link","HTMLLink", array(
    		"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=detail&id=" . $entity->getId())
    	));
    	$this->createAdd("edit_link","HTMLLink", array(
    		"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&action=edit&id=" . $entity->getId())
    	));
	}
}

class SOYShopDiscountCouponCodeList extends HTMLList{
	public function populateItem($entity){

    	$this->createAdd("code","HTMLLabel", array(
    		"text" => $entity->getCode()
    	));
    	$this->createAdd("status","HTMLSelect", array(
    		"name"  => "status[" . $entity->getCouponId() . "][" . $entity->getCode() . "]",
    		"options"  => SOYShopCouponCode::$STATUS_ARRAY,
    		"selected" => $entity->getStatus()
    	));
    	$this->createAdd("status1","HTMLCheckBox", array(
    		"name"  => "status[" . $entity->getCouponId() . "][" . $entity->getCode() . "]",
    		"label"  => SOYShopCouponCode::$STATUS_ARRAY[1],
    		"value"  => 1,
    		"selected" => $entity->getStatus() == 1
    	));
    	$this->createAdd("status2","HTMLCheckBox", array(
    		"name"  => "status[" . $entity->getCouponId() . "][" . $entity->getCode() . "]",
    		"label"  => SOYShopCouponCode::$STATUS_ARRAY[2],
    		"value"  => 2,
    		"selected" => $entity->getStatus() == 2
    	));
    	$this->createAdd("status3","HTMLCheckBox", array(
    		"name"  => "status[" . $entity->getCouponId() . "][" . $entity->getCode() . "]",
    		"label"  => SOYShopCouponCode::$STATUS_ARRAY[3],
    		"value"  => 3,
    		"selected" => $entity->getStatus() == 3
    	));
    	$this->createAdd("status4","HTMLCheckBox", array(
    		"name"  => "status[" . $entity->getCouponId() . "][" . $entity->getCode() . "]",
    		"label"  => SOYShopCouponCode::$STATUS_ARRAY[4],
    		"value"  => 4,
    		"selected" => $entity->getStatus() == 4
    	));
    	$this->createAdd("order_link","HTMLLink", array(
    		"text" => $entity->getOrderId(),
    		"link" => SOY2PageController::createLink("Order.Detail." . $entity->getOrderId())
    	));
    	$this->createAdd("user_link","HTMLLink", array(
    		"text" => $entity->getUserId(),
    		"link" => SOY2PageController::createLink("User.Detail." . $entity->getUserId())
    	));
	}
}

