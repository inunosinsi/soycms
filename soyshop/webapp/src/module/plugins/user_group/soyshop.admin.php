<?php
class UserGroupCustomSearchFieldAdmin extends SOYShopAdminBase{

	function execute(){
		//現在登録されている最新の商品IDを取得する
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT group_id FROM soyshop_user_group_custom_search ORDER BY group_id DESC LIMIT 1;", array());
		}catch(Exception $e){
			$res = array();
		}

		$lastGroupId = (isset($res[0]["group_id"])) ? (int)$res[0]["group_id"] : 0;

		//最新の商品IDよりも上のIDがあるか調べる
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_group WHERE id > :groupId;", array(":groupId" => $lastGroupId));
		}catch(Exception $e){
			return;
		}

		if(count($res)){
			foreach($res as $v){
				$sql = "INSERT INTO soyshop_user_group_custom_search (group_id) VALUES (" . $v["id"] . ")";
				try{
					$dao->executeQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.admin", "user_group", "UserGroupCustomSearchFieldAdmin");
