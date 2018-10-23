<?php

class SOYShopItemOptionBase implements SOY2PluginAction{

	function clear($index, CartLogic $cart){

	}

	function compare($index, CartLogic $cart){

	}

	function doPost($index, CartLogic $cart){

	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, $index){

	}

	function order($index){

	}

	function display(SOYShop_ItemOrder $itemOrder){

	}

	function form(SOYShop_ItemOrder $itemOrder){

	}

	function change($itemOrders){

	}

	function history($newItemOrder, $oldItemOrder){

	}

	function add(){

	}

	function edit($key){

	}

	function build($itemOrderId, $key, $selected){

	}

	function buildOnAdmin($index, $fieldValue, $key, $selected){

	}

	function addition($index){

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
				$this->_htmls = $action->onOutput($this->htmlObj, $this->index);
				break;
			case "order":
				$this->_attributes = $action->order($this->index);
				break;
			case "addition":
				$this->_addition = $action->addition($this->index);
				break;
			case "display":
				if($this->item instanceof SOYShop_ItemOrder){
					$this->_htmls = $action->display($this->item);
				}
				break;
			case "form":	//隠しモード マイページで編集用のフォームを出力する
				if($this->item instanceof SOYShop_ItemOrder){
					$this->_htmls = $action->form($this->item);
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
				$this->_label = $action->edit($this->key);
				break;
			case "build":
				$this->_htmls = $action->build($this->itemOrderId, $this->key, $this->selected);
				break;
			case "admin":
				$this->_htmls = $action->buildOnAdmin($this->index, $this->fieldValue, $this->key, $this->selected);
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
