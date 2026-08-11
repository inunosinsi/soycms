<?php

class AutoCompletionDownload extends SOYShopDownload{

	const TYPE_CATEGORY = 0;
	const TYPE_ITEM = 1;

	private $q;

	function execute(){
		//if(!isset($_POST["q"])) $_POST["q"] = "さ";	//debug
		if(!isset($_POST["q"])) self::_send();;
		$q = htmlspecialchars(trim($_POST["q"]), ENT_QUOTES, "UTF-8");
		if(!strlen($q)) self::_send();

		$this->q = $q;

		$list = self::_read();
		if(is_null($list)){
			$list = array(self::TYPE_CATEGORY => array(), self::TYPE_ITEM => array());

			SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
			$cnf = AutoCompletionUtil::getConfig();
			$lim = (isset($cnf["count"]) && is_numeric($cnf["count"]))? (int)$cnf["count"] : 10;

			//カテゴリ名検索
			if(isset($cnf["include_category"]) && $cnf["include_category"] == AutoCompletionUtil::INCLUDE_CATEGORY){
				$list[self::TYPE_CATEGORY] = self::_execute(self::TYPE_CATEGORY, $lim);
			}

			//商品名検索
			$list[self::TYPE_ITEM] = self::_execute(self::TYPE_ITEM, ($lim - self::_count($list)));
			self::_save($list);	//検索結果を記録
		}

		if(!self::_count($list)) self::_send(array());

		$results = array();
		foreach($list as $type => $ids){
			if(!count($ids)) continue;

			$arr = self::_getNames($type, $ids);
			if(!count($arr)) continue;

			foreach($arr as $hash => $values){
				$results[$hash] = $values;
			}
		}

		self::_send($results);
	}

	private function _execute(int $mode, int $lim){
		$arr = array();
		$sql = self::_sql($mode);
		for($i = 0; $i <= 2; $i++){
			$customSql = $sql;
			if(count($arr)){	//既に得られた結果は除く
				$customSql = "AND id NOT IN (" . implode(",", $arr) . ")";
			}

			$bind = self::_bind($i);
			try{
				$res = self::_catDao()->executeQuery($customSql . "LIMIT " . ($lim - count($arr)), array(":name" => $bind, ":hiragana" => $bind, ":katakana" => $bind, ":other" => $bind));
			}catch(Exception $e){
				$res = array();
			}
			if(!count($res)) continue;

			foreach($res as $v){
				if(count($arr) && is_numeric(array_search($v["id"], $arr))) continue;
				$arr[] = $v["id"];
			}

			if(count($arr) >= $lim) break;
		}
		return $arr;
	}

	private function _sql(int $mode=self::TYPE_CATEGORY){
		switch($mode){
			case self::TYPE_CATEGORY:
				return "SELECT id FROM soyshop_category ".
						"WHERE (".
							"category_name LIKE :name ".
							"OR id IN (".
								"SELECT category_id FROM soyshop_auto_complete_dictionary_category ".
								"WHERE hiragana LIKE :hiragana ".
								"OR katakana LIKE :katakana ".
								"OR other LIKE :other".
							") ".
						") ".
						"AND category_is_open = 1 ";
			case self::TYPE_ITEM:
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
						"AND detail_page_id > 0 ".
						"AND item_is_open = 1 ".
						"AND is_disabled = 0 ";

				//親商品のみ取得
				$arrows = array(SOYShop_Item::TYPE_SINGLE, SOYShop_Item::TYPE_GROUP, SOYShop_Item::TYPE_DOWNLOAD, SOYShop_Item::TYPE_DOWNLOAD_GROUP);
				return $sql . "AND item_type IN ('" . implode("','", $arrows) . "') ";
		}
	}

	private function _bind($i = 0){
		switch($i){
			case 0:	//前方一致
				return $this->q . "%";
			case 1:	//一文字目が前方一致
				return mb_substr($this->q, 0, 1) . "%";
			case 2:	//部分一致
				return "%" . $this->q . "%";
		}
	}

	private function _getNames(int $mode, array $ids){
		$arr = array();
		try{
			$res = self::_dao()->executeQuery(self::_nameSql($mode, $ids));
		}catch(Exception $e){
			$res = array();
		}

		foreach($ids as $id){
			foreach($res as $v){
				$hash = self::_hash($mode . $id);
				if(self::_hash($mode . $v["id"]) == $hash){
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
					$arr[$hash] = array("", "", "", "");
					$arr[$hash][0] = (isset($v["item_name"])) ? $v["item_name"] : $v["category_name"];
				}
			}
		}

		//読み方
		try{
			$res = self::_dao()->executeQuery(self::_readingSql($mode, $ids));
		}catch(Exception $e){
			$res = array();
		}

		foreach($ids as $id){
			foreach($res as $v){
				$hash = self::_hash($mode . $id);
				if(!isset($arr[$hash])) $arr[$hash] = array();
				$arr[$hash][1] = $v["hiragana"];
				$arr[$hash][2] = $v["katakana"];
				$arr[$hash][3] = $v["other"];
			}
		}
		return $arr;
	}

	private function _nameSql(int $mode, array $ids){
		switch($mode){
			case self::TYPE_CATEGORY:
				return "SELECT id, category_name FROM soyshop_category WHERE id IN (" . implode(",", $ids) . ")";
			case self::TYPE_ITEM:
				return "SELECT id, item_name FROM soyshop_item WHERE id IN (" . implode(",", $ids) . ") AND detail_page_id > 0";
		}
	}

	private function _readingSql(int $mode, array $ids){
		switch($mode){
			case self::TYPE_CATEGORY:
				return "SELECT * FROM soyshop_auto_complete_dictionary_category WHERE category_id IN (" . implode(",", $ids) . ")";
			case self::TYPE_ITEM:
				return "SELECT * FROM soyshop_auto_complete_dictionary WHERE item_id IN (" . implode(",", $ids) . ")";
		}
	}

	//カテゴリと商品を含めた検索結果
	private function _count(array $list){
		return count($list[self::TYPE_CATEGORY]) + count($list[self::TYPE_ITEM]);
	}

	// JSONを送信
	private function _send(array $arr=array()){
		if(count($arr)){
			$tmps = array();
			foreach($arr as $values){
				if(count($values) < 3){
					for($i = 0; $i < 4; $i++){
						if(!isset($values[$i]) || is_null($values[$i])) $values[$i] = "";
					}
				}
				$tmps[] = $values;
			}
			$arr = $tmps;
			unset($tmps);
		}
		echo json_encode($arr);
		exit;
	}

	// 検索の省力化の為に結果を残しておく
	private function _save(array $ids){
		file_put_contents(self::_cachePath(), json_encode($ids));
	}

	private function _hash(string $str){
		return substr(md5($str), 0, 3);
	}

	private function _read(){
		$filepath = self::_cachePath();
		if(!file_exists($filepath)) return null;

		$c = file_get_contents($filepath);
		return (strlen($c)) ? json_decode($c) : array(self::TYPE_CATEGORY => array(), self::TYPE_ITEM => array());
	}

	private function _cachePath(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/autocomplete/";
		if(!file_exists($dir)) mkdir($dir);

		$hash = substr(md5($this->q), 0, 18);
		for($i = 0; $i < 12; $i++){
			$dir .= substr($hash, 0, 1) . "/";
			if(!file_exists($dir)) mkdir($dir);
			$hash = substr($hash, 1);
		}
		$dir .= $hash . ".log";

		return $dir;
	}

	private function _catDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		return $dao;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.download", "auto_completion_item_name", "AutoCompletionDownload");
