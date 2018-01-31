<?php

class OrderPage extends WebPage{

	private $id;

	function doPost(){
		if(soy2_check_token()){
			$itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($this->id);
			$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

			$cnt = 0;
			foreach($_POST["Item"] as $idx => $v){
				$itemOrder = $itemOrders[$idx];
				$itemOrder->setDisplayOrder($cnt++);
				try{
					$itemOrderDao->update($itemOrder);
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
	}

	function __construct($args){
		MessageManager::addMessagePath("admin");
		$this->id = (isset($args[0])) ? (int)$args[0] : "";
		parent::__construct();

		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		try{
			$order = $logic->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Order.Detail." . $this->id);
		}

		$this->addLabel("order_name_text", array(
			"text" => $order->getTrackingNumber()
		));

		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		$this->addLink("order_edit_link", array(
			"link" => SOY2PageController::createLink("Order.Edit." . $order->getId())
		));

		$this->addForm("form");

		$this->createAdd("item_list", "_common.Order.ItemOrderListComponent", array(
			"list" => $logic->getItemsByOrderId($this->id),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO")
		));
	}

	function getItem($itemId){
		static $itemDAO;
		static $items = array();

		if(!$itemDAO)$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!isset($items[$itemId])){
			try{
				$items[$itemId] = $itemDAO->getById($itemId);
			}catch(Exception $e){
				$items[$itemId] = new SOYShop_Item();
			}
		}

		return $items[$itemId];
	}
}
