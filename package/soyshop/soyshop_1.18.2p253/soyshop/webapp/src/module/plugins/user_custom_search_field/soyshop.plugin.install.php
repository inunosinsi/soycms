<?php
class UserCustomSearchFieldInstall extends SOYShopPluginInstallerBase{

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
			$res = $dao->executeQuery("SELECT * FROM soyshop_user_custom_search");
		}catch(Exception $e){
			return;
		}

		if(!count($res)){
			//実行後、商品IDを全て登録する
			try{
				$users = SOY2DAOFactory::create("user.SOYShop_UserDAO")->get();
			}catch(Exception $e){
				return;
			}


			foreach($users as $user){
				$sql = "INSERT INTO soyshop_user_custom_search (user_id) VALUES (" . $user->getId() . ")";
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
SOYShopPlugin::extension("soyshop.plugin.install", "user_custom_search_field", "UserCustomSearchFieldInstall");
