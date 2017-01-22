<?php
class TagExamplePage extends WebPage{
	
	private $config;
	private $itemId;
	
	function __construct(){}
	
	function doPost(){}
	
	function execute(){
		WebPage::__construct();
				
		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $this->itemId),
			"text" => self::getItemById($this->itemId)->getName() . "の詳細ページに戻る"
		));
		
		$this->addLabel("item_id", array(
			"text" => $this->itemId
		));
	}
	
	private function getItemById($itemId){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
	
	function setConfigObj($obj) {
		$this->config = $obj;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
?>