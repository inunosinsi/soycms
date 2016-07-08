<?php

class SettingPage extends WebPage{
	
	private $configObj;
	private $itemDao;
	private $attrDao;
	
	function SettingPage(){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
	
	function doPost(){
		if(isset($_POST["items"]) && count($_POST["items"]) && isset($_POST["Standard"])){
			
			//子商品生成
			$logic = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic");
			
			foreach($_POST["items"] as $itemId){
				$values = array();
				foreach($_POST["Standard"] as $confId => $value){
					if(strlen($value)){
						$obj = self::get($itemId, $confId);
						$obj->setValue(trim($value));
						
						//新規
						if(is_null($obj->getItemId())){
							$obj->setItemId($itemId);
							$obj->setFieldId($this->configObj->getModuleId() . "_plugin_" . $confId);
							try{
								$this->attrDao->insert($obj);
							}catch(Exception $e){
								continue;
							}
							
						//更新
						}else{
							try{
								$this->attrDao->update($obj);
							}catch(Exception $e){
								continue;
							}
						}
						
						$values[] = explode("\n", $value);
					}
				}
				
				if(count($values)){
					$result;
					$keyCnt = count($values);
					for($i = 0; $i < count($values); $i++){
						if(isset($values[$i + 1])){
							foreach ($values[$i] as $val1) {
								foreach ($values[$i + 1] as $val2) {
									if(!is_array($val1)) $val1 = trim($val1);
									$result[] = array_merge((array)$val1, (array)trim($val2));
								}
							}
							if(isset($values[$i + 2])){
								$values[$i + 1] = $result;
							}
						}
					}
					
					//整形
					$list = array();
					foreach($result as $res){
						if(count($res) === $keyCnt){
							$list[] = $res;
						}
					}
					
					/**
					 * @ToDo ここから一括登録　いろいろ練らなければならない
					 */
					foreach($list as $keys){
						
					}
				}
			}
			
			$this->configObj->redirect("collective&updated");
		}
	}
	
	function execute(){
		MessageManager::addMessagePath("admin");
		
		WebPage::WebPage();
		
		//self::buildSearchForm();
		
		$this->addForm("form");
				
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => self::getItems(),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"categoriesDAO" => SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $this->categories,
			"config" => SOYShop_ShopConfig::load(),
			"appLimit" => true
		));
		
		$this->addLabel("standard_form_area", array(
			"html" => SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.BuildFormLogic")->buildCollectiveFormArea()
		));
	}
	
	private function getItems(){
		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.SearchLogic");
		//$searchLogic->setLimit(50);	//仮
		//$searchLogic->setCondition(self::getParameter("search_condition"));
		return $searchLogic->get();
	}
	
	private function get($itemId, $confId){
		try{
			return $this->attrDao->get($itemId, $this->configObj->getModuleId() . "_plugin_" . $confId);
		}catch(Exception $e){
			return new SOYShop_ItemAttribute();
		}
	}
	
	private function combine() {
		$args = func_get_args();

		$a = array_shift($args);
		$b = array_shift($args);

		$result = array();
		foreach ($a as $val1) {
			foreach ($b as $val2) {
				$result[] = array_merge((array)$val1, (array)$val2);
			}
		}

		if (count($args) > 0) {
			foreach ($args as $arg) {
				$result = self::combine($result, $arg);
			}
		}

		return $result;
	}
			
	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>