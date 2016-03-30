<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CommonOrderCustomfieldModule extends SOYShopOrderCustomfield{
	
	private $dao;
	private $list;
	
	//読み込み準備
	function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");		
			$this->list = SOYShop_OrderAttributeConfig::load();
		}
	}
	
	function clear(CartLogic $cart){
		
		$this->prepare();
		
		foreach($this->list as $config){			
			$cart->removeModule($cart->getAttribute("order_customfield_" . $config->getFieldId()));
			$cart->clearAttribute("order_customfield_" . $config->getFieldId() . ".value");
			$cart->clearOrderAttribute("order_customfield_" . $config->getFieldId());
		}
	}
	
	function doPost($param){
		
		$this->prepare();
				
		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			if($obj->getType() == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX){
				$value["value"] = (isset($param[$obj->getFieldId()]) && is_array($param[$obj->getFieldId()])) ? implode(",", $param[$obj->getFieldId()]) : "";
			}else{
				$value["value"] = (isset($param[$obj->getFieldId()])) ? $param[$obj->getFieldId()] : "";
			}
			
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();
			
			if($obj->getType() == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO){
				$config = $obj->getConfig();
				$value["option"] = $config["attributeOtherText"];
			}else{
				$value["option"] = null;
			}
			
			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;
		
		$cart = $this->getCart();

		foreach($param as $key => $obj){
			$module = new SOYShop_ItemModule();
			$module->setId("order_customfield_" . $key);
			$module->setName($obj["label"]);
			$module->setType("customfield_module_" . $key);//カスタムフィールドは仮想的にモジュールがたくさん存在することになる
			$module->setIsVisible(false);
			$cart->addModule($module);
			
			$value = null;
			switch($obj["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$other = (isset($_POST["customfield_module"]["custom_radio_other_text"])) ? $_POST["customfield_module"]["custom_radio_other_text"] : null;
					$array = array();
					$array[] = $obj["value"];
					if(isset($obj["option"]) && $obj["value"] == $obj["option"]){
						$array[] = ":" . $other;
						$val = $other;
					}else{
						$val = null;
					}
					$value = implode("", $array);
					/**
					 * radioの場合だけarray("value" => "", "other" => "")の形式にする
					 */
					$obj["value"] = array("value" => $obj["value"], "other" => $val);
					break;
				default:
					$value = $obj["value"];
					break;
			}
			unset($obj["option"]);
			
			//属性の登録
			$cart->setAttribute("order_customfield_" . $key . ".value", $obj["value"]);
			$cart->setOrderAttribute("order_customfield_" . $key, $obj["label"], $value, true);
		}
	}
	
	function order(CartLogic $cart){
		
		$orderId = $this->getNewOrderId();
		
		$this->prepare();
		
		$this->dao->begin();
		foreach($this->list as $config){
			
			$value = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".value");
			
			$obj = new SOYShop_OrderAttribute();
			$obj->setOrderId($orderId);
			$obj->setFieldId($config->getFieldId());
			
			switch($config->getType()){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$obj->setValue1($value["value"]);
					$obj->setValue2($value["other"]);
					break;
				default:
					$obj->setValue1($value);
					break;
			}
			
			try{
				$this->dao->insert($obj);
			}catch(Exception $e){
			}
			$cart->clearOrderAttribute("order_customfield_" . $config->getFieldId());
		}
		$this->dao->commit();
	}

	function hasError($param){
		$cart = $this->getCart();
		
		$this->prepare();
		
		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			$value["value"] = (isset($param[$obj->getFieldId()])) ? $param[$obj->getFieldId()] : "";
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();
			$value["isRequired"] = (int)$obj->getIsRequired();
			
			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;
		
		$res = false;
		foreach($param as $key => $obj){
			$error = "";
			
			//必須項目の時のみ調べる
			if($obj["isRequired"] == SOYShop_OrderAttribute::IS_REQUIRED){
				//エラーメッセージ用
				switch($obj["type"]){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
						if(strlen($obj["value"]) === 0){
							$error = "値が入力されていません。";
						}
						break;
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						if(strlen($obj["value"]) === 0){
							$error = "選択されていません。";
						}
						break;
					default:
						break;
				}
			}
			if(strlen($error) > 0){
				$cart->setAttribute("order_customfield_" . $key . ".error", $error);
				$res = true;
			}else{
				$cart->clearAttribute("order_customfield_" . $key . ".error");
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
			
			$value = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".value");
			
			$obj = array();
			$obj["name"] = $config->getLabel();
			
			$html = array();
			if(!is_null($config->getAttributeDescription())){
				$html[] = "<p>" . $config->getAttributeDescription() . "</p>";
			}
			$html[] = "<p>" . $config->getForm($value) . "</p>";
			$obj["description"] = implode("\n", $html);
			
			//必須項目であるか？
			$obj["isRequired"] = $config->getIsRequired();
			
			$error = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".error");
			if(isset($error) && strlen($error)){
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
		if(count($list) == 0) return array();
		
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
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$msg = $obj->getValue1();
					if(strlen($obj->getValue2()) > 0){
						$msg .= ":" . $obj->getValue2();
					}
					$value["value"] = $msg;
					break;
				default:
					$value["value"] = $obj->getValue1();
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
			$attrList[$obj->getFieldId()]["config"] = $obj->getConfig();
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
			$attrObjects = array();
			$htmls = array();
			$attrObjects["label"] = $attrList[$attribute->getFieldId()]["label"];
			$name = "Customfield[" . $attribute->getFieldId() . "]";
			switch($attrList[$attribute->getFieldId()]["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					$htmls[] = "<input type=\"text\" name=\"" . $name . "\" value=\"" .$attribute->getValue1() . "\">";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
					$htmls[] = "<textarea name=\"" . $name . "\">" . $attribute->getValue1() . "</textarea>";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
					$values = explode(",", $attribute->getValue1());
					foreach($options as $option){
						$htmls[] = "<label>";
						if(in_array(trim($option), $values)){
							$htmls[] = "<input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . trim($option) . "\" checked=\"checked\">";
						}else{
							$htmls[] = "<input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . trim($option) . "\">";
						}
						$htmls[] = trim($option) . "</label>";
					}
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					if(!isset($attrList[$attribute->getFieldId()]["config"]["option"])) continue;
					$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
					if(count($options) === 0) continue;
					foreach($options as $option){
						$htmls[] = "<label>";
						if($attribute->getValue1() == trim($option)){
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . trim($option) . "\" checked=\"checked\">";
						}else{
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . trim($option) . "\">";
						}
						$htmls[] = trim($option) . "</label>";
					}
					//その他がある場合
					$config = $attrList[$attribute->getFieldId()]["config"];
					if(isset($config["attributeOther"]) && $config["attributeOther"] == 1){
						$htmls[] = "<label>";						
						$otherValue = (isset($config["attributeOtherText"]) && strlen($config["attributeOtherText"])  > 0) ? $config["attributeOtherText"] : "その他";
						if($attribute->getValue1() == $otherValue){
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . $otherValue . "\" checked=\"checked\">";
						}else{
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . $otherValue . "\">";
						}
						$htmls[] = $otherValue;
						$htmls[] = "</label>";
						$htmls[] = "<input type=\"text\" name=\"Customfield[" . $attribute->getFieldId() . "_other_text]\" value=\"" . $attribute->getValue2() . "\">";
					}
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
					if(!isset($attrList[$attribute->getFieldId()]["config"]["option"])) continue;
					$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
					if(count($options) === 0) continue;
					$htmls[] = "<select name=\"" . $name . "\">";
					foreach($options as $option){
						if($attribute->getValue1() == trim($option)){
							$htmls[] = "<option selected>" . trim($option) . "</option>";
						}else{
							$htmls[] = "<option>" . trim($option) . "</option>";
						}
					}
					$htmls[] = "</select>";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RICHTEXT:
				default:
					//未実装
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
}
SOYShopPlugin::extension("soyshop.order.customfield", "common_order_customfield", "CommonOrderCustomfieldModule");
?>