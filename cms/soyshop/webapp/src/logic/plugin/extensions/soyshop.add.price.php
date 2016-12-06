<?php
class SOYShopAddPriceBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Item $item){

	}
	
	function doPost(SOYShop_Item $item){
		
	}
}

class SOYShopAddPriceDeletageAction implements SOY2PluginDelegateAction{

	private $item;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost($this->getItem());
		}else{
			echo $action->getForm($this->getItem());
		}
	}

	function getItem(){
		return $this->item;
	}
	
	function setItem($item){
		$this->item = $item;
	}
}
SOYShopPlugin::registerExtension("soyshop.add.mailaddress","SOYShopAddMailAddressDeletageAction");
?>
