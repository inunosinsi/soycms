<?php
class CustomSearchFieldInstall extends SOYShopPluginInstallerBase{

	function onInstall(){

		//初期化時のみテーブルを作成する
		$sqls = self::_sqls();
        $dao = new SOY2DAO();

        if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
            if(count($tmp[0])){
                foreach($tmp[0] as $sql){
                    try{
                        $dao->executeQuery(trim($sql));
                    }catch(Exception $e){
                        //データベースが存在する場合はスルー
                    }
                }
            }
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
	private function _sqls(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "custom_search_field", "CustomSearchFieldInstall");
