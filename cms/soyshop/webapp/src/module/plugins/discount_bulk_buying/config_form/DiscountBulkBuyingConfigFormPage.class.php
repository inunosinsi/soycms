<?php
class DiscountBulkBuyingConfigFormPage extends WebPage{

	private $config;

	/**
	 * コンストラクタ
	 */
	function __construct(){}

	function doPost(){
		if(soy2_check_token()){

			if(isset($_POST["submit_discount"]) && isset($_POST["Discount"]) && is_array($_POST["Discount"])){
				$discount = DiscountBulkBuyingConfigUtil::getDiscount();
				$post = $_POST["Discount"];

				//割引種類
				if(isset($_POST["Type"])){

					if($_POST["Type"] == DiscountBulkBuyingConfigUtil::TYPE_AMOUNT || $_POST["Type"] == DiscountBulkBuyingConfigUtil::TYPE_PERCENT){
						$discount["type"] = $_POST["Type"];
					}

				}

				//割引額
				if(isset($post["amount"]) && is_numeric($post["amount"])){
					$discount["amount"] = $post["amount"];
				}

				//割引率
				if(isset($post["percent"]) && is_numeric($post["percent"])){
					$discount["percent"] = $post["percent"];
				}

				//割引名
				if(isset($post["name"])){
					$discount["name"] = $post["name"];
				}

				//説明
				if(isset($post["description"])){
					$discount["description"] = $post["description"];
				}

				//公開状態
				if(isset($post["status"])){
					$discount["status"] = $post["status"];
				}

				DiscountBulkBuyingConfigUtil::setDiscount($discount);
			}

			//割引条件
			if(isset($_POST["submit_condition"]) && isset($_POST["Condition"]) && is_array($_POST["Condition"])){

				$condition = DiscountBulkBuyingConditionUtil::getCondition();
				$post = $_POST["Condition"];

				//合計金額 オンオフ
				if(isset($post["price_checkbox"]) && is_numeric($post["price_checkbox"])){
					$condition["price_checkbox"] = $post["price_checkbox"];
				}

				//合計金額 金額
				if(isset($post["price_value"]) && is_numeric($post["price_value"])){
					$condition["price_value"] = $post["price_value"];
				}

				//合計商品数 オンオフ
				if(isset($post["amount_checkbox"]) && is_numeric($post["amount_checkbox"])){
					$condition["amount_checkbox"] = $post["amount_checkbox"];
				}

				//合計商品数 金額
				if(isset($post["amount_value"]) && is_numeric($post["amount_value"])){
					$condition["amount_value"] = $post["amount_value"];
				}

				//組み合わせ
				if(isset($post["combination"]) && is_numeric($post["combination"])){
					$condition["combination"] = $post["combination"];
				}


				DiscountBulkBuyingConditionUtil::setCondition($condition);
			}

			$this->config->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();
		$discount = DiscountBulkBuyingConfigUtil::getDiscount();
		$this->buildDiscountForm($discount);//内容

		$this->buildConditionForm();//条件


		/* notice */
	}

	/**
	 * 割引内容のフォーム
	 */
	function buildDiscountForm($discount){
		$this->addForm("discount_form");

		//割引名
		$this->addInput("discount_name", array(
			"name" => "Discount[name]",
			"value" => $discount["name"]
		));


		//カートでの説明文
		$this->addTextarea("discount_description", array(
			"name" => "Discount[description]",
			"text" => $discount["description"]
		));

		//割引 割引額
		$this->addCheckbox("discount_type_amount", array(
			"name" => "Type",
			"value" => DiscountBulkBuyingConfigUtil::TYPE_AMOUNT,
			"selected" => ($discount["type"] == DiscountBulkBuyingConfigUtil::TYPE_AMOUNT),
			"elementId" => "discount_type_amount",
		));

		//割引額
		$this->addInput("discount_amount", array(
			"name" => "Discount[amount]",
			"value" => $discount["amount"]
		));

		//割引 割引率
		$this->addCheckbox("discount_type_percent", array(
			"name" => "Type",
			"value" => DiscountBulkBuyingConfigUtil::TYPE_PERCENT,
			"selected" => ($discount["type"] == DiscountBulkBuyingConfigUtil::TYPE_PERCENT),
			"elementId" => "discount_type_percent",
		));

		//割引率
		$this->addInput("discount_percent", array(
			"name" => "Discount[percent]",
			"value" => $discount["percent"]
		));

		//公開状態 非公開
		$this->addCheckbox("status_type_inactive", array(
			"name" => "Discount[status]",
			"value" => DiscountBulkBuyingConfigUtil::STATUS_INACTIVE,
			"selected" => ($discount["status"] == DiscountBulkBuyingConfigUtil::STATUS_INACTIVE),
			"elementId" => "status_type_inactive",
		));

		//公開状態 公開
		$this->addCheckbox("status_type_active", array(
			"name" => "Discount[status]",
			"value" => DiscountBulkBuyingConfigUtil::STATUS_ACTIVE,
			"selected" => ($discount["status"] == DiscountBulkBuyingConfigUtil::STATUS_ACTIVE),
			"elementId" => "status_type_active",
		));

	}

	/**
	 * 割引条件のフォーム
	 */
	function buildConditionForm(){
		$this->addForm("condition_form");
		$condition = DiscountBulkBuyingConditionUtil::getCondition();

		//合計金額 チェックボックス
		$this->addCheckbox("condition_price_checkbox", array(
			"name" => "Condition[price_checkbox]",
			"value" => 1,
			"selected" => $condition["price_checkbox"],
			"isBoolean" => true,
			"elementId" => "condition_price_checkbox",
		));

		//合計金額 入力
		$this->addInput("condition_price_value", array(
			"name" => "Condition[price_value]",
			"value" => $condition["price_value"]
		));

		//合計商品数 チェックボックス
		$this->addCheckbox("condition_amount_checkbox", array(
			"name" => "Condition[amount_checkbox]",
			"value" => 1,
			"selected" => $condition["amount_checkbox"],
			"isBoolean" => true,
			"elementId" => "condition_amount_checkbox"
		));

		//合計商品数
		$this->addInput("condition_amount_value", array(
			"name" => "Condition[amount_value]",
			"value" => $condition["amount_value"]
		));


		/* 条件の適用 */

		//両方の条件に一致
		$this->addCheckbox("condition_combination_all", array(
			"name" => "Condition[combination]",
			"value" => DiscountBulkBuyingConditionUtil::COMBINATION_ALL,
			"selected" => ($condition["combination"] == DiscountBulkBuyingConditionUtil::COMBINATION_ALL),
			"elementId" => "condition_combination_all"
		));

		//片方の条件に一致
		$this->addCheckbox("condition_combination_any", array(
			"name" => "Condition[combination]",
			"value" => DiscountBulkBuyingConditionUtil::COMBINATION_ANY,
			"selected" => ($condition["combination"] == DiscountBulkBuyingConditionUtil::COMBINATION_ANY),
			"elementId" => "condition_combination_any"
		));
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/DiscountBulkBuyingConfigFormPage.html";
	}

	function setConfigObj($config){
		$this->config = $config;
	}
}
?>
