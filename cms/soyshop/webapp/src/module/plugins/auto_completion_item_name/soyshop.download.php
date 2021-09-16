<?php

class AutoCompletionDownload extends SOYShopDownload{

	function execute(){
		if(!isset($_POST["q"])) $_POST["q"] = "ぜ";	//debug
		if(!isset($_POST["q"])) self::_send();;
		$q = htmlspecialchars(trim($_POST["q"]), ENT_QUOTES, "UTF-8");
		if(!strlen($q)) self::_send();

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		$list = self::_read($q);
		if(is_null($list)){
			$list = array();

			SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
			$cnf = AutoCompletionUtil::getConfig();
			$lim = (isset($cnf["count"]) && is_numeric($cnf["count"]))? (int)$cnf["count"] : 10;


			$now = time();
			$sql = "SELECT id FROM soyshop_item ".
					"WHERE (".
						"item_name LIKE :name ".
						"OR id IN (".
							"SELECT item_id FROM soyshop_auto_complete_dictionary ".
							"WHERE hiragana LIKE :hiragana ".
							"OR katakana LIKE :katakana ".
							"OR other LIKE :other".
						") ".
					") ".
					"AND order_period_start < " . $now . " ".
					"AND order_period_end > " . $now . " ".
					"AND open_period_start < " . $now . " ".
					"AND open_period_end > " . $now . " ".
					"AND item_is_open = 1 ".
					"AND is_disabled = 0 ";

			//親商品のみ取得
			$arrows = array(SOYShop_Item::TYPE_SINGLE, SOYShop_Item::TYPE_GROUP, SOYShop_Item::TYPE_DOWNLOAD, SOYShop_Item::TYPE_DOWNLOAD_GROUP);
			$sql .= "AND item_type IN ('" . implode("','", $arrows) . "') ";

			for($i = 0; $i <= 2; $i++){
				switch($i){
					case 0:	//前方一致
						$bind = $q . "%";
						break;
					case 1:	//一文字目が前方一致
						$fQ = mb_substr($q, 0, 1);
						$bind = $fQ . "%";
						break;
					case 2:	//部分一致
						$bind = "%" . $q . "%";
						break;
				}

				$customSql = $sql;
				if(count($list)){	//既に得られた結果は除く
					$customSql = "AND id NOT IN ('" . implode("','", $list) . "') ";
				}

				try{
					$res = $dao->executeQuery($customSql . "LIMIT " . ($lim - count($list)), array(":name" => $bind, ":hiragana" => $bind, ":katakana" => $bind, ":other" => $bind));
				}catch(Exception $e){
					$res = array();
				}
				if(!count($res)) continue;

				foreach($res as $v){
					if(count($list) && is_numeric(array_search($v["id"], $list))) continue;
					$list[] = $v["id"];
				}

				if(count($list) >= $lim) break;

				self::_save($q, $list);	//検索結果を記録
			}
		}

		if(!count($list)) self::_send(array());

		$results = array();

		$sql = "SELECT id, item_name FROM soyshop_item WHERE id IN (" . implode(",", $list) . ")";
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		foreach($list as $id){
			foreach($res as $v){
				if(!isset($results[$id])) $results[$id] = array("", "", "");
				if($v["id"] == $id){
					// $isDuplicate = false;
					//
					// //商品名の重複を除く
					// if(count($results)){
					// 	foreach($results as $result){
					// 		if(!$isDuplicate && strlen($result[0]) && $result[0] == $v["item_name"]) $isDuplicate = true;
					// 	}
					// }
					//
					// if(!$isDuplicate) $results[$id][0] = $v["item_name"];
					$results[$id][0] = $v["item_name"];
				}
			}
		}

		//読み方
		$sql = "SELECT * FROM soyshop_auto_complete_dictionary WHERE item_id IN (" . implode(",", $list) . ")";
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		unset($dao);

		foreach($list as $id){
			foreach($res as $v){
				$results[$id][1] = $v["hiragana"];
				$results[$id][2] = $v["katakana"];
				$results[$id][3] = $v["other"];
			}
		}

		self::_send($results);
	}



	// JSONを送信
	private function _send(array $arr=array()){
		echo json_encode($arr);
		exit;
	}

	// 検索の省力化の為に結果を残しておく
	private function _save(string $q, array $ids){
		file_put_contents(self::_cachePath($q), implode(",", $ids));
	}

	private function _read(string $q){
		$filepath = self::_cachePath($q);
		if(!file_exists($filepath)) return null;

		$c = file_get_contents($filepath);
		return (strlen($c)) ? explode(",", $c) : array();
	}

	private function _cachePath(string $q){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/autocomplete/";
		if(!file_exists($dir)) mkdir($dir);

		$hash = substr(md5($q), 0, 18);
		for($i = 0; $i < 12; $i++){
			$dir .= substr($hash, 0, 1) . "/";
			if(!file_exists($dir)) mkdir($dir);
			$hash = substr($hash, 1);
		}
		$dir .= $hash . ".log";

		return $dir;
	}
}
SOYShopPlugin::extension("soyshop.download", "auto_completion_item_name", "AutoCompletionDownload");
