<?php
SOY2::imports("module.plugins.download_assistant.domain.*");
SOY2::import("module.plugins.bonus_download.util.BonusDownloadConfigUtil");
SOY2::import("module.plugins.bonus_download.util.BonusDownloadConditionUtil");
class BonusDownloadRegisterLogic {

	/**
	 * @param SOYShop_Order $order
	 * @param integer $userId
	 */
	function register(SOYShop_Order $order){

		if(is_null($order->getId()))return;

		$config = BonusDownloadConfigUtil::getConfig();

		//購入特典条件に当てはまらない場合は何もしない。
		if(!BonusDownloadConditionUtil::hasBonusByOrder($order))return;

		//ダウンロードファイル形式の場合。
		if($config["type"] == BonusDownloadConfigUtil::TYPE_FILE){

			$files = BonusDownloadConfigUtil::getBonusFiles($config["download_files"]);
			$filenames = array();
			foreach($files as $file){
				$filenames[] = $file["name"];
			}


			$limit = BonusDownloadConfigUtil::getTimelimit($config);
			$name = "ボーナス[購入特典][". $config["name"]. "]";


			//トークンの生成
			$list = array();
			foreach($filenames as $key => $filename){
				$token = BonusDownloadConfigUtil::generateToken($order->getId(), $order->getUserId(), $key);
				$list[] = $token;

				$filenameId = "bonus_download.filename." . $token;
				$filenameName = $name. "[ファイル名]";
				$order = BonusDownloadConfigUtil::setOrderAttribute($order, $filenameId, $filenameName, $filename, true);

				$timelimitId = "bonus_download.timelimit." . $token;
				$timelimitName = $name. "[有効期限]";
				$order = BonusDownloadConfigUtil::setOrderAttribute($order, $timelimitId, $timelimitName, $limit, true);
			}

			//トークンリスト
			$order = BonusDownloadConfigUtil::setOrderAttribute($order, "bonus_download.list", "ボーナス[購入特典]リスト", implode("\n", $list), true);

			//URLリスト
			$urls = BonusDownloadConfigUtil::generateDownloadUrls($order);
			$order = BonusDownloadConfigUtil::setOrderAttribute($order, "bonus_download.url_list", "ボーナス[購入特典]URL", implode("\n", $urls), true);

			$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

			try{
				$orderDao->update($order);
			}catch(Exception $e){

			}
		}

		//URL指定の場合
		if($config["type"] == BonusDownloadConfigUtil::TYPE_TEXT){
			$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

			//URLリスト
			$urls = BonusDownloadConfigUtil::generateDownloadUrls($order);
			$order = BonusDownloadConfigUtil::setOrderAttribute($order, "bonus_download.url_list", "ボーナス[購入特典]URL", implode("\n", $urls));

			try{
				$orderDao->update($order);
			}catch(Exception $e){
				//
			}
		}
	}


	/**
	 * すでに購入特典がある場合
	 * @param SOYShop_Order $order
	 * @return boolean 登録されていなければtrue
	 */
	function checkRegister(SOYShop_Order $order){
		$list = $order->getAttribute("bonus_download.list");

		//公開状態を見る
		$config = BonusDownloadConfigUtil::getConfig();
		if($config["status"] != BonusDownloadConfigUtil::STATUS_ACTIVE) return false;

		//すでに登録されている場合
		if(!is_null($list)) return false;

		//@TODO 購入特典条件に合致しない場合
		if(false) return true;

		return true;
	}
}
