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
		
		self::buildSearchForm();
		
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
		
		$this->addLabel("csf_form", array(
			"html" => self::buildForm($field)
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
	
	private function buildSearchForm(){
		
		$cnd = self::getParameter("search_condition");
		
		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));
		
		$this->addForm("search_form");
		
		$this->addLabel("csf_label", array(
			"text" => $this->config[$this->fieldId]["label"]
		));
				
		$this->addCheckBox("nothing_check", array(
			"name" => "search_condition[nothing]",
			"value" => 1,
			"selected" => (isset($cnd["nothing"])),
			"label" => "値の設定なし"
		));
				
		$this->addLabel("csf_cnd_form", array(
			"html" => self::buildSearchConditionForm($this->config[$this->fieldId], $cnd)
		));
	}
	
	private function buildForm($field){
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
		$h = array();
		$h[] = FieldFormComponent::buildForm($this->fieldId, $field);
		return implode("\n", $h);
	}
	
	private function buildSearchConditionForm($field, $cnd){
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
		$h = array();
		$h[] = FieldFormComponent::buildSearchConditionForm($this->fieldId, $field, $cnd);
		return implode("\n", $h);
	}
	
	private function getItems(){
		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.admin.SearchLogic", array("fieldId" => $this->fieldId));
		$searchLogic->setLimit(50);
		return $searchLogic->get();
	}
	
	private function getCategories(){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Custom.Search:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Custom.Search:" . $key, $value);
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}