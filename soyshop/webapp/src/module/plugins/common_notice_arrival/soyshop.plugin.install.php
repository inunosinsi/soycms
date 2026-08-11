<?php
class CommonNoticeArrivalInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();

		try{
			$dao->executeQuery(self::_sql());
		}catch(Exception $e){
			//データベースが存在する場合はスルー
		}

		//メール文面を登録
		foreach(array("title", "header", "footer") as $typ){
			$v = SOYShop_DataSets::get("mail.user.arrival." . $typ, null);
			if(is_null($v)) {
				switch($typ){
					case "header":
						$v .= "\n";
						break;
					case "footer":
						$v = "\n" . $v;
						break;
					default:
						//何もしない
				}
				SOYShop_DataSets::put("mail.user.arrival." . $typ, file_get_contents(dirname(__FILE__) . "/mail/" . $typ . ".txt"));
			}
		}
	}

	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}

	/**
	 * @return String sql for init
	 */
	private function _sql(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install","common_notice_arrival","CommonNoticeArrivalInstall");
