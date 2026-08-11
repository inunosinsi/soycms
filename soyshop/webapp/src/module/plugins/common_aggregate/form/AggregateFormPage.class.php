<?php

class AggregateFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
	}

	function execute(){
		parent::__construct();

		$this->addCheckBox("type_month", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_MONTH,
			"selected" => (!file_exists(dirname(__FILE__)  . "/_AggregateFormPage.html")),
			"label" => AggregateUtil::TYPE_MONTH,
			"class" => "use_calendar"
		));

		$this->addCheckBox("type_day", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_DAY,
			"selected" => false,
			"label" => AggregateUtil::TYPE_DAY,
			"class" => "use_calendar"
		));

		$this->addCheckBox("type_itemrate", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_ITEMRATE,
			"selected" => false,
			"label" => AggregateUtil::TYPE_ITEMRATE,
			"class" => "use_calendar"
		));

		$this->addCheckBox("type_age", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_AGE,
			"selected" => false,
			"label" => AggregateUtil::TYPE_AGE,
			"class" => "use_calendar"
		));

		$this->addCheckBox("type_customer", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_CUSTOMER,
			"selected" => false,
			"label" => AggregateUtil::TYPE_CUSTOMER,
			"class" => "use_date_form"
		));

		/** 顧客別の売上集計で使用する表 **/
		$syear = self::getYearList();
		foreach(array("start", "end") as $t){
			$this->addSelect("select_" . $t . "_year", array(
				"name" => "Customer[" . $t . "][year]",
				"options" => $syear
			));

			$this->addSelect("select_" . $t . "_month", array(
				"name" => "Customer[" . $t . "][month]",
				"options" => range(1,12)
			));

			$this->addSelect("select_" . $t . "_day", array(
				"name" => "Customer[" . $t . "][day]",
				"options" => range(1, 31)
			));
		}

		$this->addCheckBox("method_include_tax", array(
			"name" => "Aggregate[method][]",
			"value" => AggregateUtil::METHOD_MODE_TAX,
			"selected" => true,
			"label" => AggregateUtil::METHOD_INCLUDE_TAX
		));

		$this->addCheckBox("method_include_commission", array(
			"name" => "Aggregate[method][]",
			"value" => AggregateUtil::METHOD_MODE_COMMISSION,
			"selected" => true,
			"label" => AggregateUtil::METHOD_INCLUDE_COMMISSION
		));

		$this->addCheckBox("method_include_point", array(
			"name" => "Aggregate[method][]",
			"value" => AggregateUtil::METHOD_MODE_POINT,
			"selected" => true,
			"label" => AggregateUtil::METHOD_INCLUDE_POINT
		));

		$this->addCheckBox("method_include_discount", array(
			"name" => "Aggregate[method][]",
			"value" => AggregateUtil::METHOD_MODE_DISCOUNT,
			"selected" => true,
			"label" => AggregateUtil::METHOD_INCLUDE_DISCOUNT
		));


		$this->addLabel("aggregate_label_month", array(
			"text" => AggregateUtil::TYPE_MONTH
		));

		$this->addLabel("aggregate_label_day", array(
			"text" => AggregateUtil::TYPE_DAY
		));

		$this->addInput("aggregate_period_start", array(
			"name" => "Aggregate[period][start]",
			"value" => "",
			"readonly" => true
		));

		$this->addInput("aggregate_period_end", array(
			"name" => "Aggregate[period][end]",
			"value" => "",
			"readonly" => true
		));

		$this->addInput("aggregate_filter_customer_name", array(
			"name" => "Aggregate[filter][customer]",
			"value" => ""
		));

		$this->addInput("aggregate_filter_item_name", array(
			"name" => "Aggregate[filter][item]",
			"value" => ""
		));

		$this->addInput("aggregate_filter_price_min", array(
			"name" => "Aggregate[filter][price][min]",
			"value" => ""
		));

		$this->addInput("aggregate_filter_price_max", array(
			"name" => "Aggregate[filter][price][max]",
			"value" => ""
		));

		$this->addCheckBox("aggregate_filter_order_price_max", array(
			"name" => "Aggregate[filter][order][max]",
			"value" => 1,
			"label" => "支払額が最高値のみ取得"
		));

		$this->addInput("aggregate_limit", array(
			"name" => "Aggregate[limit]",
			"value" => 50
		));
	}

	private function getYearList(){
		$list = array();

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT order_date FROM soyshop_order ORDER BY order_date ASC LIMIT 1");
		}catch(Exception $e){
			var_dump($e);
			$res = array();
		}

		if(!count($res) || !isset($res[0]["order_date"])) return $list[] = date("Y");

		$fyear = (int)date("Y", $res[0]["order_date"]);
		$diff = date("Y") - $fyear + 1;

		for ($i = 0; $i < $diff; $i++){
			$list[] = $fyear + $i;
		}

		return array_reverse($list);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}

?>
