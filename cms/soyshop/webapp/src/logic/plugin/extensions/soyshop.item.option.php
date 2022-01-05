<?php

class SOYShopItemOptionBase implements SOY2PluginAction{

	/**
	 * カートから商品を削除した時に実行する
	 * @param int index, CartLogic
	 */
	function clear(int $index, CartLogic $cart){}

	/**
	 * カートに商品を入れた直後に実行する
	 * @param array $postedOptions, CartLogic
	 * @return int index
	 */
	function compare(array $postedOptions, CartLogic $cart){
		return 0;
	}

	function doPost(int $index, CartLogic $cart){}

	/**
	 * 商品情報の下に表示される情報
	 * @param htmlObj, int index
	 * @return string html
	 */
	function onOutput($htmlObj, int $index){
		return "";
	}

	/**
	 * 注文確定時に実行する
	 * @param int index
	 */
	function order(int $index){}

	/**
	 * 注文確定後に商品情報の下に表示される
	 * @param SOYShop_ItemOrder
	 * @return string
	 */
	function display(SOYShop_ItemOrder $itemOrder){
		return "";
	}

	/**
	 * マイページで商品オプションを変更できるようにする
	 * @param SOYShop_ItemOrder
	 * @return string html
	 */
	function form(SOYShop_ItemOrder $itemOrder){
		return "";
	}

	/**
	 * マイページで商品オプションの値を変更する
	 * @param SOYShop_ItemOrdere
	 */
	function change(array $itemOrders){}

	/**
	 * マイページで変更履歴を残す
	 * @param SOYShop_ItemOrder $new, SOYShop_ItemOrder $old
	 * @return array(string...)
	 */
	function history(SOYShop_ItemOrder $newItemOrder, SOYShop_ItemOrder $oldItemOrder){
		return array();
	}

	/**
	 * 用途不明
	 */
	function add(){}

	/**
	 * 注文詳細で商品オプションを変更できるようにする
	 * @param string
	 * @return string
	 */
	function edit(string $key){
		return "";
	}

	/**
	 * @param int itemOrderId, string key, string selected
	 * @return string 
	 */
	function build(int $itemOrderId, string $key, string $selected){
		return "";
	}

	/**
	 * @param int index, string value, string key, string selected
	 * @return string
	 */
	function buildOnAdmin(int $index, string $fieldValue, string $key, string $selected){
		return "";
	}

	/**
	 * @param int index
	 * @return int 0 or 1
	 */
	function addition(int $index){
		return 0;
	}
}
class SOYShopItemOptionDeletageAction implements SOY2PluginDelegateAction{

	private $_id;
	private $_htmls;
	private $_attributes;
	private $_addition;
	private $_label;
	private $_changes = array();
	private $mode;
	private $cart;
	private $index;
	private $key;
	private $item;
	private $htmlObj;
	private $option;
	private $itemOrders;
	private $newItemOrder;
	private $oldItemOrder;
	private $itemOrderId;
	private $selected;
	private $fieldValue;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		switch($this->mode){
			case "clear":
				$action->clear($this->index, $this->cart);
				break;
			case "compare":
				$this->_id = $action->compare($this->option, $this->cart);
				break;
			case "post":
				$action->doPost($this->index, $this->cart);
				break;
			case "item":
				$this->_htmls[$moduleId] = $action->onOutput($this->htmlObj, $this->index);
				break;
			case "order":
				$attrs = $action->order($this->index);
				if(isset($attrs)){
					$this->_attributes = $attrs;
				}
				break;
			case "addition":
				$isAddition = $action->addition($this->index);
				if(isset($isAddition)){
					$this->_addition = $isAddition;
				}
				break;
			case "display":
				if($this->item instanceof SOYShop_ItemOrder){
					$this->_htmls[$moduleId] = $action->display($this->item);
				}
				break;
			case "form":	//隠しモード マイページで編集用のフォームを出力する
				if($this->item instanceof SOYShop_ItemOrder){
					$this->_htmls[$moduleId] = $action->form($this->item);
				}
				break;
			case "change":	//隠しモード マイページで編集用のフォームから値を変更する
				$action->change($this->itemOrders);
				break;
			case "history":	//隠しモード マイページで編集用のフォーム変更時変更履歴を記録する
				$this->_changes[$moduleId] = $action->history($this->newItemOrder, $this->oldItemOrder);
				break;
			case "add":
				$this->_attributes[$moduleId] = $action->add();
				break;
			case "edit":
				if(!is_string($this->key)) $this->key = "";
				$label = $action->edit($this->key);
				if(isset($label)){
					$this->_label = $label;
				}
				break;
			case "build":
				$this->_htmls[$moduleId] = $action->build($this->itemOrderId, $this->key, $this->selected);
				break;
			case "admin":
				$this->_htmls[$moduleId] = $action->buildOnAdmin($this->index, $this->fieldValue, $this->key, $this->selected);
				break;
			default:
				//何もしない
		}
	}
	function getCartOrderId(){
		return $this->_id;
	}
	function getHtmls(){
		return $this->_htmls;
	}
	function getAttributes(){
		return $this->_attributes;
	}
	function getAddition(){
		return $this->_addition;
	}
	function getLabel(){
		return $this->_label;
	}
	function getChanges(){
		return $this->_changes;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setCart($cart){
		$this->cart = $cart;
	}
	function setIndex($index){
		$this->index = $index;
	}
	function setKey($key){
		$this->key = $key;
	}
	function setItem($item){
		$this->item = $item;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
	function setOption($option) {
		$this->option = $option;
	}
	function setItemOrders($itemOrders){
		$this->itemOrders = $itemOrders;
	}
	function setNewItemOrder($newItemOrder){
		$this->newItemOrder = $newItemOrder;
	}
	function setOldItemOrder($oldItemOrder){
		$this->oldItemOrder = $oldItemOrder;
	}
	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}
	function setSelected($selected){
		$this->selected = $selected;
	}
	function setFieldValue($fieldValue){
		$this->fieldValue = $fieldValue;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.option","SOYShopItemOptionDeletageAction");
