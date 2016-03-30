<?php
class DiscountFreeCouponInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){
		//初期化時のみテーブルを作成する
		$sql = $this->getSQL();
		$dao = new SOY2DAO();
		
		$array = preg_split('/create table/', $sql, -1, PREG_SPLIT_NO_EMPTY) ;
		
		foreach($array as $value){
			$sql = "create table " . trim($value);
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				
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
		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE.".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "discount_free_coupon", "DiscountFreeCouponInstall");
?>