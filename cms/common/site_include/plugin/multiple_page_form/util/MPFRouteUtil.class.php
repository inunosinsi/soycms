<?php

class MPFRouteUtil {

	/**
	 * データをセッションに入れる方法は
	 * array(
	 *	hash => array(
	 *		array(label, value),
	 * 		...
	 *	)
	 * )
	 * とする
	 */

	public static function doPost(){
		//soy2_tokenがある場合は次のページを調べてリダイレクト GET版
		$param = (isset($_POST["soy2_token"])) ? $_POST : $_GET;
		if(isset($param["soy2_token"]) && soy2_check_token() && soy2_check_referer()){
			//値を記録しておきたい
			$hash = self::_getPageHash();

			SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
			$cnf = MultiplePageFormUtil::readJson($hash);
			if(!count($cnf)){
				$basisHash = self::getBasisHashByRepeatHash($hash);
				$cnf = MultiplePageFormUtil::readJson($basisHash);
			}
			//var_dump($cnf);exit;

			$values = self::_getValues();

			$next = null;
			switch($cnf["type"]){
				case MultiplePageFormUtil::TYPE_TEXT:
					if(isset($param["next"])) $next = $cnf["next"];
				 	break;
				case MultiplePageFormUtil::TYPE_CHOICE:
				case MultiplePageFormUtil::TYPE_CONFIRM_CHOICE:
					if(isset($param["idx"]) && isset($cnf["choice"][$param["idx"]])){	//何らを選択している場合
						$selected = $cnf["choice"][$param["idx"]];
						if(isset($cnf["label"]) && strlen($cnf["label"])){
							$values[$hash] = array(array("label" => $cnf["label"], "value" => $selected["item"]));
							self::_save($values);
						}

						$next = trim($selected["next"]);
					}
					break;
				case MultiplePageFormUtil::TYPE_FORM:
					if(isset($param["MPF"])){
						SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeFormUtil");
						$items = $cnf["item"];
						foreach($items as $idx => $item){
							$v = "";
							if(isset($param["MPF"]["form_" . $idx])){
								if($item["type"] == MPFTypeFormUtil::TYPE_CHECKBOX){
									$v = (is_array($param["MPF"]["form_" . $idx])) ? implode(",", $param["MPF"]["form_" . $idx]) : "";
								}else{
									$v = trim($param["MPF"]["form_" . $idx]);
								}
							}
							$values[$hash][$idx] = array("label" => $item["name"], "value" => $v);
						}
					}
					self::_save($values);

					if(isset($param["next"])) $next = $cnf["next"];

					break;
				case MultiplePageFormUtil::TYPE_EXTEND:
					SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeExtendUtil");
					if(!isset($cnf["extend"]) || !strlen($cnf["extend"])) break;

					$classFilePath = MPFTypeExtendUtil::getPageDir() . $cnf["extend"] . ".class.php";
					if(!file_exists($classFilePath)) break;

					include_once($classFilePath);
					$form = SOY2HTMLFactory::createInstance($cnf["extend"]);
					$form->setHash($hash);
					$form->doPost();

					if(isset($param["next"])) $next = $cnf["next"];

					break;
				case MultiplePageFormUtil::TYPE_CONFIRM:
					// SOY Inqiuryと連携している場合はデータベースに値を格納する
					if(isset($param["next"])){
						SOY2::import("site_include.plugin.multiple_page_form.util.SOYInquiryConnectUtil");
						$conCnf = SOYInquiryConnectUtil::getConfig();
						if(isset($conCnf["form_id"]) && strlen($conCnf["form_id"])){
							SOYInquiryConnectUtil::connect($conCnf["form_id"]);
						}
						$next = $cnf["next"];
					}
					break;
			}

			// routeを記録
			if(isset($next) && strlen($next)){
				//同じページを繰り返す場合はrepeatハッシュを設けて、別ページとして認識させる
				$next = self::_repeatHash($next);

				self::_saveRoute($next);
			}else{	//戻る
				self::_backRoute();
			}

			header("Location:" . $_SERVER["REDIRECT_URL"]);	//とりあえずGETパラメータ付きのページは禁止
			exit;
		}
	}

	public static function getValues($hash){
		$values = self::_getValues();
		return isset($values[$hash]) ? $values[$hash] : array();
	}

	public static function save($hash, $v){
		$values = self::_getValues();
		$values[$hash] = $v;
		self::_save($values);
	}

	//確認用
	public static function getAllPageValues(){
		$values = self::_getValues();
		$route = self::_get("mpf_route");

		$list = array();
		foreach($route as $hash){
			if(!isset($values[$hash])) continue;
			foreach($values[$hash] as $v){
				//$v["value"] = nl2br(htmlspecialchars($v["value"], ENT_QUOTES, "UTF-8"));
				$list[] = $v;
			}
		}
		return $list;
	}

	//すべてのルートからメールアドレスらしき値を探す
	public static function getMailAddressOnAllRoute(){
		$route = self::_get("mpf_route");
		if(!is_array($route) || !count($route)) return null;

		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeFormUtil");
		foreach($route as $hash){
			$cnf = MultiplePageFormUtil::readJson($hash);
			if(!isset($cnf["type"]) || $cnf["type"] != MultiplePageFormUtil::TYPE_FORM) continue;
			if(!isset($cnf["item"]) || !is_array($cnf["item"]) || !count($cnf["item"])) continue;

			$values = self::getValues($hash);
			foreach($cnf["item"] as $idx => $item){
				if(!isset($item["required"]) || (int)$item["required"] != 1) continue;
				if(!isset($item["type"]) || $item["type"] != MPFTypeFormUtil::TYPE_EMAIL) continue;
				if(!isset($values[$idx])) continue;

				return $values[$idx]["value"];
			}
		}
		return null;
	}

	//置換文字列リスト
	public static function getReplacementStringList(){
		$route = self::_get("mpf_route");
		if(!is_array($route) || !count($route)) return array();

		$list = array();

		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeFormUtil");
		foreach($route as $hash){
			$cnf = MultiplePageFormUtil::readJson($hash);
			if(!isset($cnf["type"]) || $cnf["type"] != MultiplePageFormUtil::TYPE_FORM) continue;
			if(!isset($cnf["item"]) || !is_array($cnf["item"]) || !count($cnf["item"])) continue;

			$values = self::getValues($hash);
			foreach($cnf["item"] as $idx => $item){
				if(!isset($item["replacement"]) || !strlen($item["replacement"])) continue;
				if(!isset($values[$idx]["value"])) continue;
				$list[trim($item["replacement"])] = $values[$idx]["value"];
			}
		}
		return $list;
	}

	public static function clear(){
		self::_set("mpf_route", null);
		self::_set("mpf_values", null);
	}

	public static function getPageHash(){
		return self::_getPageHash();
	}

	public static function getPrevPageHash(){
		$route = self::_get("mpf_route");
		if(is_null($route) || count($route) < 2) return null;

		$last = count($route) - 2;
		return $route[$last];
	}

	//リピートハッシュの基のページのハッシュを探す
	public static function getBasisHashByRepeatHash($hash){
		return self::_getBasisHashByRepeatHash($hash);
	}

	//任意のハッシュを含むレピートハッシュテーブルを返す
	public static function getRepeatHashListByHash($hash){
		$table = self::_repeatHashTable();
		if(!count($table)) return array();

		//繰り返し一ページ目
		if(isset($table[$hash])){
			return self::_createHashList($hash);

		//繰り返しの中に含まれている場合
		}else{
			$targetHash = null;
			foreach($table as $basisHash => $repeatHashes){
				if(count($repeatHashes)){
					foreach($repeatHashes as $repeatHash){
						if($hash == $repeatHash){
							return self::_createHashList($basisHash);
						}
					}
				}
			}
		}

		return array();
	}

	/** private method **/
	private static function _getValues(){
		$values = self::_get("mpf_values");
		if(is_null($values)) $values = array();
		return $values;
	}

	private static function _save($values){
		self::_set("mpf_values", $values);
	}

	private static function _getPageHash(){
		$route = self::_get("mpf_route");
		if(is_null($route) || (is_array($route) && !count($route))) {
			$route = array();
			$route[] = self::_getFirstPageHash();
			self::_set("mpf_route", $route);
		}

		//一番最後の値を取得
		$last = count($route) - 1;

		//リピートハッシュテーブル
		$table = self::_repeatHashTable();

		//ハッシュが存在しているか？調べる
		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		$jsonDir = MultiplePageFormUtil::jsonDir();
		for(;;){
			if($last < 0) break;
			$hash = $route[$last--];
			if(file_exists($jsonDir . $hash . ".json")) return $hash;

			//リピートハッシュを探す→あればリピートハッシュを返す
			$basisHash = self::_getBasisHashByRepeatHash($hash);
			if(isset($basisHash)) return $hash;
		}

		//最初のページまで戻す
		$route = array();
		$route[] = self::_getFirstPageHash();
		self::_set("mpf_route", $route);
	}

	private static function _getBasisHashByRepeatHash($hash){
		//リピートハッシュテーブル
		$table = self::_repeatHashTable();
		foreach($table as $basisHash => $repeatHashTable){
			foreach($repeatHashTable as $repeatHash){
				if($hash == $repeatHash){	//繰り返しページであることがわかればハッシュを返す
					return $basisHash;
				}
			}
		}
		return null;
	}

	//今まで辿ってきたルートの中に同じハッシュがある場合は繰り返しハッシュを設ける
	private static function _repeatHash($hash){
		$route = self::_get("mpf_route");
		if(is_null($route) || !is_array($route) || !count($route)) return $hash;

		$flg = false;
		foreach($route as $prevHash){
			if($hash == $prevHash) $flg = true;
		}

		//同じハッシュがなかったのでそのまま返す
		if(!$flg) return $hash;

		//以前戻るボタンを押した時のバックアップがある場合はリピートテーブルから調べてみる
		$backupHash = self::_get("mpf_route_next_hash");
		if(strlen($backupHash)){
			$table = self::_repeatHashTable();
			if(isset($table[$hash])){
				$idx = array_search($backupHash, $table[$hash]);
				if(is_numeric($idx)) {
					self::_set("mpf_route_next_hash", null);
					return $table[$hash][$idx];
				}
			}
		}

		//繰り返してきたページを調べて、何回目の繰り返しかを調べる
		$hashList = self::_createHashList($hash);
		if(is_array($hashList) && count($hashList)){
			foreach($hashList as $prevHash){
				if(is_numeric(array_search($prevHash, $route)))continue;
				return $prevHash;
			}
		}

		//repeatHashの生成
		return self::_generateRepeatHash($hash);
	}

	private static function _generateRepeatHash($hash){
		$table = self::_repeatHashTable();
		if(!isset($table[$hash])) $table[$hash] = array();

		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		$repeatHash = MultiplePageFormUtil::createHash();

		$table[$hash][] = $repeatHash;
		self::_set("mpf_repeat_hash_table", $table);
		return $repeatHash;
	}

	private static function _repeatHashTable(){
		$table = self::_get("mpf_repeat_hash_table");
		if(is_null($table)) $table = array();
		return $table;
	}

	private static function _createHashList($hash){
		$table = self::_repeatHashTable();
		$list = array();
		$list[] = $hash;
		$list = array_merge($list, $table[$hash]);
		return $list;
	}

	private static function _saveRoute($hash){
		$route = self::_get("mpf_route");
		if(is_null($route)) {
			$route = array();
			$route[] = self::_getFirstPageHash();
		}
		$route[] = $hash;
		self::_set("mpf_route", $route);
	}

	private static function _backRoute(){
		$route = self::_get("mpf_route");
		if(count($route) > 0){
			$next = array_pop($route);
			self::_set("mpf_route_next_hash", $next);	//戻るを押した時に今いたページのバックアップをとっておく
		}
		self::_set("mpf_route", $route);
	}

	private static function _getFirstPageHash(){
		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		$pages = MultiplePageFormUtil::getPageList();
		if(!count($pages)) return "error";

		foreach($pages as $hash => $page){
			return $hash;
		}
		return "error";
	}

	private static function _get($key){
		static $session;
		if(is_null($session)) $session = SOY2ActionSession::getUserSession();
		return $session->getAttribute($key);
	}

	private static function _set($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute($key, $value);
	}
}
