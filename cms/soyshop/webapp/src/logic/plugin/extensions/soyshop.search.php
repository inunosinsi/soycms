<?php
class SOYShopSearchModule implements SOY2PluginAction{
	
	private $page;	//SOYShop_SearchPage
	
	/**
	 * title text
	 */
	function getTitle(){}
	
	/**
	 * @return html
	 */
	function getForm(){}
	
	/**
	 * @return array<SOYShop_Item?
	 */
	function getItems($current, $limit){ return array(); }
	
	/**
	 * @return number
	 */
	function getTotal(){ return 0; }
	
	function execute($page){ }

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
}
class SOYShopSearchModuleDelegateAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $page;
	private $_action = null;

	function run($extentionId, $moduleId, SOY2PluginAction $action){
		
		if(!$action instanceof SOYShopSearchModule)return;
		if(is_null($this->page))throw new Exception("SOYShop_SearchPage is null");
		
		$action->setPage($this->page);
		
		switch($this->mode){
			case "list":
				$this->_list[$moduleId] = array(
					"moduleId" => $moduleId,
					"name" => $action->getTitle(),
				);
				break;
			default:
				$action->execute($this->page);
				break;
		}
		
		$this->_action = $action;
	}


	function getList() {
		return $this->_list;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
	
	
	/**
	 * @return html
	 */
	function getForm(){
		return ($this->_action) ? $this->_action->getForm() : "";
	}
	
	/**
	 * @return array<SOYShop_Item?
	 */
	function getItems($current,$limit){ 
		return ($this->_action) ? $this->_action->getItems($current,$limit) : array();	
	}
	
	/**
	 * @return number
	 */
	function getTotal(){
		return ($this->_action) ? $this->_action->getTotal() : array();
	}
}
SOYShopPlugin::registerExtension("soyshop.search", "SOYShopSearchModuleDelegateAction");
?>
