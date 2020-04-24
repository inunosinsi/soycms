<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class UserGoogleMapCustomFieldModule extends SOYShopUserCustomfield{

	const PLUGIN_ID = "user_google_map";

	private $dbLogic;

	function clear($app){}

	/**
	 * @param array $param 中身は$_POST["user_customfield"]
	 */
	function doPost($param){}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 *
	 */
	function getForm($app, $userId){}

	/**
	 * 各項目ごとに、createAdd()を行う。
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}

	function hasError($param){
		/** @ToDo 必須の設定をそのうち追加したいところ **/
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 */
	function confirm($app){}

	/**
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		if(isset($_POST["user_google_map"])){
			$userAttrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			foreach($_POST["user_google_map"] as $key => $v){
				$attr = new SOYShop_UserAttribute();
				$attr->setUserId($userId);
				$attr->setFieldId(self::PLUGIN_ID . "_" . $key);
				$attr->setValue($v);
				try{
					$userAttrDao->insert($attr);
				}catch(Exception $e){
					try{
						$userAttrDao->update($attr);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","user_google_map","UserGoogleMapCustomFieldModule");
