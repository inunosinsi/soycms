<?php

class SOYShopItemCustomFieldBase implements SOY2PluginAction{

	/**
	 * @param array
	 */
	function onEntryListBeforeOutput(array $items){}

	/**
	 * @param SOYShop_Item
	 * @return string
	 */
	function getForm(SOYShop_Item $item){}

	/**
	 * doPost
	 * @param SOYShop_Item
	 */
	function doPost(SOYShop_Item $item){}

	/**
	 * onOutput
	 * @param SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){}

	/**
	 * addByAdmin
	 * @param unknown $htmlObj
	 * @param SOYShop_Item $item
	 */
	function outputFormForAdmin($htmlObj, SOYShop_Item $item, string $nameBase, int $itemIndex){}

	/**
	 * @onDelete
	 * @param int
	 */
	function onDelete(int $itemId){}

}
class SOYShopItemCustomFieldDeletageAction implements SOY2PluginDelegateAction{

	private $deleteItemId;
	private $item;
	private $items=array();
	private $htmlObj;
	private $pageObj;
	private $nameBase;
	private $itemIndex;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){	//管理画面
			if($this->item instanceof SOYShop_Item){
				if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
					$action->doPost($this->item);
				}else{
					if(is_string($this->nameBase) && is_numeric($this->itemIndex)){
						$action->outputFormForAdmin($this->htmlObj, $this->item, $this->nameBase, $this->itemIndex);
					}else{
						echo $action->getForm($this->item);
					}
				}
			}

			if(is_numeric($this->deleteItemId)){
				$action->onDelete($this->deleteItemId);
			}
		}else{	//公開側
			if($this->item instanceof SOYShop_Item){
				$action->onOutput($this->htmlObj, $this->item);
			}else if(is_array($this->items) && count($this->items)){
				$action->onEntryListBeforeOutput($this->items);
			}
		}
	}

	function getItem() {
		return $this->item;
	}
	function setItem($item) {
		$this->item = $item;
	}
	function getItems(){
		return $this->items;
	}
	function setItems($items){
		$this->items = $items;
	}

	function getHtmlObj() {
		return $this->htmlObj;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
	function getPageObj() {
		return $this->pageObj;
	}
	function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}
	function getDeleteItemId() {
		return $this->deleteItemId;
	}
	function setDeleteItemId($deleteItemId) {
		$this->deleteItemId = $deleteItemId;
	}
	function setNameBase($nameBase){
		$this->nameBase = $nameBase;
	}
	function setItemIndex($itemIndex){
		$this->itemIndex = $itemIndex;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.customfield", "SOYShopItemCustomFieldDeletageAction");
