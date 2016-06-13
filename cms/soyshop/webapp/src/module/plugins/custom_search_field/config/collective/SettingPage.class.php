<?php

class SettingPage extends WebPage{
	
	private $configObj;
	private $fieldId;
	
	private $config;
	private $dbLogic;
	
	function SettingPage(){
		$this->fieldId = (isset($_GET["field_id"])) ? $_GET["field_id"] : null;
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->config = CustomSearchFieldUtil::getConfig();
		$this->dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			if(isset($_POST["set"])){
				
				if(count($_POST["items"])){
					foreach($_POST["items"] as $itemId){
						$values = (isset($_POST["custom_search"]) && count($_POST["custom_search"])) ? $_POST["custom_search"] : null;
						$this->dbLogic->save($itemId, $values);
					}
				}
				
				$this->configObj->redirect("collective&field_id=" . $this->fieldId . "&updated");
			}
		}
		
	}
	
	function execute(){
		
		MessageManager::addMessagePath("admin");
		
		WebPage::WebPage();
		
		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		
		$this->addForm("form");
		
		$field = $this->config[$this->fieldId];
		$this->addLabel("field_label", array(
			"text" => (isset($field["label"])) ? $field["label"] : ""
		));
		
		$this->addLabel("prefix", array(
			"text" => CustomSearchFieldUtil::PLUGIN_PREFIX
		));
		
		$this->addLabel("field_id", array(
			"text" => $this->fieldId
		));
		
		$this->addLabel("checkbox", array(
			"html" => self::buildCheckBox($field)
		));
		
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => self::getItems(),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"categoriesDAO" => SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => self::getCategories(),
			"config" => SOYShop_ShopConfig::load(),
			"appLimit" => true
		));
	}
	
	private function buildCheckBox($field){
		static $html;
		if(is_null($html)){
			SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
			$html = array();
			$html[] = FieldFormComponent::buildForm($this->fieldId, $field);
		}

		return implode("\n", $html);
	}
	
	private function getItems(){
		//何も検索していない時はとりあえず50件表示
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$dao->setLimit(50);
		try{
			return $dao->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}