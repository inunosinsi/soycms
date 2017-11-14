<?php
SOY2::import("module.plugins.common_item_option.logic.ItemOptionLogic");

include(dirname(__FILE__) . "/common.php");

class ItemPage extends WebPage{

	private $cart;

	function doPost(){
		if(soy2_check_token()){

			if(isset($_POST["Item"])){

				$items = $this->cart->getItems();

				$newItems = $_POST["Item"];
				foreach($items as $id => $itemOrder){
					if(isset($newItems[$id]) && is_array($newItems[$id])){
						if( isset($newItems[$id]["itemDelete"]) && $newItems[$id]["itemDelete"] ){
							$count = 0;
						}elseif(isset($newItems[$id]["itemCount"]) && is_numeric($newItems[$id]["itemCount"]) && $newItems[$id]["itemCount"] > 0){
							$count = $newItems[$id]["itemCount"];
						}else{
							continue;
						}

//						//商品オプションの配列はシリアライズしておく
//						$newAttributes = (isset($newItems[$id]["attributes"])) ? $newItems[$id]["attributes"] : array();
//						if(count($newAttributes) > 0){
//							$newItems[$id]["attributes"] = @soy2_serialize($newItems[$id]["attributes"]);
//						}

						$this->cart->updateItem($id, $count);
						$this->cart->save();
					}
				}
			}

			if(
				isset($_POST["AddItemByName"]) && is_array($_POST["AddItemByName"])
				 && isset($_POST["AddItemByName"]["name"]) && isset($_POST["AddItemByName"]["price"]) && isset($_POST["AddItemByName"]["count"])
				 && is_array($_POST["AddItemByName"]["name"]) && is_array($_POST["AddItemByName"]["price"]) && is_array($_POST["AddItemByName"]["count"])
			){
				foreach($_POST["AddItemByName"]["name"] as $key => $value){
					$name  = isset($_POST["AddItemByName"]["name"][$key]) && strlen(isset($_POST["AddItemByName"]["name"][$key]))
					       ? trim($_POST["AddItemByName"]["name"][$key]) : "" ;
					$price = isset($_POST["AddItemByName"]["price"][$key]) && strlen($_POST["AddItemByName"]["price"][$key])
					       ? trim($_POST["AddItemByName"]["price"][$key]) : "" ;
					$count = isset($_POST["AddItemByName"]["count"][$key]) && strlen($_POST["AddItemByName"]["count"][$key])
					       ? trim($_POST["AddItemByName"]["count"][$key]) : 1 ;
					if(strlen($name)>0 && strlen($price)>0 && $count > 0){
						$this->cart->addUnlistedItem($name, $count, $price);
						$this->cart->save();
					}
				}
			}

			if(
				isset($_POST["AddItemByCode"]) && is_array($_POST["AddItemByCode"])
				 && isset($_POST["AddItemByCode"]["code"]) && isset($_POST["AddItemByCode"]["count"])
				 && is_array($_POST["AddItemByCode"]["code"]) && is_array($_POST["AddItemByCode"]["count"])
			){
				$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				foreach($_POST["AddItemByCode"]["code"] as $key => $value){
					$code = isset($_POST["AddItemByCode"]["code"][$key]) && strlen($_POST["AddItemByCode"]["code"][$key])
					       ? trim($_POST["AddItemByCode"]["code"][$key]) : "" ;
					$count = isset($_POST["AddItemByCode"]["count"][$key]) && strlen($_POST["AddItemByCode"]["count"][$key])
					       ? trim($_POST["AddItemByCode"]["count"][$key]) : 1 ;
					if(strlen($code)>0 && $count > 0){
						try{
							$item = $dao->getByCode($code);
							$this->cart->addItem($item->getId(), $count);
							$this->cart->save();
						}catch(Exception $e){
							continue;
						}
					}
				}
			}

			//変更なし
			SOY2PageController::jump("Order.Register.Item");
		}
	}

/*
	function getOptionIndex(){
		$logic = new ItemOptionLogic();
		$list = $logic->getOptions();
		$empty = array();

		foreach($list as $index => $value){
			$empty[$index] = "";
		}

		return $empty;
	}
*/
	function __construct($args) {
		$this->cart = AdminCartLogic::getCart();
		parent::__construct();

		//パラメータから商品IDを取得
		$userId = (isset($args[0]))?$args[0]:null;
		if(isset($args[0]) && strlen($args[0])){
			$itemId = (int)$args[0];
			$this->cart->addItem($itemId, 1);
			$this->cart->save();
			SOY2PageController::jump("Order.Register");
		}

		$this->addForm("form");
		$this->buildForm();

	}

	function buildForm(){

		$this->createAdd("item_list", "ItemList", array(
			"list" => $this->cart->getItems(),
			"cart" => $this->cart
		));

		$this->createAdd("total_item_price","HTMLLabel", array(
			"text" => number_format($this->cart->getItemPrice())
		));
	}

    function convertDate($date){
    	return mktime(0,0,0,$date["month"],$date["day"],$date["year"]);
    }

    function convertDateText($time){
    	return date("Y",$time)."-".date("m",$time)."-".date("d",$time);
    }

}
