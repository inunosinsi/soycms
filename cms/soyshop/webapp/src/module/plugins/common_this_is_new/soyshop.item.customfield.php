<?php
/*
 */
class CommonThisIsNew extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
	}

	function getForm(SOYShop_Item $item){
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$visible = false;
		$now = time();

		if(isset($item)){
			$compare = $this->compareTime($item);
			if($this->compareTime($item)>$now){
				$visible = true;
			}
		}

		$htmlObj->createAdd("this_is_new","HTMLModel", array(
			"visible" => $visible,
			"soy2prefix" => "cms",
		));
	}

	function onDelete($id){
	}
	
	function compareTime($item){
		$config = $this->getConfig();
		
		$createDate = $item->getCreateDate();
		return $createDate + $config["date"] * 60*60*24;
	}

	function getConfig(){
    	return SOYShop_DataSets::get("common_this_is_new", array(
    		"date" => 7
    	));
    }
}

SOYShopPlugin::extension("soyshop.item.customfield","common_this_is_new","CommonThisIsNew");
?>
