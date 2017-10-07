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
		self::prepare();

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
			self::prepare();
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
		self::prepare();

		$values = $this->dbLogic->getByUserId($userId);

		//出力する内容を格納する
		$array = array();

		$configs = UserCustomSearchFieldUtil::getConfig();
		if(count($configs)){
			SOY2::import("module.plugins.user_custom_search_field.component.FieldFormComponent");
			foreach($configs as $key => $field){

				$obj = array();
				$name = htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8");

				if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
					$obj["name"] = $name . " (" . UserCustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")";
				}else{
					$obj["name"] = $name;
				}

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
	}

	/**
	 * 各項目ごとに、createAdd()を行う。
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){
		self::prepare();
		$values = $this->dbLogic->getByUserId($userId);

		foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){

            //多言語化対応はデータベースから値を取得した時点で行っている
            $usfValue = (isset($values[$key])) ? $values[$key] : null;

            $pageObj->addModel($key . "_visible", array(
                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
                "visible" => (strlen($usfValue))
            ));

            $pageObj->addLabel($key, array(
                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
                "html" => (isset($usfValue)) ? $usfValue : null
            ));

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

		self::prepare();

		foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){

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
		self::prepare();

		//登録用の配列
		$values = array();

		//管理画面側での登録処理
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			if(isset($_POST["user_custom_search"])){
				$values = $_POST["user_custom_search"];
			}
		//公開側での登録処理
		}else{
			foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
				$attributeKey = self::getAttributeKey($key);
				$values[$key] = $app->getAttribute($attributeKey);
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

	private function prepare(){
		if(!$this->dbLogic){
			$this->dbLogic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic");
			SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","user_custom_search_field","UserCustomSearchFieldModule");
