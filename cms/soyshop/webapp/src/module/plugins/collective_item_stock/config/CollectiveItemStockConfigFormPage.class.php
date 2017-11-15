<?php

class CollectiveItemStockConfigFormPage extends WebPage{

	private $configObj;
	private $itemDao;

	private $categories = array();

	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$this->categories = self::getCategories();
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["Stock"]) && count($_POST["Stock"])){

				$this->itemDao->begin();
				foreach($_POST["Stock"] as $itemId => $stock){
					//念の為
					if(!is_numeric($stock)) continue;

					//在庫に変更があるか調べる
					try{
						$item = $this->itemDao->getById($itemId);
					}catch(Exception $e){
						continue;
					}

					//変更がない場合は次へ
					if((int)$item->getStock() === (int)$stock) continue;

					$item->setStock($stock);

					try{
						$this->itemDao->update($item);
					}catch(Exception $e){
						//
					}
				}
				$this->itemDao->commit();

				$this->configObj->redirect("updated");
			}
		}

	}

	function execute(){
		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			$this->configObj->redirect();
		}

		MessageManager::addMessagePath("admin");

		parent::__construct();

		self::buildSearchForm();

		$this->addForm("form");

		SOY2::import("domain.config.SOYShop_ShopConfig");
		SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.ItemListComponent");
		$this->createAdd("item_list", "ItemListComponent", array(
			"list" => self::getItems(),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO"),
			"categoriesDAO" => SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO"),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"categories" => $this->categories,
			"config" => SOYShop_ShopConfig::load(),
		));
	}

	private function buildSearchForm(){

		//POSTのリセット
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

		$opts = array();
		foreach($this->categories as $cat){
			$opts[$cat->getId()] = $cat->getName();
		}
		$this->addSelect("search_item_category", array(
			"name" => "search_condition[item_category]",
			"options" => $opts,
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

		//表示件数
		$this->addInput("search_item_number", array(
			"name" => "search_number",
			"value" => (isset($_POST["search_number"])) ? (int)$_POST["search_number"] : 15,
			"style" => "width: 80px;"
		));

		$this->addCheckBox("search_item_type_parent", array(
			"name" => "search_condition[item_type][parent]",
			"value" => 1,
			"selected" => (!isset($cnd["item_type"]["parent"]) || $cnd["item_type"]["parent"] == 1),
			"label" => "通常商品(親商品)"
		));
		$this->addInput("search_item_type_parent_hidden", array(
			"name" => "search_condition[item_type][parent]",
			"value" => 0,
		));

		$this->addCheckBox("search_item_type_child", array(
			"name" => "search_condition[item_type][child]",
			"value" => 1,
			"selected" => (isset($cnd["item_type"]["child"]) && $cnd["item_type"]["child"] == 1),
			"label" => "子商品"
		));
	}

	private function getItems(){
		$num = (isset($_POST["search_number"]) && is_numeric($_POST["search_number"])) ? (int)$_POST["search_number"] : 15;

		$searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.SearchLogic");
		$searchLogic->setLimit($num);	//仮
		$searchLogic->setCondition(self::getParameter("search_condition"));
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
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Collective.Stock:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Collective.Stock:" . $key, $value);
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
