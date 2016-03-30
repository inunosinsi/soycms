<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CommonOrderDateCustomfieldModule extends SOYShopOrderCustomfield{
	
	private $dao;
	private $list;
	
	//読み込み準備
	function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");		
			$this->list = SOYShop_OrderDateAttributeConfig::load();
		}
	}
	
	function clear(CartLogic $cart){
		
		$this->prepare();
	
		foreach($this->list as $config){			
			$cart->removeModule($cart->getAttribute("order_date_customfield_" . $config->getFieldId()));
			$cart->clearAttribute("order_date_customfield_" . $config->getFieldId() . ".value");
			$cart->clearOrderAttribute("order_date_customfield_" . $config->getFieldId());
		}
	}
	
	function doPost($param){
		
		$this->prepare();
		
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
					$value = $this->getDateText($obj["value"]["date"]);
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$value = $this->getDateText($obj["value"]["start"]) . " ～ " . $this->getDateText($obj["value"]["end"]);
					break;
			}
			
			//属性の登録
			$cart->setAttribute("order_date_customfield_" . $key . ".value", $obj["value"]);
			$cart->setOrderAttribute("order_date_customfield_" . $key, $obj["label"], $value, true);
		}
	}
	
	function order(CartLogic $cart){

		$orderId = $this->getNewOrderId();
		
		$this->prepare();
		
		$this->dao->begin();
		foreach($this->list as $config){
			
			$value = $cart->getAttribute("order_date_customfield_" . $config->getFieldId() . ".value");
			
			$obj = new SOYShop_OrderDateAttribute();
			$obj->setOrderId($orderId);
			$obj->setFieldId($config->getFieldId());
			
			switch($config->getType()){
				case "date":
					$obj->setValue1($this->getTimeStamp($value["date"]));
					break;
				case "period":
					$obj->setValue1($this->getTimeStamp($value["start"]));
					$obj->setValue2($this->getTimeStamp($value["end"]));
					break;
			}
			
			try{
				$this->dao->insert($obj);
			}catch(Exception $e){
			}
			$cart->clearOrderAttribute("order_date_customfield_" . $config->getFieldId());
		}
		$this->dao->commit();
	}

	function hasError($param){
		$cart = $this->getCart();
		
		$this->prepare();
				
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
					$start = $this->getTimestamp($value["start"]);
					$end = $this->getTimestamp($value["end"]);
					if($start>$end){
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
		
		return ($res) ? true : false;
	}
	

	function getForm(CartLogic $cart){
		//出力する内容を格納する
		$array = array();
		
		$this->prepare();
		
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
	
	function display($orderId){
		
		$this->prepare();
		
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
					$value["value"] = $this->getTimeText($obj->getValue1());
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$value["value"] = $this->getTimeText($obj->getValue1()) . " ～ " . $this->getTimeText($obj->getValue2());
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
	function edit($orderId){
		
		$this->prepare();

		//扱いやすい形に整形
		$attrList = array();
		foreach($this->list as $obj){
			$attrList[$obj->getFieldId()]["label"] = $obj->getLabel();
			$attrList[$obj->getFieldId()]["type"] = $obj->getType();
		}
		if(count($attrList) === 0) return array();
		
		try{
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
		
		$array = array();
		foreach($attributes as $attribute){
			if(!isset($attrList[$attribute->getFieldId()])) continue;
			//ラベルとフォームを放り込む変数
			$attrObjects = array();
			$htmls = array();
			$attrObjects["label"] = $attrList[$attribute->getFieldId()]["label"];
			$name = "Customfield[" . $attribute->getFieldId() . "]";
			switch($attrList[$attribute->getFieldId()]["type"]){
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					if(is_null($attribute->getValue1())) continue;
					$name = $name . "[date]";
					$value1 = date("Y-m-d", $attribute->getValue1());
					$dateArray = explode("-", $value1);
					$htmls[] = $this->buildSelectBox($dateArray[0], $name, "year") . "年";
					$htmls[] = $this->buildSelectBox($dateArray[1], $name, "month") . "月";
					$htmls[] = $this->buildSelectBox($dateArray[2], $name, "day") . "日";
					
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					if(is_null($attribute->getValue1()) || is_null($attribute->getValue2())) continue;
					{
						$startName = $name . "[start]";
						$value1 = date("Y-m-d", $attribute->getValue1());
						$dateArray = explode("-", $value1);
						$htmls[] = $this->buildSelectBox($dateArray[0], $startName, "year") . "年";
						$htmls[] = $this->buildSelectBox($dateArray[1], $startName, "month") . "月";
						$htmls[] = $this->buildSelectBox($dateArray[2], $startName, "day") . "日";
						$htmls[] = "～";
					}
					{
						$endName = $name . "[end]";
						$value2 = date("Y-m-d", $attribute->getValue2());
						$dateArray = explode("-", $value2);
						$htmls[] = $this->buildSelectBox($dateArray[0], $endName, "year") . "年";
						$htmls[] = $this->buildSelectBox($dateArray[1], $endName, "month") . "月";
						$htmls[] = $this->buildSelectBox($dateArray[2], $endName, "day") . "日";
					}
					break;
			}
			$attrObjects["form"] = implode("\n", $htmls);
			$array[] = $attrObjects;
		}
		
		return $array;
		
	}
	
	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config($orderId){
		$this->prepare();
		
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
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attributes = array();
		}
		
		$array = array();
		foreach($attributes as $obj){
			if(!isset($list[$obj->getFieldId()])) continue;
			$value["label"] = $list[$obj->getFieldId()]["label"];
			$value["value1"] = $obj->getValue1();
			$value["value2"] = $obj->getValue2();
			$value["type"] = $list[$obj->getFieldId()]["type"];
			
			$array[$obj->getFieldId()] = $value;
		}
		
		return $array;
	}

	function getTimestamp($value){
		return mktime(0, 0, 0, $value["month"], $value["day"], $value["year"]);
	}
	
	function getDateText($value){
		return $value["year"] . "-" . $value["month"] . "-" . $value["day"];
	}
	function getTimeText($value){
		return date("Y", $value) . "-" . date("m", $value) . "-" . date("d", $value);
	}
	
	//最新の注文IDを取得する
	function getNewOrderId(){
		$dao = new SOY2DAO();
		
		$sql = "SELECT id "
			  ."FROM soyshop_order "
			  ."ORDER BY id desc "
			  ."LIMIT 1";
		try{
			$result = $dao->executeQuery($sql);
			$id = $result[0]["id"] + 1;
		}catch(Exception $e){
			$id = 1;
		}
		
		return $id;
	}
	
	
	function buildSelectBox($value, $name, $type="year"){
		$html[] = "<select name=\"" . $name . "[" . $type . "]\">";
		
		switch($type){
			case "year":
				$year = date("Y", time());
				$start = $year - 5;
				$end = $year + 4;
				for($i = $start; $i <= $end; $i++){
					$html[] = $this->buildSelectBoxOption($i, $value);
				}
				break;
			case "month":
				for($i = 1; $i <= 12; $i++){
					if(strlen($i) === 1) $i = "0" . $i;
					$html[] = $this->buildSelectBoxOption($i, $value);
				}
				break;
			case "day":
				for($i = 1; $i <= 31; $i++){
					if(strlen($i) === 1) $i = "0" . $i;
					$html[] = $this->buildSelectBoxOption($i, $value);
				}
				break;
		}
		
		$html[] = "</select>";
		
		return implode("\n", $html);
	}
	
	function buildSelectBoxOption($int, $value){
		if($int == $value){
			$html = "<option value=\"" . $int . "\" selected=\"selected\">" . $int . "</option>";
		}else{
			$html = "<option value=\"" . $int . "\">" . $int . "</option>";
		}
		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "common_order_date_customfield", "CommonOrderDateCustomfieldModule");
?>