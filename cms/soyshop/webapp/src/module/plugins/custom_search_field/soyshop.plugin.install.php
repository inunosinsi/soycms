<?php
class CustomSearchFieldInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){

		//初期化時のみテーブルを作成する
		$sql = $this->getSQL();
		$dao = new SOY2DAO();
		
		try{
			$dao->executeQuery($sql);
		}catch(Exception $e){
			//
		}
		
		try{
			$res = $dao->executeQuery("SELECT * FROM soyshop_custom_search");
		}catch(Exception $e){
			return;
		}
		
		if(!count($res)){
			//実行後、商品IDを全て登録する
			try{
				$items = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->get();
			}catch(Exception $e){
				return;
			}
			
			
			foreach($items as $item){
				$sql = "INSERT INTO soyshop_custom_search (item_id) VALUES (" . $item->getId() . ")";
				try{
					$dao->executeQuery($sql);
				}catch(Exception $e){
					//
				}
			}
		}
		
			
	}
	
	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}
		
	/**
	 * @return String sql for init
	 */
	function getSQL(){
		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "custom_search_field", "CustomSearchFieldInstall");
?>