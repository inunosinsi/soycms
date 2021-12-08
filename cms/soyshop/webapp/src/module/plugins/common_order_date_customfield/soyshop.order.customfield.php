<?php

class CommonOrderDateCustomfieldModule extends SOYShopOrderCustomfield{

	private $dao;
	private $list;

	//読み込み準備
	private function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
			foreach(SOYShop_OrderDateAttributeConfig::load() as $config){
				//管理画面側なら必ずフォームを表示する or 公開側の場合はisAdminOnlyが0であれば表示する
				if(
					(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE) ||
					($config->getIsAdminOnly() != SOYShop_OrderDateAttribute::DISPLAY_ADMIN_ONLY)
				) {
					$this->list[] = $config;
				}
			}
		}
	}

	function clear(CartLogic $cart){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return;

		foreach($this->list as $config){
			$moduleId = $cart->getAttribute("order_date_customfield_" . $config->getFieldId());
			if(is_string($moduleId)) $cart->removeModule($moduleId);
			$cart->clearAttribute("order_date_customfield_" . $config->getFieldId() . ".value");
			$cart->clearOrderAttribute("order_date_customfield_" . $config->getFieldId());
		}
	}

	function doPost(array $param){

		self::prepare();
		if(is_null($this->list) && !count($this->list)) return;

		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			$value["value"] = $param[$obj->getFieldId()];
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();

			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;

		$cart = $this->getCart();

		foreach($param as $key => $obj){
			$module = new SOYShop_ItemModule();
			$module->setId("order_date_customfield_" . $key);
			$module->setName($obj["label"]);
			$module->setType("customfield_module_" . $key);//カスタムフィールドは仮想的にモジュールがたくさん存在することになる
			$module->setIsVisible(false);
			$cart->addModule($module);

			$value = null;
			switch($obj["type"]){
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					$value = soyshop_convert_date_string_by_array($obj["value"]["date"]);
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$value = soyshop_convert_date_string_by_array($obj["value"]["start"]) . " ～ " . soyshop_convert_date_string_by_array($obj["value"]["end"]);
					break;
			}

			//属性の登録
			$cart->setAttribute("order_date_customfield_" . $key . ".value", $obj["value"]);
			$cart->setOrderAttribute("order_date_customfield_" . $key, $obj["label"], $value, true, true);
		}
	}

	function complete(CartLogic $cart){

		$orderId = $cart->getAttribute("order_id");
		if(!is_numeric($orderId)){
			throw new Exception("No order.id designated for this order custom field.");
		}

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return;

		foreach($this->list as $config){

			$value = $cart->getAttribute("order_date_customfield_" . $config->getFieldId() . ".value");

			$obj = new SOYShop_OrderDateAttribute();
			$obj->setOrderId($orderId);
			$obj->setFieldId($config->getFieldId());

			switch($config->getType()){
				case "date":
					$obj->setValue1(soyshop_convert_timestamp_on_array($value["date"]));
					break;
				case "period":
					$obj->setValue1(soyshop_convert_timestamp_on_array($value["start"]));
					$obj->setValue2(soyshop_convert_timestamp_on_array($value["end"]));
					break;
			}

			$this->dao->insert($obj);

			$cart->clearOrderAttribute("order_date_customfield_" . $config->getFieldId());
		}
	}

	function hasError(array $param){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		$cart = $this->getCart();

		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			$value["value"] = $param[$obj->getFieldId()];
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();

			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;

		$error = "";
		$res = false;
		foreach($param as $key => $obj){

			switch($obj["type"]){
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					//スルー
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$value = $obj["value"];
					$start = soyshop_convert_timestamp_on_array($value["start"]);
					$end = soyshop_convert_timestamp_on_array($value["end"]);
					if($start > $end){
						$error = "入力した内容が正しくありません。";
					}
					break;
			}

			if(strlen($error) > 0){
				$cart->setAttribute("order_date_customfield_" . $key . ".error",$error);
				$res = true;
			}else{
				$cart->clearAttribute("order_date_customfield_" . $key . ".error");
			}
		}

		return $res;
	}


	function getForm(CartLogic $cart){
		self::prepare();

		if(is_null($this->list) || !count($this->list)) return array();

		//出力する内容を格納する
		$array = array();
		foreach($this->list as $config){
			$value = null;

			$value = $cart->getAttribute("order_date_customfield_" . $config->getFieldId() . ".value");

			$obj = array();
			$obj["name"] = $config->getLabel();

			$html = array();
			if(!is_null($config->getAttributeDescription())){
				$html[] = "<p>" . $config->getAttributeDescription() . "</p>";
			}
			$html[] = "<p>" . $config->getForm($value)."</p>";
			$obj["description"] = implode("\n", $html);

			// @ToDo 管理画面側の注文で注釈を出力できる
			$obj["annotation"] = null;

			$error = $cart->getAttribute("order_date_customfield_" . $config->getFieldId() . ".error");
			if(isset($error)){
				$obj["error"] = $error;
			}else{
				$obj["error"] = null;
			}

			$array[$config->getFieldId()] = $obj;
		}

		return $array;
	}

	function display(int $orderId){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//リストの再配列
		$array = array();
		foreach($this->list as $obj){
			$array[$obj->getFieldId()]["label"] = $obj->getLabel();
			$array[$obj->getFieldId()]["type"] = $obj->getType();
		}
		$list = $array;
		if(count($list) == 0)return array();

		try{
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attributes = array();
		}

		$array = array();
		foreach($attributes as $obj){
			if(!isset($list[$obj->getFieldId()])) continue;
			$value["name"] = $list[$obj->getFieldId()]["label"];

			switch($list[$obj->getFieldId()]["type"]){
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					$value["value"] = soyshop_convert_date_string($obj->getValue1());
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$value["value"] = soyshop_convert_date_string($obj->getValue1()) . " ～ " . soyshop_convert_date_string($obj->getValue2());
					break;
			}

			$array[] = $value;
		}

		return $array;
	}

	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit(int $orderId){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//扱いやすい形に整形
		$attrList = array();
		foreach($this->list as $obj){
			$attrList[$obj->getFieldId()]["label"] = $obj->getLabel();
			$attrList[$obj->getFieldId()]["type"] = $obj->getType();
		}
		if(count($attrList) === 0) return array();

		try{
			$attrs = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attrs = array();
		}
		if(!count($attrs)) return array();

		//値が登録されていなければフィールドを追加
		foreach($attrList as $fieldId => $attrConf){
			if(!isset($attrs[$fieldId])){
				$attrObj = new SOYShop_OrderDateAttribute();
				$attrObj->setFieldId($fieldId);
				$attrObj->setOrderId($orderId);
				$attrs[$fieldId] = $attrObj;
			}
		}

		SOY2::import("module.plugins.common_order_date_customfield.component.DateSelectBoxComponent");

		$array = array();
		foreach($attrs as $attr){
			if(!isset($attrList[$attr->getFieldId()])) continue;
			//ラベルとフォームを放り込む変数
			$attrObj = array();
			$html = array();
			$attrObj["label"] = $attrList[$attr->getFieldId()]["label"];
			$name = "Customfield[" . $attr->getFieldId() . "]";
			switch($attrList[$attr->getFieldId()]["type"]){
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					$name = $name . "[date]";
					$v = (is_numeric($attr->getValue1())) ? (int)$attr->getValue1() : 0;
					$dateArr = soyshop_convert_date_array_by_timestamp($v);

					$html[] = DateSelectBoxComponent::build((int)$dateArr["year"], $name, "year") . "年";
					$html[] = DateSelectBoxComponent::build((int)$dateArr["month"], $name, "month") . "月";
					$html[] = DateSelectBoxComponent::build((int)$dateArr["day"], $name, "day") . "日";

					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					{
						$startName = $name . "[start]";
						$v = (is_numeric($attr->getValue1())) ? (int)$attr->getValue1() : 0;
						$dateArr = soyshop_convert_date_array_by_timestamp($v);

						$html[] = DateSelectBoxComponent::build((int)$dateArr["year"], $startName, "year") . "年";
						$html[] = DateSelectBoxComponent::build((int)$dateArr["month"], $startName, "month") . "月";
						$html[] = DateSelectBoxComponent::build((int)$dateArr["day"], $startName, "day") . "日";
						$html[] = "～";
					}
					{
						$endName = $name . "[end]";
						$v = (is_numeric($attr->getValue2())) ? (int)$attr->getValue2() : 0;
						$dateArr = soyshop_convert_date_array_by_timestamp($v);

						$html[] = DateSelectBoxComponent::build((int)$dateArr["year"], $endName, "year") . "年";
						$html[] = DateSelectBoxComponent::build((int)$dateArr["month"], $endName, "month") . "月";
						$html[] = DateSelectBoxComponent::build((int)$dateArr["day"], $endName, "day") . "日";
					}
					break;
			}
			$attrObj["form"] = implode("\n", $html);
			$array[] = $attrObj;
		}

		return $array;
	}

	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config(int $orderId){
		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//リストの再配列
		$array = array();
		foreach($this->list as $key => $obj){
			$values = array();
			$values["label"] = $obj->getLabel();
			$values["type"] = $obj->getType();
			$array[$obj->getFieldId()] = $values;
		}
		$list = $array;

		try{
			$attrs = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attrs = array();
		}

		//値が登録されていなければフィールドを追加
		foreach($list as $fieldId => $attrConf){
			if(!isset($attrs[$fieldId])){
				$attrObj = new SOYShop_OrderDateAttribute();
				$attrObj->setFieldId($fieldId);
				$attrObj->setOrderId($orderId);
				$attrs[$fieldId] = $attrObj;
			}
		}

		$array = array();
		foreach($attrs as $obj){
			if(!isset($list[$obj->getFieldId()])) continue;
			$value["label"] = $list[$obj->getFieldId()]["label"];
			$value["value1"] = $obj->getValue1();
			$value["value2"] = $obj->getValue2();
			$value["type"] = $list[$obj->getFieldId()]["type"];

			$array[$obj->getFieldId()] = $value;
		}

		return $array;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "common_order_date_customfield", "CommonOrderDateCustomfieldModule");
