<?php
class SOYShopAddPriceBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Item $item){

	}

	function doPost(SOYShop_Item $item){

	}

	// array( array("label", "price") )
	function confirm(SOYShop_Item $item){
		return array();
	}
}

class SOYShopAddPriceDeletageAction implements SOY2PluginDelegateAction{

	private $item;
	private $mode;
	private $_list;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){
			case "confirm":
				$prices = $action->confirm($this->getItem());
				if(is_array($prices) && count($prices)){
					$this->_list[$moduleId] = $prices;
				}
				break;
			default:
				if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
					$action->doPost($this->getItem());
				}else{
					echo $action->getForm($this->getItem());
				}
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getItem(){
		return $this->item;
	}
	function setItem($item){
		$this->item = $item;
	}

	function getPriceList(){
		return $this->_list;
	}
}
SOYShopPlugin::registerExtension("soyshop.add.price","SOYShopAddPriceDeletageAction");
