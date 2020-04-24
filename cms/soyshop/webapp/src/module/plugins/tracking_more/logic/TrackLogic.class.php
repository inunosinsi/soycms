<?php

class TrackLogic extends SOY2LogicBase {

	private $forcedStop = false;	//強制終了
	private $config;
	private $track;	//TrackingMoreで用意されたクラス

	function __construct(){
		SOY2::import("module.plugins.tracking_more.util.TrackingMoreUtil");
		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");
		SOY2::import("util.SOYShopPluginUtil");
		$this->config = TrackingMoreUtil::getConfig();
		include_once(dirname(dirname(__FILE__)) . "/api/track.class.php");
		$this->track = new Trackingmore(trim($this->config["key"]));
	}

	function searchAll(){
		if(!SOYShopPluginUtil::checkIsActive("slip_number")) return;
		if(!isset($this->config["key"]) || !strlen($this->config["key"])) return;

		$list = self::getSlipNumberList();
		if(!count($list)) return;

		foreach($list as $slipNumber){
			if($this->forcedStop) break;	//エラーがあった場合は強制終了
			if(self::searchStatus($slipNumber)){
				self::changeStatus($slipNumber);
			}
		}
	}

	function searchStatus($trackNo){
		$career = "taqbin-jp";	//クロネコヤマトのこと @ToDo いずれは他の配送業者でも確認できるようにしたい
		$result = $this->track->getRealtimeTrackingResults($career, $trackNo);
		//$result = array();	//止めておく
		//include_once(dirname(dirname(__FILE__)) . "/sample/sample.php");
		sleep(1);	//1秒待つ	これで過アクセスを防ぐ

		if(!isset($result["meta"]["code"]) || $result["meta"]["code"] != 200) {
			error_log("TrackingMore ErrorCode:" . $result["meta"]["code"] . " " . $result["meta"]["message"]);
			$this->forcedStop = true;	//強制終了
			return false;
		}

		$items = $result["data"]["items"];
		if(!count($items)) return false;

		$item = array_shift($items);
		if(!isset($item["origin_info"]["trackinfo"]) || is_null($item["origin_info"]["trackinfo"])) return false;

		$infos = $item["origin_info"]["trackinfo"];
		return (count($infos));	//trackinginfoがあれば発送済みとする
	}

	private function getSlipNumberList(){
		if(!isset($this->config["try"]) || !is_numeric($this->config["try"])) return array();
		$tryCnt = (int)$this->config["try"];	//一回あたりのトライ件数

		$dao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");

		//出荷予定日のデータがあれば、出荷予定日の近いもの順に出す
		if(self::useShippingDate()){
			//出荷予定日の範囲
			$start = (isset($this->config["start"])) ? (int)$this->config["start"] : 1;
			$end = (isset($this->config["end"])) ? (int)$this->config["end"] : 3;

			$sql = "SELECT slip_number FROM soyshop_slip_number ".
					"WHERE is_delivery = " . SOYShop_SlipNumber::NO_DELIVERY . " ".
					"AND order_id IN (".
						"SELECT order_id FROM soyshop_order_date_attribute ".
						"WHERE order_field_id = 'shipping_date' " .
						"AND order_value_1 > " . (time() - $start * 24 * 60 * 60) . " ".
						"AND order_value_1 < " . (time() + $end * 24 * 60 * 60) . " ".
						"ORDER BY order_value_1 ASC ".
					") ".
					"LIMIT " . ($tryCnt * 5);
		}else{
			$sql = "SELECT slip_number FROM soyshop_slip_number ".
					"WHERE is_delivery = " . SOYShop_SlipNumber::NO_DELIVERY . " ".
					"LIMIT " . ($tryCnt * 3);
		}

		try{
			$res = $dao->executeQuery($sql);
			if(!count($res)) return array();
		}catch(Exception $e){
			return array();
		}

		$list = array();
		foreach($res as $v){
			if(!isset($v["slip_number"])) continue;
			$list[] = $v["slip_number"];
		}
		unset($res);

		//配列をシャッフルする
		shuffle($list);

		//トライ回数よりも多い場合は削る
		if(count($list) > $tryCnt){
			$list = array_slice($list, 0, $tryCnt);
		}

		return $list;
	}

	//webhook用に伝票番号を登録する
	function registSlipNumbers(){
		$career = "taqbin-jp";	//クロネコヤマトのこと @ToDo いずれは他の配送業者でも確認できるようにしたい
		$list = self::getSlipNumberListForRegstration();
		if(!count($list)) return;

		$items = array();
		foreach($list as $v){
			if(strpos($v["slip_number"], "test") !== false) continue;

			$items[] = array(
				"tracking_number"	=> $v["slip_number"],
				"carrier_code"		=> $career,
				"title"				=> "宅配便",
				"customer_name"		=> $v["name"],
				"customer_email"	=> $v["mail_address"],
				"order_id"			=> $v["tracking_number"]
			);
		}

		if(!count($items)) return;

		$this->track->createMultipleTracking($items);

		//実行した日時を記録しておく
		SOYShop_DataSets::put("tracking_more.exec_time", time());
	}

	private function getSlipNumberListForRegstration(){
		$dao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");

		$lastExecTime = SOYShop_DataSets::get("tracking_more.exec_time", 0);

		// @ToDo 最後に登録したものよりも後の伝票番号
		$sql = "SELECT slip.slip_number, o.tracking_number, u.name, u.mail_address FROM soyshop_slip_number slip ".
				"INNER JOIN soyshop_order o ".
				"ON slip.order_id = o.id ".
				"INNER JOIN soyshop_user u ".
				"ON o.user_id = u.id ".
				"WHERE slip.is_delivery = " . SOYShop_SlipNumber::NO_DELIVERY . " ".
				"AND slip.create_date > " . $lastExecTime;

		try{
			return $dao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
	}

	//webhookで受信する
	function receiveByWebHook($json=null){
		//開発用 /ショップID/json/trackingmore/sample.phpを用意する
		if(is_null($json)) $json = file_get_contents(SOYSHOP_SITE_DIRECTORY . "/json/trackingmore/sample.json");
		$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$result = json_decode($json, true);
		if(!isset($result["meta"]["code"]) || !isset($result["data"])) {
			error_log("TrackingMore Fatal Error");
			return false;
		}
		if($result["meta"]["code"] != 200) {
			error_log("TrackingMore ErrorCode:" . $result["meta"]["code"] . " " . $result["meta"]["message"]);
			return false;
		}

		$trackNo = htmlspecialchars($result["data"]["tracking_number"], ENT_QUOTES, "UTF-8");
		$status = htmlspecialchars($result["data"]["status"], ENT_QUOTES, "UTF-8");

		switch($status){
			case "transit":
			case "pickup":
			case "delivered":
				self::changeStatus($trackNo);
				//Trackingmoreで追跡から外す
				$this->track->deleteTrackingItem("taqbin-jp", $trackNo);
				break;
			default:
				//何もしない
		}
	}

	function useShippingDate(){
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery("SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = 'shipping_date' LIMIT 1");
			return (isset($res[0]["order_id"]));
		}catch(Exception $e){
			return false;
		}
	}

	//発送済みであれば、伝票番号プラグインと連携して発送済みにする
	private function changeStatus($trackNo){
		self::slipLogic()->changeStatus($trackNo);
	}

	private function slipLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}
