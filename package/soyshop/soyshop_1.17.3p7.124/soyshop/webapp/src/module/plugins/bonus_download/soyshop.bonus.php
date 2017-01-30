<?php
class SOYShopBonusDownloadModule extends SOYShopBonus{
	private $config;
	private $dao;
	
	function SOYShopBonusDownloadModule(){
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConfigUtil");
		SOY2::import("module.plugins.bonus_download.util.BonusDownloadConditionUtil");
		SOY2::import("module.plugins.bonus_download.logic.BonusDownloadFileLogic");
	}
	
	/**
	 * 注文処理時
	 * @param string $moduleId
	 */
	function order($moduleId){
		//なにもしない。 soyshop.order.completeでの処理
	}
	
	/**
	 * ボーナス条件を判定して$hasBonus, $name, $contentHtml, $bonusContentに諸々詰める
	 */
	function confirmBonus(){
		$this->config();
	}
	
	function config(){
		$cart = $this->getCart();
		
		$config = BonusDownloadConfigUtil::getConfig();
		
		//公開状態を確認する
		if($config["status"] == BonusDownloadConfigUtil::STATUS_ACTIVE){
			$this->setHasBonus(BonusDownloadConditionUtil::hasBonusByCart($cart));
			$this->setName($config["name"]);
			$this->setHtml($config["html"]);
			$this->setBonusContent($config["download_url"]);
			
			//ダウンロードファイル管理
			if($config["type"] == BonusDownloadConfigUtil::TYPE_FILE){
				
			//URL入力
			}elseif($config["type"] == BonusDownloadConfigUtil::TYPE_TEXT){
				
			}
		}
	}

}
SOYShopPlugin::extension("soyshop.bonus", "bonus_download", "SOYShopBonusDownloadModule");
?>