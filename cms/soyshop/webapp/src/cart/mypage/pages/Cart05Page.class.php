<?php
/**
 * @class Cart05Page
 * @date 2009-10-17
 * @author SOY2HTMLFactory
 */
class Cart05Page extends MainCartPageBase{

	function doPost(){

		if(isset($_POST["next"]) || isset($_POST["next_x"])){
			$cart = CartLogic::getCart();

			if(soy2_check_token()){

				try{
					//注文実行
					$cart->order();
					
					//割引モジュール
					{
						SOYShopPlugin::load("soyshop.discount");
						$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
							"mode" => "order",
							"cart" => $cart,
						));
					}

					//pluginで次の画面があるかどうかチェック
					$hasOption = $cart->getAttribute("has_option");

					if($hasOption){
						$cart->setAttribute("page", "Cart06");
					}else{
						$cart->setAttribute("page", "Complete");
					}

				}catch(SOYShop_EmptyStockException $e){

					$cart->addErrorMessage("stock", "在庫切れの商品があります。");
					$cart->setAttribute("page", "Cart01");
					$cart->save();

				}catch(SOYShop_OverStockException $e){

					$cart->addErrorMessage("stock", "在庫切れの商品があります。");
					$cart->setAttribute("page", "Cart01");
					$cart->save();

				}catch(Exception $e){

					if(DEBUG_MODE){
						$cart->addErrorMessage("order_error","注文の登録に失敗しました。<pre>" . var_export($e,true) . "</pre>");
					}else{
						$cart->addErrorMessage("order_error","注文の登録に失敗しました。");
					}


				}
			}

			soyshop_redirect_cart();
		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart04");

			soyshop_redirect_cart();
		}

	}

	function Cart05Page(){
		
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

		//商品リストの出力
		$items = $cart->getItems();

		$this->createAdd("item_list", "_common.ItemList", array(
			"list" => $items
		));

		$modules = $cart->getModules();
		$this->createAdd("module_list", "ModuleList", array(
			"list" => $modules
		));

		$this->createAdd("total_item_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getTotalPrice()
		));

		$this->buildForm($cart);

		//error
		$this->createAdd("order_error", "ErrorMessageLabel", array(
			"html" => $cart->getErrorMessage("order_error")
		));

		if(strlen($cart->getErrorMessage("order_error")) < 1)DisplayPlugin::hide("has_order_error");
		$cart->clearErrorMessage();
	}

	function buildForm($cart){

		$user = $cart->getCustomerInformation();

		$this->createAdd("mail_address","HTMLLabel", array(
    		"text" => $user->getMailAddress(),
    	));

    	$this->createAdd("password","HTMLLabel", array(
    		"text" => $user->getPassword(),
    	));

    	$this->createAdd("name","HTMLLabel", array(
    		"text" => $user->getName(),
    	));

    	$this->createAdd("furigana","HTMLLabel", array(
    		"text" => $user->getReading(),
    	));
		
		$gender = $user->getGender();
    	$this->createAdd("gender","HTMLLabel", array(
			"text" => ($gender === 0 || $gender === "0") ? "男性" :
			        ( ($gender === 1 || $gender === "1") ? "女性" : "" )
    	));

    	$this->createAdd("birthday","HTMLLabel", array(
    		"text" => $user->getBirthdayText()
    	));

    	$this->createAdd("post_number","HTMLLabel", array(
    		"text" => $user->getZipCode()
    	));

		$this->createAdd("area","HTMLLabel", array(
    		"text" => SOYShop_Area::getAreaText($user->getArea())
    	));

    	$this->createAdd("address1","HTMLLabel", array(
    		"text" => $user->getAddress1(),
    	));

    	$this->createAdd("address2","HTMLLabel", array(
    		"text" => $user->getAddress2(),
    	));

    	$this->createAdd("tel_number","HTMLLabel", array(
    		"text" => $user->getTelephoneNumber(),
    	));

    	$this->createAdd("fax_number","HTMLLabel", array(
    		"text" => $user->getFaxNumber(),
    	));

    	$this->createAdd("ketai_number","HTMLLabel", array(
    		"text" => $user->getCellphoneNumber(),
    	));

    	$this->createAdd("job","HTMLLabel", array(
    		"text" => $user->getJobName(),
    	));

    	$send = $cart->getAddress();

		$this->createAdd("send_office","HTMLLabel", array(
			"text" => $send["office"]
		));
		$this->createAdd("if_send_office","HTMLModel", array(
			"visible" => strlen($send["office"])
		));

		$this->createAdd("send_name","HTMLLabel", array(
			"text" => $send["name"]
		));

		$this->createAdd("send_reading","HTMLLabel", array(
			"text" => $send["reading"]
		));

		$this->createAdd("send_zip_code","HTMLLabel", array(
			"text" => $send["zipCode"]
		));

		$this->createAdd("send_area","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText($send["area"])
		));

		$this->createAdd("send_address1","HTMLLabel", array(
			"text" => $send["address1"]
		));

		$this->createAdd("send_address2","HTMLLabel", array(
			"text" => $send["address2"]
		));
		
		$this->createAdd("send_tel","HTMLLabel", array(
			"text" => $send["telephoneNumber"]
		));

		$this->createAdd("is_use_address","HTMLModel", array(
			"visible" => false == (
					empty($send["name"]) &&
					empty($send["reading"]) &&
					empty($send["zipCode"]) &&
					empty($send["area"]) &&
					empty($send["address1"]) &&
					empty($send["address2"]) &&
					empty($send["telephoneNumber"])
			)
		));

		/*
		 * メモ
		 */
		$memo = $cart->getOrderAttribute("memo");
		$this->createAdd("memo","HTMLLabel", array(
			"html" => nl2br(htmlspecialchars($memo["value"])),
		));

		/*
		 * 属性 他で表示しているものは削除
		 */
		$attr = $cart->getOrderAttributes();
		unset($attr["memo"]);

		$this->createAdd("order_attribute_list","OrderAttributeList", array(
			"list" => $attr
		));

	}
}


class OrderAttributeList extends HTMLList{

	protected function populateItem($entity){
		$this->createAdd("attribute_title","HTMLLabel", array(
			"text" => $entity["name"],
		));

		$this->createAdd("attribute_value","HTMLLabel", array(
			"text" => $entity["value"],
		));

	}

}

/**
 * @class ModuleList
 * @generated by SOY2HTML
 */
class ModuleList extends HTMLList{

	protected function populateItem($entity){

		$this->createAdd("module_name","HTMLLabel", array(
			"text" => $entity->getName(),
		));

		$this->createAdd("module_price", "NumberFormatLabel", array(
			"text" => $entity->getPrice(),
		));

		return $entity->isVisible();

	}
}
?>