<?php
/**
 * @class Cart03Page
 * @date 2010-10-17
 * @author SOY2HTMLFactory
 */
class Cart03Page extends MainCartPageBase{

	function doPost(){

		if(isset($_POST["next"]) || isset($_POST["next_x"])){
			
			$mypage = MyPageLogic::getMyPage();
			$array = $mypage->getAttributes();
			$userId = $array["userId"];

			$cart = CartLogic::getCart();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			try{
				$user = $userDAO->getById($userId);
			}catch(Exception $e){
				$user = new SOYShop_User();
			}
			
			$list = $user->getAddressListArray();
			
			$address_key = $_POST["address"];
			$cart->setAttribute("address_key",$address_key);

			$cart->setCustomerInformation($user);

			//備考
			if(isset($_POST["Attributes"]) && isset($_POST["Attributes"]["memo"])){
				$cart->setOrderAttribute("memo","備考", $_POST["Attributes"]["memo"]);
			}else{

			}

			$cart->save();
			$cart->setAttribute("page", "Cart04");
			
			soyshop_redirect_cart();
		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart01");

			soyshop_redirect_cart();
		}
		
	}

	function Cart03Page(){
		
		
		//ログインチェック
		$cart = CartLogic::getCart();
		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()==false){
			$cart->checkOrderable();
			$cart->setAttribute("page", "Cart02");
			$cart->save();
			soyshop_redirect_cart();
		}
		
		parent::__construct();

		$this->createAdd("order_form","HTMLForm", array(
			"action" => soyshop_get_cart_url(false)
		));

		$items = $cart->getItems();

		$this->createAdd("item_list", "_common.ItemList", array(
			"list" => $items
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));
		
		$array = $mypage->getAttributes();
		$userId = $array["userId"];
		
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$user = $dao->getById($userId);
		
		$customer = $cart->getCustomerInformation();

		//送付先フォーム
		$this->buildSendForm($user,$cart,$customer);
	}


	function buildSendForm($user,$cart,$customer){

    	$memo = $cart->getOrderAttribute("memo");
    	if(is_null($memo))$memo = array("name"=>"備考","value"=>"");
    	$this->createAdd("order_memo","HTMLTextArea", array(
    		"name" => "Attributes[memo]",
    		"value" => $memo["value"]
    	));
    	
    	/* 送付先リスト */
    	$checked =  $cart->getAttribute("address_key");
    	
    	
    	//ユーザ登録した住所
    	$this->createAdd("user_name","HTMLLabel", array(
    		"text" => $user->getName()
    	));
    	$this->createAdd("user_reading","HTMLLabel", array(
    		"text" => $user->getReading()
    	));
    	$this->createAdd("user_zipcode","HTMLLabel", array(
    		"text" => $user->getZipcode()
    	));
    	$this->createAdd("user_area","HTMLLabel", array(
    		"text" => SOYShop_Area::getAreaText($user->getArea())
    	));
    	$this->createAdd("user_address1","HTMLLabel", array(
    		"text" => $user->getAddress1()
    	));
    	$this->createAdd("user_address2","HTMLLabel", array(
    		"text" => $user->getAddress2()
    	));
    	$this->createAdd("user_tel","HTMLLabel", array(
    		"text" => $user->getTelephoneNumber()
    	));
    	
    	
    	
    	
    	//お届け先選択
    	$this->createAdd("address_default","HTMLCheckBox", array(
    		"name" => "address",
    		"value" => -1,
    		"id" => "address_default",
    		"selected" => (is_null($checked) || $checked < 0)
    	));
    	
    	$this->createAdd("address_list","AddressList", array(
    		"list" => $user->getAddressListArray(),
    		"checked" => $checked
    	));
    	
    	
	}

}

class AddressList extends HTMLList{
	
	private $checked;
	
	function populateItem($entity,$key,$index){
		
		$checked = $this->checked;
		if(is_null($checked))$checked = -1;
		
		//radio
		$this->createAdd("address","HTMLCheckBox", array(
			"id" => "address_" . $key,
			"name" => "address",
			"value" => $key,
			"selected" => ($checked == $key)
		));

		$this->createAdd("send_office","HTMLLabel", array(
			"text" => $entity["office"]
		));
		
		$this->createAdd("is_send_office","HTMLModel", array(
			"visible" => strlen($entity["office"]) > 0
		));
		
		$this->createAdd("send_name","HTMLLabel", array(
			"text" => $entity["name"]
		));
		$this->createAdd("send_zipcode","HTMLLabel", array(
			"text" => $entity["zipCode"]
		));
		$this->createAdd("send_area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($entity["area"])
		));
		$this->createAdd("send_address1","HTMLLabel", array(
			"text" => $entity["address1"]
		));
		$this->createAdd("send_address2","HTMLLabel", array(
			"text" => $entity["address2"]
		));
		$this->createAdd("send_tel","HTMLLabel", array(
			"text" => $entity["telephoneNumber"]
		));

		//text
//		$text = "";
//		if($index > 0){
//			foreach($entity as $key => $val){
//				
//				if($key == "area")$val = SOYShop_Area::getAreaText($val);
//				$text .= $val;
//				$text .= "<br />";
//			}
//			
//		}
//		$this->createAdd("address_text","HTMLLabel", array(
//			"html" => $text
//		));		
	}

	function getChecked() {
		return $this->checked;
	}
	function setChecked($checked) {
		$this->checked = $checked;
	}
}

?>