<?php

class MecabReadingLogic extends SOY2LogicBase {

	const LIMIT = 50;

	//読み方が未取得の商品ID一覧を取得
	function getUnacquiredReadingItemIds(){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery(
				"SELECT id ".
				"FROM soyshop_item ".
				"WHERE id NOT IN (".
					"SELECT item_id ".
					"FROM soyshop_auto_complete_dictionary".
				") ".
				"AND is_disabled = 0 "
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = (int)$v["id"];
		}
		return $list;
	}

	//商品ごとの読み方を取得する
	function setReadingEachItems(){
		$ids = self::getUnacquiredReadingItemIds();
		$cnt = count($ids);

		if($cnt === 0) return;

		//50件ずつにする
		if($cnt > self::LIMIT){
			$ids = array_slice($ids, self::LIMIT);
		}

		SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryDAO");
		$dao = SOY2DAOFactory::create("SOYShop_AutoComplete_DictionaryDAO");
		foreach($ids as $id){
			list($hiragana, $katakana) = self::_getReadingByItemId($id);
			$dic = new SOYShop_AutoComplete_Dictionary();
			$dic->setItemId($id);
			$dic->setHiragana($hiragana);
			$dic->setKatakana($katakana);
			try{
				$dao->insert($dic);
				self::_saveLog($dic);
			}catch(Exception $e){
				//
			}
		}
	}

	private function _getReadingByItemId(int $itemId){
		$name = soyshop_get_item_object($itemId)->getName();
		if(!strlen($name)) return array("", "");
		exec("echo " . $name . " | mecab", $res);
		if(!is_array($res) || count($res) === 0) return array("", "");
		$katakana = "";
		foreach($res as $v){
			$arr = explode(",", $v);
			//idx:7にカタカナの読み方がある
			if(count($arr) < 8) continue;
			$katakana .= $arr[7];
		}
		$hiragana = mb_convert_kana($katakana, "c");
		return array($hiragana, $katakana);
	}

	function getLogs(){
		$files = soy2_scandir(self::_dir());
		if(!count($files));
		$list = array();
		foreach($files as $file){
			$list[] = "/" . SOYSHOP_ID . "/.log/autocomplete/" . $file;
		}
		return $list;
	}

	private function _saveLog(SOYShop_AutoComplete_Dictionary $dic){
		$txt = $dic->getItemId() . "," . soyshop_get_item_object($dic->getItemId())->getName() . "," . $dic->getHiragana() . "," . $dic->getKatakana() . "\n";
		file_put_contents(self::_dir() . date("Ymd") . ".log", $txt, FILE_APPEND);
	}

	private function _dir(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".log/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "autocomplete/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}
