<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CommonUserCustomfieldModule extends SOYShopUserCustomfield{

	private $dao;
	private $list;

	//読み込み準備
	function prepare(){
		if(!$this->list){
			SOY2::import("domain.user.SOYShop_UserAttribute");
			$this->list = SOYShop_UserAttributeConfig::load();
		}
	}

	function clear($app){
		$this->prepare();

		if(count($this->list)){
			foreach($this->list as $field){
				$app->clearAttribute(self::_attrKey($field->getFieldId()));
			}
		}
	}

	/**
	 * @param array $param 中身は$_POST["user_customfield"]
	 */
	function doPost($param){

		$this->prepare();
		$app = $this->getApp();

		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			if($obj->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX){
				$value["value"] = (isset($param[$obj->getFieldId()]) && is_array($param[$obj->getFieldId()])) ? implode(",", $param[$obj->getFieldId()]) : "";
			}else{
				$value["value"] = (isset($param[$obj->getFieldId()])) ? $param[$obj->getFieldId()] : "";
			}
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();

			if($obj->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO){
				$config = $obj->getConfig();
				$value["option"] = $config["attributeOtherText"];
			}else{
				$value["option"] = null;
			}

			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;

		foreach($param as $key => $obj){
			$value = null;
			switch($obj["type"]){
				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
					$other = (isset($_POST["user_customfield"]["custom_radio_other_text"])) ? $_POST["user_customfield"]["custom_radio_other_text"] : null;
					$array = array();
					$array[] = $obj["value"];
					if(isset($obj["option"]) && $obj["value"] == $obj["option"]){
						$array[] = ":" . $other;
						$val = $other;
					}else{
						$val = null;
					}
					$value = implode("", $array);
					$obj["value"] = array("value" => $obj["value"], "other" => $val);
					break;

				default:
					$value = $obj["value"];
					break;
			}
			unset($obj["option"]);
			$app->setAttribute(self::_attrKey($key), $obj["value"]);
		}

	}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 *
	 * <テンプレート記述例>
	 * <!-- soy:id="has_user_customfield" -->
			<!-- soy:id="user_customfield_list" -->
			<tr>
				<th nowrap scope="row"><!-- soy:id="customfield_name" /--></th>
				<td>
					<!-- soy:id="customfield_form" /-->
				</td>
			</tr>
			<!-- /soy:id="user_customfield_list" -->
			<!-- /soy:id="has_user_customfield" -->
	 *
	 *
	 */
	function getForm($app, int $userId){
		//出力する内容を格納する
		$this->prepare();
		if(!count($this->list)) return array();

		$array = array();
		foreach($this->list as $field){

			//管理画面での呼び出し
			$value = (isset($app)) ? $app->getAttribute(self::_attrKey($field->getFieldId())) : null;

			//マイページ、カートでの編集
			if(is_null($value) && isset($userId)){
				$attrValue = soyshop_get_user_attribute_value($userId, $field->getFieldId(), "string");

				//Typeがradioの場合
				if($field->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO){
					$values = explode(":", $attrValue);
					$value = array();
					$value["value"] = (isset($values[0])) ? $values[0] : null;
					$value["other"] = (isset($values[1])) ? $values[1] : null;
				}else{
					$value = $attrValue;
				}
			}

			$obj = array();
			$obj["name"] = $field->getLabel();
			$obj["form"] = $field->getForm($value);

			$config = $field->getConfig();
			$obj["isRequired"] = (isset($config["isRequired"])) ? (int)$config["isRequired"] : 0;
			$obj["error"] = (isset($app)) ? $app->getAttribute("user_customfield_" . SOYSHOP_ID . "_" . $field->getFieldId() . ".error") : null;

			$array[$field->getFieldId()] = $obj;
		}

		return $array;
	}

	/**
	 * 各項目ごとに、createAdd()を行う。
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){
		$this->prepare();
		if(!count($this->list)) return;

		foreach($this->list as $config){
			$value = (isset($app)) ? $app->getAttribute(self::_attrKey($config->getFieldId())) : null;

			//マイページ、カートでの編集
			if(is_null($value) && isset($userId)){
				$attrValue = soyshop_get_user_attribute_value($userId, $config->getFieldId(), "string");

				//Typeがradioの場合
				if($config->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO){
					$values = explode(":", $attrValue);
					$value = array();
					$value["value"] = (isset($values[0])) ? $values[0] : null;
					$value["other"] = (isset($values[1])) ? $values[1] : null;
				}else{
					$value = $attrValue;
				}
			}

			$h_formID = self::_formId($config->getFieldId());
			$h_formName = self::_formName($config->getFieldId());

			$obj = array();
			$obj["name"] = $config->getLabel();

			switch($config->getType()){
				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					//DefaultValueがあればそれを使う
					$checkbox_value = (is_string($config->getDefaultValue()) && strlen($config->getDefaultValue()) > 0) ? $config->getDefaultValue() : "";
					$h_checkbox_value = htmlspecialchars($checkbox_value, ENT_QUOTES, "UTF-8");
					$pageObj->addCheckbox($h_formID, array(
						"elementId" => $h_formID,
						"name" => $h_formName,
						"value" => $h_checkbox_value,
						"selected" => ($h_checkbox_value == $value)
					));

					$pageObj->addLabel($h_formID. "_text", array(
						"text" => $h_checkbox_value
					));

					break;

				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
					$options = (is_string($config->getOption())) ? explode("\n", str_replace(array("\r\n", "\r"), "\n", $config->getOption())) : array();

					$labelId = 'user_customfield_'. $config->getFieldId();
					$pageObj->addLabel($labelId. "_text", array(
						"text" => $value["value"]
					));

					$radioId = 'user_customfield_radio_'. $config->getFieldId();
					if(count($options)){
						foreach($options as $key => $option){
							$option = trim($option);
							if(strlen($option) > 0){
								$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
								$optionId = $radioId . '_' . $key;

								$pageObj->addCheckbox($optionId, array(
									"elementId" => $optionId,
									"name" => $h_formName,
									"value" => $h_option,
									"selected" => ($option == $value["value"])
								));
							}
						}
					}
					break;

				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_SELECT:
					$options = (is_string($config->getOption())) ? explode("\n", str_replace(array("\r\n", "\r"), "\n", $config->getOption())) : array();
					$options = array_combine($options, $options);
					$options = array_merge(array("" => "----"), $options);

					$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
					$pageObj->addSelect($h_formID, array(
						"id" => $h_formID,
						"name" => $h_formName,
						"options" => $options,
						"selected" => $h_value
					));

					$pageObj->addLabel($h_formID. "_text", array(
						"text" => $h_value
					));
					break;

				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
					$pageObj->addTextarea($h_formID, array(
						"id" => $h_formID,
						"name" => $h_formName,
						"value" => (is_string($value)) ? htmlspecialchars($value, ENT_QUOTES, "UTF-8") : ""
					));
					break;

				default://テキスト
					$pageObj->addInput($h_formID, array(
						"id" => $h_formID,
						"name" => $h_formName,
						"value" => (is_string($value)) ? htmlspecialchars($value, ENT_QUOTES, "UTF-8") : ""
					));
			}
		}
	}

	function hasError($param){
		$this->prepare();
		$app = $this->getApp();

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
			if($obj["isRequired"] == SOYShop_UserAttribute::IS_REQUIRED){
				//エラーメッセージ用
				switch($obj["type"]){
					case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_INPUT:
					case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
						if(strlen($obj["value"]) === 0){
							$error = "値が入力されていません。";
						}
						break;
					case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
					case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_SELECT:
						if(strlen($obj["value"]) === 0){
							$error = "選択されていません。";
						}
						break;
					default:
						break;
				}
			}
			if(strlen($error) > 0){
				$app->setAttribute("user_customfield_" . SOYSHOP_ID . "_" . $key . ".error", $error);
				$res = true;
			}else{
				$app->clearAttribute("user_customfield_" . SOYSHOP_ID . "_" . $key . ".error");
			}
		}

		return ($res) ? true : false;
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 */
	function confirm($app){
		$this->prepare();
		if(!count($this->list)) return array();

		//出力する内容を格納する
		$array = array();
		foreach($this->list as $field){
			$value = $app->getAttribute(self::_attrKey($field->getFieldId()));

			$obj = array();
			$obj["name"] = $field->getLabel();

			switch($field->getType()){
				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
					$obj["confirm"] = $value["value"];
					if(isset($value["other"])){
						$obj["confirm"] .= ":" . $value["other"];
					}
					break;

				default:
					$obj["confirm"] = $value;
					break;
			}

			$array[$field->getFieldId()] = $obj;
		}

		return $array;
	}

	/**
	 * UserAttributeに登録する
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		$this->prepare();
		if(!count($this->list)) return;

		foreach($this->list as $obj){

			//管理画面での更新
			if(is_null($app)){
				$value = (isset($_POST["user_customfield"][$obj->getFieldId()])) ? $_POST["user_customfield"][$obj->getFieldId()] : "";
				if($obj->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO){
					//入れたい配列に変換出来たら、改めて$valueに挿入
					$value = self::_convertArray($obj, $value);//$values;
				}else if($obj->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX){
					$value = (is_array($value)) ? implode(",", $value) : "";
				}

			//マイページ、カートでの更新
			}else{
				$value = $app->getAttribute(self::_attrKey($obj->getFieldId()));
			}

			switch($obj->getType()){
				case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
					$text = $value["value"];
					if(isset($value["other"])){
						$text = $text . ":" . $value["other"];
					}
					$value = $text;
					break;
				default:
					break;
			}

			$newAttr = soyshop_get_user_attribute_object($userId, $obj->getFieldId());
			$newAttr->setValue($value);
			soyshop_save_user_attribute_object($newAttr);

			//管理画面モード以外はセッションを削除
			if(isset($app)) $app->clearAttribute(self::_attrKey($obj->getFieldId()));
		}
	}

	private function _convertArray($obj, $value){
		$other = (isset($_POST["user_customfield"]["custom_radio_other_text"])) ? $_POST["user_customfield"]["custom_radio_other_text"] : null;
		$values = array();
		$values["value"] = $value;
		$config = $obj->getConfig();
		$values["other"] = (isset($config["attributeOtherText"]) && $value == $config["attributeOtherText"]) ? $other : null;

		return $values;
	}

	/**
	 * @param string $fieldId
	 * @return string MyPage/Cart の attributeのKey
	 */
	private function _attrKey(string $fieldId){
		return "user_customfield_" . SOYSHOP_ID . "_" . $fieldId . ".value";
	}

	/**
	 * @param string $fieldId
	 * @param boolean $isRadio
	 * @return string MyPage/Cart の attributeのKey
	 */
	private function _formId(string $fieldId, bool $isRadio=false){
		if($isRadio){
			return "user_customfield_radio_". htmlspecialchars($fieldId, ENT_QUOTES, "UTF-8");
		}else{
			return "user_customfield_". htmlspecialchars($fieldId, ENT_QUOTES, "UTF-8");
		}
	}

	/**
	 * @param string $fieldId
	 * @return string MyPage/Cart の attributeのKey
	 */
	private function _formName(string $fieldId){
		return "user_customfield[". htmlspecialchars($fieldId, ENT_QUOTES, "UTF-8"). "]";
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","common_user_customfield","CommonUserCustomfieldModule");
