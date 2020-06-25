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
		if(isset($param["soy2_token"]) && soy2_check_token()){
			//値を記録しておきたい
			$hash = self::_getPageHash();

			SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
			$cnf = MultiplePageFormUtil::readJson($hash);

			$values = self::_getValues();

			$next = null;
			switch($cnf["type"]){
				case MultiplePageFormUtil::TYPE_CHOICE:
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
					if(!isset($cnf["extend"]) || !strlen($cnf["extend"])) multiple_page_form_empty_echo();

					$classFilePath = MPFTypeExtendUtil::getPageDir() . $cnf["extend"] . ".class.php";
					if(!file_exists($classFilePath)) multiple_page_form_empty_echo();

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
				// @ToDo 存在していないページハッシュを手動で挿入される可能性もある
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
		return $route[$last];
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
			array_pop($route);
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
