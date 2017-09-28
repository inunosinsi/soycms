<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class UserGroupCustomField extends SOYShopUserCustomfield{

	function clear($app){}

	/**
	 * @param array $param 中身は$_POST["user_customfield"]
	 */
	function doPost($param){
		// 公開側でグループ登録の予定はない
	}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 *
	 */
	function getForm($app, $userId){

		//出力する内容を格納する
		$array = array();
		$form = self::getGroupForm($userId);

		// nameとformを持つ配列を入れる
		$array["user_group_plugin"] = array("name" => "グループ", "form" => $form);

		return $array;
	}

	private function getGroupForm($userId){
		$list = self::getGroupList();
		if(!count($list)) return "";

		$html = array();
		foreach($list as $groupId => $v){
			$label = $v["name"];
			if(strlen($v["code"])) $label .= "(" . $v["code"] . ")";
			if(array_search($groupId, self::getGroupIdListByUserId($userId)) !== false){
				$html[] = '<label><input type="checkbox" name="user_group_plugin[]" value="'. $groupId .'" checked="checked">' . $label . '</label>';
			}else{
				$html[] = '<label><input type="checkbox" name="user_group_plugin[]" value="'. $groupId .'">' . $label . '</label>';
			}
		}

		return implode("<br>\n", $html);
	}

	private function getGroupList(){
		try{
			$groups = self::groupDao()->get();
		}catch(Exception $e){
			return array();
		}

		if(!count($groups)) return array();

		$list = array();
		foreach($groups as $group){
			if(strlen($group->getName())){
				$list[$group->getId()] = array("name" => $group->getName(), "code" => $group->getCode());
			}
		}
		return $list;
	}

	private function getGroupIdListByUserId($userId){
		static $list;
		if(isset($list)) return $list;
		try{
			$groupings = self::groupingDao()->getByUserId($userId);
		}catch(Exception $e){
			return array();
		}
		if(!count($groupings)) return array();

		$list = array();
		foreach($groupings as $grouping){
			$list[] = $grouping->getGroupId();
		}

		return $list;
	}

	/**
	 * 各項目ごとに、createAdd()を行う。
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}
	function hasError($param){}

	/**
	 * @param MyPageLogic || CartLogic $app
	 */
	function confirm($app){
		// 公開側でグループ登録の予定はない
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		//管理画面側での登録処理
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			//最初にすべて削除
			try{
				self::groupingDao()->deleteByUserId($userId);
			}catch(Exception $e){
				//
			}

			if(isset($_POST["user_group_plugin"])){
				foreach($_POST["user_group_plugin"] as $groupId){
					$groupingObj = new SOYShop_UserGrouping();
					$groupingObj->setUserId($userId);
					$groupingObj->setGroupId($groupId);
					try{
						self::groupingDao()->insert($groupingObj);
					}catch(Exception $e){
						//
					}
				}
			}

		//公開側での登録処理
		}else{
			// なし
		}
	}

	private function groupDao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::imports("module.plugins.user_group.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
		}
		return $dao;
	}

	private function groupingDao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::imports("module.plugins.user_group.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_UserGroupingDAO");
		}
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.user.customfield","user_group","UserGroupCustomField");
