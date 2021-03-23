<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class UserCustomSearchFieldModule extends SOYShopUserCustomfield{

	private $dbLogic;

	function clear($app){
		self::_prepare();

		foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
			$attributeKey = self::getAttributeKey($key);
			$app->clearAttribute($attributeKey);
		}
	}

	/**
	 * @param array $param 中身は$_POST["user_customfield"]
	 */
	function doPost($param){

		if(isset($_POST["user_custom_search"])){
			self::_prepare();
			$app = $this->getApp();

			foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
				$attributeKey = self::getAttributeKey($key);
				$v = (isset($_POST["user_custom_search"][$key])) ? $_POST["user_custom_search"][$key] : null;
				$app->setAttribute($attributeKey, $v);
			}
		}
	}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 *
	 */
	function getForm($app, $userId){
		// 現時点では管理画面のみ
		//if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			self::_prepare();

			$values = $this->dbLogic->getByUserId($userId);

			//出力する内容を格納する
			$array = array();

			$configs = UserCustomSearchFieldUtil::getConfig();
			if(count($configs)){
				SOY2::import("module.plugins.user_custom_search_field.component.FieldFormComponent");
				foreach($configs as $key => $field){
					//公開側で項目を出力するか？
					if(!SOYSHOP_ADMIN_PAGE && isset($field["is_admin_only"]) && $field["is_admin_only"] == UserCustomSearchFieldUtil::DISPLAY_ADMIN_ONLY) continue;

					$obj = array();
					$obj["name"] = htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8");
					if(SOYSHOP_ADMIN_PAGE) $obj["name"] .= " (" . UserCustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")";

					$value = (isset($values[$key])) ? $values[$key] : null;
					if(is_null($value)){
						$app = $this->getApp();
						if(!is_null($app)){
							$attributeKey = self::getAttributeKey($key);
							$value = $app->getAttribute($attributeKey);
						}
					}
					$obj["form"] = FieldFormComponent::buildForm($key, $field, $value);
					$array["ucsf_" .$key] = $obj;
				}
			}
			return $array;
		//}
	}

	/**
	 * 各項目ごとに、createAdd()を行う。 soy:id="usf_{field_id}"にする
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){
		self::_prepare();
		$values = $this->dbLogic->getByUserId($userId);

		//マイページとカートで動作 URLで判断
		if(
			strpos($_SERVER["REQUEST_URI"], soyshop_get_cart_uri()) != false ||
			strpos($_SERVER["REQUEST_URI"], soyshop_get_mypage_uri()) !== false && (strpos($_SERVER["REQUEST_URI"], "/register") || strpos($_SERVER["REQUEST_URI"], "/edit"))
		) {
			foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){

				$usfValue = null;
				if(!is_null($app)){
					$attributeKey = self::getAttributeKey($key);
					$usfValue = $app->getAttribute($attributeKey);
				}

				if(is_null($usfValue) && isset($values[$key])) $usfValue = $values[$key];

				$nameProperty = "user_custom_search[" . htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "]";

				switch($field["type"]){
					case UserCustomSearchFieldUtil::TYPE_TEXTAREA:
						$pageObj->addTextArea("usf_" . $key, array(
							"name" => $nameProperty,
							"value" => $usfValue
						));
						break;
					case UserCustomSearchFieldUtil::TYPE_CHECKBOX:
						if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
							$options = explode("\n", $field["option"]);
							for($i = 0; $i < count($options); $i++){
								$opt = htmlspecialchars(trim($options[$i]), ENT_QUOTES, "UTF-8");
								$pageObj->addCheckBox("usf_" . $key . "_" . $i, array(
									"name" => $nameProperty . "[]",
									"value" => $opt,
									"selected" => (isset($usfValue) && is_array($usfValue) && count($usfValue) && array_search($opt, $usfValue) !== false),
									"label" => $opt
								));
							}
						}
						$pageObj->addLabel("usf_" . $key . "_text", array(
							"text" => (isset($usfValue) && is_array($usfValue)) ? implode(",", $usfValue) : ""
						));
						break;
					case UserCustomSearchFieldUtil::TYPE_RADIO:
						if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
							$options = explode("\n", $field["option"]);
							for($i = 0; $i < count($options); $i++){
								$opt = htmlspecialchars(trim($options[$i]), ENT_QUOTES, "UTF-8");
								$pageObj->addCheckBox("usf_" . $key . "_" . $i, array(
									"name" => $nameProperty,
									"value" => $opt,
									"selected" => ($opt == $usfValue),
									"label" => $opt
								));
							}
						}

						$pageObj->addLabel("usf_" . $key . "_text", array(
							"text" => $usfValue
						));
						break;
					case UserCustomSearchFieldUtil::TYPE_SELECT:
						$opts = array();
						if (isset ($field["option"]) && strlen(trim($field["option"])) > 0) {
							$options = explode("\n", $field["option"]);
							for($i = 0; $i < count($options); $i++){
								$opts[] = htmlspecialchars(trim($options[$i]), ENT_QUOTES, "UTF-8");
							}
						}
						$pageObj->addSelect("usf_" . $key, array(
							"name" => $nameProperty,
							"options" => $opts,
							"selected" => $usfValue
						));
						$pageObj->addLabel("usf_" . $key . "_text", array(
							"text" => $usfValue
						));
						break;
					case UserCustomSearchFieldUtil::TYPE_RICHTEXT:
					case UserCustomSearchFieldUtil::TYPE_DATE:
						//公開側で使用不可
						break;
					case UserCustomSearchFieldUtil::TYPE_MAILADDRESS:
						$pageObj->addInput("usf_" . $key, array(
							"type" => "email",
							"name" => $nameProperty,
							"value" => $usfValue
						));
						break;
					case UserCustomSearchFieldUtil::TYPE_URL:
						$pageObj->addInput("usf_" . $key, array(
							"type" => "url",
							"name" => $nameProperty,
							"value" => $usfValue
						));
						break;
					default:
						$pageObj->addInput("usf_" . $key, array(
							"name" => $nameProperty,
							"value" => $usfValue
						));
				}
			}
		//入力画面以外
		} else {
			foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
				$usfValue = (isset($values[$key])) ? $values[$key] : null;

	            switch($field["type"]){
	                case UserCustomSearchFieldUtil::TYPE_CHECKBOX:
	                    if(strlen($field["option"])){
	                        $vals = explode(",", $usfValue);
	                        $opts = explode("\n", $field["option"]);
	                        foreach($opts as $i => $opt){
	                            $opt = trim($opt);
	                            $pageObj->addModel($key . "_"  . $i . "_visible", array(
	                                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
	                                "visible" => (in_array($opt, $vals))
	                            ));

	                            $pageObj->addLabel($key . "_" . $i, array(
	                                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
	                                "text" => $opt
	                            ));
	                        }
	                    }
	                    break;
					default:
						$pageObj->addModel($key . "_visible", array(
							"soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
							"visible" => (strlen($usfValue))
						));

						$pageObj->addLabel($key, array(
							"soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
							"html" => (isset($usfValue)) ? $usfValue : null
						));

						//隠しモード
						if($field["type"] == UserCustomSearchFieldUtil::TYPE_DATE){
							$pageObj->addLabel($key . "_wareki", array(
								"soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
								"html" => (isset($usfValue)) ? date("Y年m月d日", $usfValue) : null
							));
						}
	            }
			}
		}
	}

	function hasError($param){
		/** @ToDo 必須の設定をそのうち追加したいところ **/
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 */
	function confirm($app){
		//出力する内容を格納する
		$array = array();

		self::_prepare();

		foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
			//公開側の対応
			if(!SOYSHOP_ADMIN_PAGE && isset($field["is_admin_only"]) && $field["is_admin_only"] == UserCustomSearchFieldUtil::DISPLAY_ADMIN_ONLY) continue;

			$obj = array();
			$obj["name"] = $field["label"];

			$attributeKey = self::getAttributeKey($key);
			$value = $app->getAttribute($attributeKey);
			if(is_array($value)) $value = implode(",", $value);
			$obj["confirm"] = $value;
			$array[$key] = $obj;
		}

		return $array;

	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		self::_prepare();

		//登録用の配列
		$values = array();

		//管理画面側での登録処理
		if(SOYSHOP_ADMIN_PAGE){
			if(isset($_POST["user_custom_search"])){
				$values = $_POST["user_custom_search"];
			}
		//公開側での登録処理
		}else{
			foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
				$attributeKey = self::getAttributeKey($key);
				$values[$key] = $app->getAttribute($attributeKey);
				$app->clearAttribute($attributeKey);
			}
		}

		$this->dbLogic->save($userId, $values);
	}

	/**
	 * @param string $fieldId
	 * @return string MyPage/Cart の attributeのKey
	 */
	private function getAttributeKey($fieldId){
		return "user_custom_search_field_" . SOYSHOP_ID . "_" . $fieldId . ".value";
	}

	private function _prepare(){
		if(!$this->dbLogic){
			$this->dbLogic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic");
			SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		}
		if(!defined("SOYSHOP_ADMIN_PAGE")) define("SOYSHOP_ADMIN_PAGE", false);
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","user_custom_search_field","UserCustomSearchFieldModule");
