<?php
/*
 */
class CommonRecommendItemField extends SOYShopItemCustomFieldBase{

	var $itemDAO;

	function doPost(SOYShop_Item $item){
		if(isset($_POST["recommend_item"])){
			$items = $this->getRecommendItems();
			if($_POST["recommend_item"] == 1){
				$items[$item->getId()] = $item->getId();
			}else{
				unset($items[$item->getId()]);
			}

			SOYShop_DataSets::put("item.recommend_items",$items);

		}
	}

	function getForm(SOYShop_Item $item){
		$items = $this->getRecommendItems();

		$checked = (isset($items[$item->getId()])) ? "checked" : "";

		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//do nothing
		//関連商品はsiteで提供
	}

	function getRecommendItems(){
		return SOYShop_DataSets::get("item.recommend_items", array());
	}

	function setRecommendItems($items){
		SOYShop_DataSets::put("item.recommend_items",$items);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_recommend_item","CommonRecommendItemField");
?>