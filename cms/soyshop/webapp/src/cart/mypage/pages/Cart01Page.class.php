<?php
/**
 * @class Cart01Page
 * @date 2009-10-17
 * @author SOY2HTMLFactory
 */
class Cart01Page extends MainCartPageBase{

	function doPost(){

		$cart = CartLogic::getCart();
		$userArray = @$_POST["User"];

		//reset info
		$cart->clearErrorMessage();
		$cart->clearAttribute("logined");

		if(isset($_POST["login"]) || isset($_POST["login_x"])){

			try{
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $userDAO->getByMailAddress($userArray["mailAddress"]);

				if($user->checkPassword($userArray["password"])){
					$cart->setCustomerInformation($user);
					$cart->setAttribute("logined", true);
					$cart->setAttribute("logined_userid", $user->getId());
				}else{
					throw new Exception();
				}
			}catch(Exception $e){
				$user = new SOYShop_User();
				$user->setMailAddress($userArray["mailAddress"]);
				$cart->setCustomerInformation($user);

				$cart->addErrorMessage("login_error","ログイン出来ません");
				$cart->save();
				soyshop_redirect_cart();
			}

			$cart->setCustomerInformation($user);

			//loginと同じ処理
			try{
				//checkStock
				$cart->checkOrderable();
				$cart->setAttribute("page", "Cart02");

			}catch(SOYShop_EmptyStockException $e){
				$cart->addErrorMessage("stock", "在庫切れの商品があります。");
				$cart->setAttribute("page", "Cart01");

			}catch(Exception $e){

			}


			$cart->save();

			soyshop_redirect_cart();
			exit;
		}


		if(isset($_POST["next"]) || isset($_POST["next_x"])){

			try{
				//checkStock
				$cart->checkOrderable();
				$cart->setAttribute("page", "Cart02");

			}catch(SOYShop_EmptyStockException $e){
				$cart->addErrorMessage("stock", "在庫切れの商品があります。");
				$cart->setAttribute("page", "Cart01");

			}catch(Exception $e){

			}

			$cart->save();

			soyshop_redirect_cart();
		}

		if(isset($_POST["modify"]) || isset($_POST["modify_x"])){
			$count = $_POST["ItemCount"];
			$cart = CartLogic::getCart();

			foreach($count as $id => $value){
				$cart->removeItem($id);
				if($value > 0){
					$cart->addItem($id,$value);
				}
			}

			$cart->removeErrorMessage("stock");

			$cart->save();

		}

		soyshop_redirect_cart();

	}

	function Cart01Page(){
		WebPage::__construct();

		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$shopConfig = SOYShop_ShopConfig::load();

		if(count($items) > 0){
			DisplayPlugin::hide("is_empty");
		}else{
			DisplayPlugin::hide("go_next");
		}

		$this->createAdd("order_form","HTMLForm", array(
			"action" => soyshop_get_cart_url(false)
		));
		$this->createAdd("login_form","HTMLForm", array(
			"action" => soyshop_get_cart_url(false)
		));

		$this->createAdd("item_list", "_common.ItemList", array(
			"list" => $items,
			"ignoreStock" => $shopConfig->getIgnoreStock()
		));


		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$this->createAdd("return_link","HTMLLink", array(
			"link" => SOYSHOP_SITE_URL
		));

		DisplayPlugin::toggle("has_stock_error",(strlen($cart->getErrorMessage("stock")) > 0));
		$this->createAdd("stock_error","HTMLLabel", array(
			"text" => $cart->getErrorMessage("stock")
		));

		DisplayPlugin::toggle("has_login_error",(strlen($cart->getErrorMessage("login_error")) > 0));
		$this->createAdd("login_error","HTMLLabel", array(
			"text" => $cart->getErrorMessage("login_error")
		));

		$user = $cart->getCustomerInformation();

		$this->createAdd("login_user_mail_address","HTMLInput", array(
			"name" => "User[mailAddress]",
			"value" => ($user) ? $user->getMailAddress() : ""
		));

	}
}
?>