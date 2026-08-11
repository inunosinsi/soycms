<?php
class SOYShopPriceOptionBase implements SOY2PluginAction{
	
	function doPost(SOYShop_Item $item){

	}
	
	function getTitle(SOYShop_Item $item){
		
	}
	
	function getForm(SOYShop_Item $item){
		
	}
}

class SOYShopPriceOptionDeletageAction implements SOY2PluginDelegateAction{

	private $_contents;
	private $item;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost($this->item);
		}else{
			$array["title"] = $action->getTitle($this->item);
			$array["form"] = $action->getForm($this->item);
			$this->_contents[$moduleId] = $array;
		}
	}
	
	function getContents(){
		return $this->_contents;
	}
	
	
	function setItem(SOYShop_Item $item){
		$this->item = $item;
	}
}
SOYShopPlugin::registerExtension("soyshop.price.option", "SOYShopPriceOptionDeletageAction");
?>