<?php
/**
 * ログイン権限があるか
 */
function soyshop_admin_login(){
	$session = SOY2ActionSession::getUserSession();

	//root user
	$root = $session->getAttribute("isdefault");
	if($root)return true;

	//auth level
	$level = soyshop_admin_auth_level();

	return ($level > 0);
}

/**
 * SOY Shopの権限レベルを取得
 */
function soyshop_admin_auth_level(){
	$session = SOY2ActionSession::getUserSession();
	$level = $session->getAttribute("app_shop_auth_level");

	if(is_null($level)){
		return 0;
	}else{
		return true;
	}
}

function print_update_date($time){
	if(!is_numeric($time)) $time = 0;
	if(date("Ymd") == date("Ymd",$time)){
		return date("H:i",$time);
	}

	return date("Y-m-d H:i", $time);
}

/**
 * 変数の文字列を数字に変換して返す。変数の文字列が数字でなかった場合は第二引数の値を返す
 * @param String, Integer
 * @return Integer
 */
function soyshop_convert_number($arg, $value){
	$arg = mb_convert_kana($arg, "a");
	if(strlen($arg) < 1 || !is_numeric($arg)){
		$arg = $value;
	}
	return (int)$arg;
}

/**
 * 文字列の末尾のスラッシュを除く
 * @param String
 * @return String
 */
function soyshop_remove_close_slash($str){

	if(strrpos($str, "/") === strlen($str) - 1){
		$str = rtrim($str, "/");
	}

	return $str;
}

/**
 * 半角カナから全角カナに変換する時の濁点の処理
 */
function soyshop_convert_kana_sonant($kana){
	$list = array(
		"カ゛" => "ガ",
		"キ゛" => "ギ",
		"ク゛" => "グ",
		"ケ゛" => "ゲ",
		"コ゛" => "ゴ",
		"サ゛" => "ザ",
		"シ゛" => "ジ",
		"ス゛" => "ズ",
		"セ゛" => "ゼ",
		"ソ゛" => "ゾ",
		"タ゛" => "ダ",
		"チ゛" => "ヂ",
		"ツ゛" => "ヅ",
		"テ゛" => "デ",
		"ト゛" => "ド",
		"ハ゛" => "バ",
		"ヒ゛" => "ビ",
		"フ゛" => "ブ",
		"ヘ゛" => "ベ",
		"ホ゛" => "ボ",
		"ハ゜" => "パ",
		"ヒ゜" => "ピ",
		"フ゜" => "プ",
		"ヘ゜" => "ペ",
		"ホ゜" => "ポ"
	);

	foreach($list as $k => $c){
		if(strpos($kana, $k) === false) continue;
		$kana = str_replace($k, $c, $kana);
	}
	return $kana;
}

function soyshop_get_category_objects(){
	static $categories;
	if(is_null($categories)){
		try{
			$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}catch(Exception $e){
			$categories = array();
		}
	}
	return $categories;
}

// array(categoryId => categoryName)
function soyshop_get_category_list(bool $isOnlyOpen=false){
	static $list;
	if(is_null($list)){
		$list = array();
		$categories = soyshop_get_category_objects();

		if(count($categories)){
			foreach($categories as $category){
				if($isOnlyOpen && $category->getIsOpen() != SOYShop_Category::IS_OPEN) continue;
				$list[$category->getId()] = $category->getName();
			}
		}
	}

	return $list;
}

// array(user_id...)
function soyshop_get_user_ids_by_orders(array $orders){
	if(!count($orders)) return array();

	$ids = array();
	foreach($orders as $order){
		if(count($ids) && is_numeric(array_search((int)$order->getUserId(), $ids))) continue;
		$ids[] = (int)$order->getUserId();
	}
	return $ids;
}

/**
 * @param string
 */
function soyshop_clear_cache(string $dir=""){
	if(!strlen($dir)) $dir = SOYSHOP_SITE_DIRECTORY . ".cache/";
	$files = soy2_scandir($dir);
	if(!count($files)) return;

	foreach($files as $f){
		if(!file_exists($dir . $f)) continue;
		if(is_dir($dir . $f)){
			soyshop_clear_cache($dir . $f . "/");
			rmdir($dir . $f . "/");
		}else{
			unlink($dir . $f);
		}
	}
}
