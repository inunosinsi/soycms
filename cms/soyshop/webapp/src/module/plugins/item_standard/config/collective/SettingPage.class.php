<?php

class SettingPage extends WebPage{

	private $configObj;
	private $itemDao;
	private $attrDao;

	private $categories = array();

	function __construct(){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$this->categories = soyshop_get_category_objects();
	}

	function doPost(){
		if(isset($_POST["items"]) && count($_POST["items"]) && isset($_POST["Standard"])){

			//子商品生成
			$logic = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic");
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

			foreach($_POST["items"] as $itemId){
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
					}
				}
			}

			//一旦ループをやめる

			$values = array();
			foreach($_POST["Standard"] as $confId => $value){
				if(strlen(trim($value))) $values[] = explode("\n", trim($value));
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

				foreach($_POST["items"] as $itemId){
					$parent = soyshop_get_item_object($itemId);

					//小商品のリセット
					$children = soyshop_get_item_children($parent->getId());
					if(!count($children)) return;

					//データベース高速化のために完全削除
					foreach($children as $child){
						try{
							$itemDao->delete($child->getId());
						}catch(Exception $e){

						}
					}

					//小商品の登録
					$doExe = false;
					foreach($list as $keys){

						$child = new SOYShop_Item();
						$child = $logic->setChildItemName($child, $parent, $keys);
						$child->setPrice((int)$parent->getPrice());
						$child->setStock((int)$parent->getStock());

						$child = $logic->setParentInfo($child, $parent);

						try{
							$itemDao->insert($child);
							$doExe = true;
						}catch(Exception $e){
							var_dump($e);
						}
					}

					//一度でも実行したら、親商品のタイプをGroupにする
					if($doExe && $parent->getType() !== SOYShop_Item::TYPE_GROUP){
						$parent->setType(SOYShop_Item::TYPE_GROUP);
						try{
							$itemDao->update($parent);
						}catch(Exception $e){
							var_dump($e);
						}
					}
				}

			}

			$this->configObj->redirect("collective&updated");
		}
	}

	function execute(){
		MessageManager::addMessagePath("admin");

		parent::__construct();

		self::buildSearchForm();

		$this->addForm("form");

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => self::getItems(),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));

		$this->addLabel("standard_form_area", array(
			"html" => SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.BuildFormLogic")->buildCollectiveFormArea()
		));
	}

	private function buildSearchForm(){

		//リセット
		if(isset($_POST["search_condition"])){
			foreach($_POST["search_condition"] as $key => $value){
				if(is_array($value)){
					//
				}else{
					if(!strlen($value)){
						unset($_POST["search_condition"][$key]);
					}
				}
			}
		}

		if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
			self::setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::getParameter("search_condition");
		}
		//リセットここまで


		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		foreach(array("item_name", "item_code") as $t){
			$this->addInput("search_" . $t, array(
				"name" => "search_condition[" . $t . "]",
				"value" => (isset($cnd[$t])) ? $cnd[$t] : ""
			));
		}

		$this->addSelect("search_item_category", array(
			"name" => "search_condition[item_category]",
			"options" => soyshop_get_category_list(),
			"selected" => (isset($cnd["item_category"])) ? $cnd["item_category"] : null
		));

		$this->addCheckBox("search_item_is_open", array(
			"name" => "search_condition[item_is_open][]",
			"value" => SOYShop_Item::IS_OPEN,
			"selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::IS_OPEN, $cnd["item_is_open"])),
			"label" => "公開"
		));

		$this->addCheckBox("search_item_no_open", array(
			"name" => "search_condition[item_is_open][]",
			"value" => SOYShop_Item::NO_OPEN,
			"selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::NO_OPEN, $cnd["item_is_open"])),
			"label" => "非公開"
		));

		//カスタムサーチフィールド
		SOY2::import("util.SOYShopPluginUtil");
		$isCsf = SOYShopPluginUtil::checkIsActive("custom_search_field");
		DisplayPlugin::toggle("custom_search_field", $isCsf);

		if($isCsf) self::buildCustomSearchForm($cnd);
	}

	private function buildCustomSearchForm($cnd){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");

		SOY2::import("module.plugins.item_standard.component.CustomSearchFieldFormListComponent");
		$this->createAdd("form_list", "CustomSearchFieldFormListComponent", array(
			"list" => CustomSearchFieldUtil::getConfig(),
			"conditions" => (isset($cnd["csf"]) && is_array($cnd["csf"])) ? $cnd["csf"] : array()
		));
	}

	private function getItems(){
		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.SearchLogic");
		$searchLogic->setLimit(50);	//仮
		$searchLogic->setCondition(self::getParameter("search_condition"));
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

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Standard:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Standard:" . $key, $value);
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
