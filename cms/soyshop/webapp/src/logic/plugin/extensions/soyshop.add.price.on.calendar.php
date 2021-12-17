<?php
class SOYShopAddPriceOnCalendarBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(){}

	function doPost(int $scheduleId){}

	/**
	 * @return array("label" => string, "price" => integer)
	 */
	function list(int $scheduleId){
		return array();
	}

	/**
	 * @return array("key" => string, "label" => string)
	 */
	function getCsvItems(){
		return array();
	}
}

class SOYShopAddPriceOnCalendarDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $scheduleId;
	private $_list;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "list":
				$list = $action->list($this->scheduleId);
				if(isset($list) && count($list)){
					$this->_list[$moduleId] = $list;
				}
				break;
			case "csv":	//CSV関連
				$this->_list[$moduleId] = $action->getCsvItems();
				break;
			default:
				if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
					$action->doPost($this->scheduleId);
				}else{
					echo $action->getForm();
				}
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setScheduleId($scheduleId){
		$this->scheduleId = $scheduleId;
	}
	function getList(){
		return $this->_list;
	}
}
SOYShopPlugin::registerExtension("soyshop.add.price.on.calendar", "SOYShopAddPriceOnCalendarDeletageAction");
