<?php
class CustomSearchFieldAdmin extends SOYShopAdminBase{

	function execute(){
		//現在登録されている最新の商品IDを取得する
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT item_id FROM soyshop_custom_search ORDER BY item_id DESC LIMIT 1;", array());
		}catch(Exception $e){
			$res = array();
		}
		
		$lastItemId = (isset($res[0]["item_id"])) ? (int)$res[0]["item_id"] : 0;
		
		//最新の商品IDよりも上のIDがあるか調べる
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_item WHERE id > :itemId;", array(":itemId" => $lastItemId));
		}catch(Exception $e){
			return;
		}
		
		if(count($res)){
			foreach($res as $v){
				$sql = "INSERT INTO soyshop_custom_search (item_id) VALUES (" . $v["id"] . ")";
				try{
					$dao->executeQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.admin", "custom_search_field", "CustomSearchFieldAdmin");
?>