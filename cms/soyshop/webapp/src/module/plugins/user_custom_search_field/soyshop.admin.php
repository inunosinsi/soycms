<?php
class UserCustomSearchFieldAdmin extends SOYShopAdminBase{

	function execute(){
		//現在登録されている最新の商品IDを取得する
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT user_id FROM soyshop_user_custom_search ORDER BY user_id DESC LIMIT 1;", array());
		}catch(Exception $e){
			$res = array();
		}
		
		$lastUserId = (isset($res[0]["user_id"])) ? (int)$res[0]["user_id"] : 0;
		
		//最新の商品IDよりも上のIDがあるか調べる
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_user WHERE id > :userId;", array(":userId" => $lastUserId));
		}catch(Exception $e){
			return;
		}
		
		if(count($res)){
			foreach($res as $v){
				$sql = "INSERT INTO soyshop_user_custom_search (user_id) VALUES (" . $v["id"] . ")";
				try{
					$dao->executeQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.admin", "user_custom_search_field", "UserCustomSearchFieldAdmin");
?>