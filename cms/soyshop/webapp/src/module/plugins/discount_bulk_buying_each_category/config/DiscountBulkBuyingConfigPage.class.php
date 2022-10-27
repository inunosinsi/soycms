<?php
class DiscountBulkBuyingConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.discount_bulk_buying_each_category.util.DiscountBulkBuyingUtil");
		SOY2::import("module.plugins.discount_bulk_buying_each_category.component.BulkBuyingCategoryConditionListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Discount"])){
				DiscountBulkBuyingUtil::saveConfig($_POST["Discount"]);
			}

			if(isset($_POST["Condition"])){
				DiscountBulkBuyingUtil::saveCondition($_POST["Condition"]);
			}

			$categoryCondition = array();

			if(isset($_POST["new"]) && strlen($_POST["new"]["category"])){
				$values = array();
				$values["category"] = trim($_POST["new"]["category"]);
				$values["price"] = soyshop_convert_number($_POST["new"]["price"], 0);
				$values["total"] = soyshop_convert_number($_POST["new"]["total"], 0);
				$values["amount"] = soyshop_convert_number($_POST["new"]["amount"], 0);
				$values["combination"] = $_POST["new"]["combination"];
				$values["type"] = $_POST["new"]["type"];
				$values["discount"] = $_POST["new"]["discount"];
				$values["apply"] = soyshop_convert_number($_POST["new"]["apply"], "");

				$categoryCondition[$values["category"]] = $values;
			}

			//更新
			if(isset($_POST["category_condition"]) && count($_POST["category_condition"])){
				if(isset($categoryCondition[""])) unset($categoryCondition[""]);
				foreach($_POST["category_condition"] as $categoryId => $v){
					if(isset($v["category"]) && strlen($v["category"])){
						$values = array();
						$values["category"] = trim($v["category"]);
						$values["price"] = soyshop_convert_number($v["price"], 0);
						$values["total"] = soyshop_convert_number($v["total"], 0);
						$values["amount"] = soyshop_convert_number($v["amount"], 0);
						$values["combination"] = $v["combination"];
						$values["type"] = $v["type"];
						$values["discount"] = $v["discount"];
						$values["apply"] = soyshop_convert_number($v["apply"], "");
						$categoryCondition[$values["category"]] = $values;
					//}else{
					//	unset($categoryCondition[$categoryId]);
					}

				}
			}

			//必ずsave
			DiscountBulkBuyingUtil::saveCategoryCondition($categoryCondition);

			//条件
			$cnd = (isset($_POST["CategoryCombination"])) ? (int)$_POST["CategoryCombination"] : DiscountBulkBuyingUtil::COMBINATION_ALL;
			DiscountBulkBuyingUtil::saveCategoryCombinationCondition($cnd);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("installed_discount_bulk_buying", SOYShopPluginUtil::checkIsActive("discount_bulk_buying"));

		//不要になった箇所
		DisplayPlugin::toggle("hoge", false);
		DisplayPlugin::toggle("huga", false);

		$this->addForm("form");
		self::buildForm();//基本設定
		self::buildConditionForm();//条件設定
	}

	/**
	 * 割引内容のフォーム
	 */
	private function buildForm(){
		$config = DiscountBulkBuyingUtil::getConfig();

		//割引名
		$this->addInput("discount_name", array(
			"name" => "Discount[name]",
			"value" => $config["name"]
		));

		//カートでの説明文
		$this->addTextarea("discount_description", array(
			"name" => "Discount[description]",
			"text" => $config["description"]
		));
	}

	private function buildConditionForm(){
		$list = soyshop_get_category_list();

		$this->createAdd("condition_list", "BulkBuyingCategoryConditionListComponent", array(
			"list" => DiscountBulkBuyingUtil::getCategoryCondition(),
			"categories" => $list
		));

		$this->addSelect("new_category", array(
			"name" => "new[category]",
			"options" => $list
		));

		$this->addInput("new_lowest_price", array(
			"name" => "new[price]",
			"value" => ""
		));

		$this->addInput("new_lowest_total", array(
			"name" => "new[total]",
			"value" => ""
		));

		$this->addInput("new_lowest_amount", array(
			"name" => "new[amount]",
			"value" => ""
		));

		$this->addSelect("new_combination", array(
			"name" => "new[combination]",
			"options" => DiscountBulkBuyingUtil::getCombinationType()
		));

		$this->addSelect("new_discount_type", array(
			"name" => "new[type]",
			"options" => DiscountBulkBuyingUtil::getDiscountType(),
			"attr:id" => "new_discount_type"
		));

		$this->addInput("new_discount_amount", array(
			"name" => "new[discount][amount]",
			"value" => ""
		));

		$this->addInput("new_discount_percent", array(
			"name" => "new[discount][percent]",
			"value" => ""
		));

		$this->addInput("new_apply_amount", array(
			"name" => "new[apply]",
			"value" => ""
		));

		$categoryCombination = DiscountBulkBuyingUtil::getCategoryCombinationCondition();

		//カテゴリ毎の条件のすべてを満たす場合
		$this->addCheckBox("category_condition_all", array(
			"name" => "CategoryCombination",
			"value" => DiscountBulkBuyingUtil::COMBINATION_ALL,
			"selected" => ($categoryCombination == DiscountBulkBuyingUtil::COMBINATION_ALL),
			"label" => "すべてを満たす"
		));

		$this->addCheckBox("category_condition_any", array(
			"name" => "CategoryCombination",
			"value" => DiscountBulkBuyingUtil::COMBINATION_ANY,
			"selected" => ($categoryCombination == DiscountBulkBuyingUtil::COMBINATION_ANY),
			"label" => "一部を満たす"
		));

		//category_ids
		$categoryIds = array_keys($list);
		$this->addLabel("category_ids_string", array(
			"text" => implode(",", $categoryIds)
		));

	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
