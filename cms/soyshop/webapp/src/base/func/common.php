<?php
/**
 * ページのURLを取得する
 */
function soyshop_get_page_url(string $uri, string $suffix=""){
	SOY2::import("domain.site.SOYShop_Page");
	if($uri == SOYShop_Page::URI_HOME) $uri = "";

	if($suffix) return soyshop_shape_page_url(soyshop_get_site_url(true) . $uri . "/" . $suffix);

	return soyshop_shape_page_url(soyshop_get_site_url(true) . $uri);
}

//URLの小手先の修正集は下記のラッパー関数で行う
function soyshop_shape_page_url(string $url){
	//サイトIDが２つ繋がってしまった時
	if(is_numeric(strpos($url, "/" . SOYSHOP_ID . "//" . SOYSHOP_ID . "/"))){
		$url = str_replace("/" . SOYSHOP_ID . "//" . SOYSHOP_ID . "/", "/" . SOYSHOP_ID . "/", $url);
	}

	return $url;
}

/**
 * サイトのURLを取得する
 */
function soyshop_get_site_url(bool $isAbsolute=false){
	$url = (defined("SOYSHOP_SITE_URL")) ? SOYSHOP_SITE_URL : "shop";

	//portがある場合は$_SERVER["SERVER_PORT"]をチェック

	if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && strpos($url, "http:") >= 0) $url = str_replace("http:", "https:", $url);

	//ルート設定の場合、ショップIDを削る
	if(defined("SOYSHOP_IS_ROOT") && SOYSHOP_IS_ROOT){
		$id = "/" . SOYSHOP_ID . "/";
		$posId = strrpos($url, $id);
		if((strlen($url) - strlen($id)) == $posId){
			$url = substr($url, 0, $posId) . "/";
		}
	}

	return ($isAbsolute) ? $url : preg_replace('/^h[a-z]+:\/\/[^\/]+/', '', $url);
}

//httpsからはじまるURLに変更
function soyshop_get_ssl_site_url(){
	$url = soyshop_get_site_url(true);
	if(is_bool(strpos($url, "https:"))) $url = str_replace("http:", "https:", $url);
	return $url;
}

function soyshop_get_arguments(){
	static $args;
	if(is_array($args)) return $args;
	if(!defined("SOYSHOP_PAGE_ID")) return array();	//SOYSHOP_PAGE_IDが定義されていない場合は以後の処理を無視する。

	$args = array();

	if(!isset($_SERVER["REQUEST_URI"])) return $args;
	//末尾にスラッシュがない場合はスラッシュを付ける
	$uri = trim($_SERVER["REQUEST_URI"], "/");
	if(strpos($uri, SOYSHOP_ID . "/") === 0) $uri = str_replace(SOYSHOP_ID . "/", "", $uri);

	// GETパラメータを外す
	if(is_numeric(strpos($uri, "?"))) $uri = substr($uri, 0, strpos($uri, "?"));
	
	$pageUri = soyshop_get_page_object(SOYSHOP_PAGE_ID)->getUri();
	if(strpos($uri, $pageUri) === 0) $uri = str_replace($pageUri, "", $uri);

	$uri = trim($uri, "/");
	$args = explode("/", $uri);
	unset($pageUri);
	unset($uri);

	return $args;
}

/**
 * httpから始まる画像のフルパスを取得する
 */
function soyshop_get_image_full_path(string $imagePath){
	static $url;
	if(is_null($url)){
		$url = soyshop_get_site_url(true);
		if(strpos($url, "/" . SOYSHOP_ID . "/") !== false){
			$url = str_replace("/" . SOYSHOP_ID . "/", "", $url);
		}
	}
	return $url . $imagePath;
}

/**
 * 画像のディレクトリからのファイルパスを取得する
 */
 function soyshop_get_image_file_path(string $imagePath){
 	static $dir;
 	if(is_null($dir)){
 		$dir = SOYSHOP_SITE_DIRECTORY;
 		if(strpos($dir, "/" . SOYSHOP_ID . "/") !== false){
 			$dir = str_replace("/" . SOYSHOP_ID . "/", "", $dir);
 		}
 	}
 	return $dir . $imagePath;
 }

/**
 * サイトのURIを取得する
 */
function soyshop_get_site_path(){
	$dir = (defined("SOYCMS_TARGET_DIRECTORY")) ? SOYCMS_TARGET_DIRECTORY : $_SERVER["DOCUMENT_ROOT"];
	return str_replace($dir, '/', SOYSHOP_SITE_DIRECTORY);
}

/**
 * 商品一覧のURLを取得する
 */
function soyshop_get_item_list_link(SOYShop_Item $item, SOYShop_Category $category){
	static $results, $isInstalled, $dao, $listUri;

	//カテゴリが存在していない場合は処理を続行しない
	if(is_null($category->getAlias())) return null;

	if(is_null($results)) {
		$results = array();

		SOY2::import("util.SOYShopPluginUtil");
		$isInstalled = SOYShopPluginUtil::checkIsActive("common_breadcrumb");

		if($isInstalled){
			SOY2::imports("module.plugins.common_breadcrumb.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO");
		}
	}

	$uri = (isset($results[$item->getId()])) ? $results[$item->getId()] : null;

	if(!isset($uri)){
		if($isInstalled){
			try{
				$uri = $dao->getPageUriByItemId($item->getId());
				$results[$item->getId()] = $uri;
			}catch(Exception $e){
				//
			}
		//サイトマップから適当に探す
		}else{
			if(is_null($listUri)){
				$values = SOYShop_DataSets::get("site.url_mapping", array());
				SOY2::import("domain.site.SOYShop_Page");
				foreach($values as $pageId => $v){
					if($v["type"] == SOYShop_Page::TYPE_LIST){
						$listUri = $v["uri"];
						$results[$item->getId()] = $listUri;
						break;
					}
				}
			}
			$uri = $listUri;
		}
	}

	return (isset($uri)) ? soyshop_get_page_url($uri, (string)$category->getAlias()) : "";
}

// array(page_id => page_name...)
function soyshop_get_page_list(){
	static $list;
	if(is_null($list)){
		$list = array();
		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			$pages = array();
		}

		if(count($pages)){
			foreach($pages as $page){
				if(is_null($page->getId())) continue;
				$list[(int)$page->getId()] = $page->getName();
			}
		}
		unset($pages);
	}
	return $list;
}

/**
 * 商品詳細のURLを取得する
 */
function soyshop_get_item_detail_link(SOYShop_Item $item){
	static $results, $urls;
	if(is_null($item->getAlias())) return null;
	
	$url = (isset($results[$item->getDetailPageId()])) ? $results[$item->getDetailPageId()] : null;
	
	if(is_null($url)){
		if(is_null($urls)) $urls = SOYShop_DataSets::get("site.url_mapping", array());

		if(isset($urls[$item->getDetailPageId()])){
			$url = $urls[$item->getDetailPageId()]["uri"];
			$results[$item->getDetailPageId()] = $url;
		}else{
			foreach($urls as $array){
				if($array["type"] == "detail"){
					$url = $array["uri"];
					$results[$item->getDetailPageId()] = $url;
					break;
				}
			}
		}
	}
	
	return (isset($url)) ? soyshop_get_page_url($url, $item->getAlias()) : "";
}

/**
 * カテゴリIDからカテゴリ名を取得する
 */
function soyshop_get_category_name(int $categoryId){
	return soyshop_get_category_object($categoryId)->getOpenCategoryName();
}

function soyshop_check_price_string($price){
	if(!is_numeric($price)) return 0;
	return (is_int($price)) ? (int)$price : (float)$price;
}

/**
 * 金額を表示する
 */
function soyshop_display_price($price){
	if(!is_numeric($price)) return 0;
	if (is_int($price)) return number_format((int)$price);

	//表記の小数点があるか？
	return (preg_match('/\.[0-9]*/', $price, $tmp)) ? soy2_number_format($price, 1) : soy2_number_format((int)$price);
}

/**
 * カートのURLを取得
 * @param bool, bool
 * @return string
 */
function soyshop_get_cart_url(bool $operation=false, bool $isAbsolute=false){
	$isUseSSL = SOYShop_DataSets::get("config.cart.use_ssl", 0);

	if($isUseSSL){
		$url = SOYShop_DataSets::get("config.cart.ssl_url");
		$url .= soyshop_get_cart_uri();
	}else{
		$url = soyshop_get_site_url($isAbsolute) . soyshop_get_cart_uri();
	}

	return ($operation) ? $url . "/operation" : $url;
}

/**
 * カートのIDを取得
 */
function soyshop_get_cart_id(){
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
		if( (defined("SOYSHOP_SMARTPHONE_MODE") && SOYSHOP_SMARTPHONE_MODE) || (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE) ) return SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
		if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE) return SOYShop_DataSets::get("config.cart.mobile_cart_id", "mobile");
	}
	return SOYShop_DataSets::get("config.cart.cart_id", "bryon");
}

/**
 * カートのURIを取得
 */
function soyshop_get_cart_uri(){
	if(defined("SOYSHOP_CART_URI")) return SOYSHOP_CART_URI;

	$cartUri = null;
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
		if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
			$cartUri = SOYShop_DataSets::get("config.cart.mobile_cart_url", "mb/cart");
		}else if( (defined("SOYSHOP_SMARTPHONE_MODE") && SOYSHOP_SMARTPHONE_MODE) || (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE) ){
			$cartUri = SOYShop_DataSets::get("config.cart.smartphone_cart_url", "i/cart");
		}
	}
	if(is_null($cartUri)) $cartUri = SOYShop_DataSets::get("config.cart.cart_url", "cart");

	//多言語化対応
	if(defined("SOYSHOP_PUBLISH_LANGUAGE")){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		if(class_exists("UtilMultiLanguageUtil")){
			$config = UtilMultiLanguageUtil::getConfig();
			if(isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) && strlen($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])){
				if(strpos($cartUri, "/")){
					$cartUri = str_replace("/", "/" . $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/", $cartUri);
				}else{
					$cartUri = $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $cartUri;
				}
			}
		}
	}

	define("SOYSHOP_CART_URI", $cartUri);
	return SOYSHOP_CART_URI;
}

/**
 * カートページのページタイトルを取得
 */
function soyshop_get_cart_page_title(){
	if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp" && class_exists("UtilMultiLanguageUtil")){
		return UtilMultiLanguageUtil::getPageTitle("cart", SOYSHOP_PUBLISH_LANGUAGE);
	}else{
		return SOYShop_DataSets::get("config.cart.cart_title", "ショッピングカート");
	}
}

/**
 * カートページにリダイレクトする
 */
function soyshop_redirect_cart($param = null){
	$url = soyshop_get_cart_url();
	if($param) $url .= "?" . $param;
	header("Location: ". $url);
	exit;
}


/**
 * カートページにリダイレクトする
 */
function soyshop_redirect_cart_with_anchor($anchor = null){
	$url = soyshop_get_cart_url();
	if($anchor)$url .= "#" . $anchor;
	header("Location: " . $url);
	exit;
}

/**
 * メールアドレスの形式チェック
 * @param string $mail
 * @return boolean 正しければtrue
 */
function soyshop_valid_email($email){
	$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
	$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
	$d3	 = '\d{1,3}';
	$ip	 = $d3. '\.'. $d3. '\.'. $d3. '\.'. $d3;
	$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

	if(! preg_match('/' . $validEmail . '/i', $email) ) {
		return false;
	}

	return true;
}

/**
 * 住所検索の郵便番号で全角や-を除く
 */
function soyshop_cart_address_validate($zipcode){
	$zipcode = mb_convert_kana($zipcode, "a");
	$zipcode = mb_convert_kana($zipcode, "s");
	$zipcode = str_replace("-", "", $zipcode);
	$zipcode = str_replace(" ", "", $zipcode);
	return $zipcode;
}

/**
 * trim
 */
function soyshop_trim($str){
	return trim($str);
}

/**
 * 配列内の各値をtrimする
 * @param array
 * @return array
 */
function soyshop_trim_values_on_array(array $arr){
	if(!count($arr)) return array();

	foreach($arr as $idx => $v){
		if(is_string($v)) $arr[$idx] = trim($v);
	}
	return $arr;
}

/**
 * カタカナの変換
 * @param string $str
 * @return string カナ変換後
 */
function soyshop_conver_kana($str){
	$str = trim($str);
	return mb_convert_kana($str, "CK", "UTF-8");
}

/**
 * カスタムフィールドのテキストフィールドでHTMLを使った箇所の\nを<br />に変換しない
 * @param string $html
 * @return string
 */
function soyshop_customfield_nl2br(string $html){
	$html = nl2br($html);
	preg_match_all('/><br.*?>/', $html, $tmp);
	if(isset($tmp[0]) && count($tmp[0])){
		$html = preg_replace('/><br.*?>/', ">\n", $html);
	}
	return $html;
}


/**
 * マイページのID取得
 */
function soyshop_get_mypage_id(){
	//モバイルチェックプラグインが動いている場合のみマイページの出し分けを行う
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
		if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
			return SOYShop_DataSets::get("config.mypage.mobile.id", "mobile");
		}elseif(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE){
			return SOYShop_DataSets::get("config.mypage.smartphone.id", "smart");
		}
	}

	return SOYShop_DataSets::get("config.mypage.id", "bryon");
}

/**
 * マイページ
 */
function soyshop_get_mypage_uri(){
	if(defined("SOYSHOP_MYPAGE_URI")) return SOYSHOP_MYPAGE_URI;

	$mypageUri = null;
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
		if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
			$mypageUri = SOYShop_DataSets::get("config.mypage.mobile.url", "mb/user");
		}elseif(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE){
			$mypageUri = SOYShop_DataSets::get("config.mypage.smartphone.url", "i/user");
		}
	}
	if(is_null($mypageUri)) $mypageUri = SOYShop_DataSets::get("config.mypage.url", "user");

	//多言語化対応
	if(defined("SOYSHOP_PUBLISH_LANGUAGE")){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		if(class_exists("UtilMultiLanguageUtil")){
			$config = UtilMultiLanguageUtil::getConfig();
			if(isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) && strlen($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])){
				if(strpos($mypageUri, "/")){
					$mypageUri = str_replace("/", "/" . $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/", $mypageUri);
				}else{
					$mypageUri = $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $mypageUri;
				}
			}
		}
	}

	define("SOYSHOP_MYPAGE_URI", $mypageUri);
	return SOYSHOP_MYPAGE_URI;
}

/**
 * マイページのURL
 */
function soyshop_get_mypage_url(bool $isAbsolute=false){

	$isUseSSL = SOYShop_DataSets::get("config.mypage.use_ssl", 0);

	if($isUseSSL){
		$url = SOYShop_DataSets::get("config.mypage.ssl_url");
		$url .= soyshop_get_mypage_uri();

	}else{
		$url = soyshop_get_site_url($isAbsolute) . soyshop_get_mypage_uri();
	}

	return $url;
}

/**
 * マイページトップのURL
 */
function soyshop_get_mypage_top_url(bool $isAbsolute=false){

	$url = soyshop_get_mypage_url($isAbsolute);

	if(strrpos($url, "/") == 0) $url = rtrim($url, "/");

	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
		if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
			return $url . "/" . SOYShop_DataSets::get("config.mypage.mobile.top", "mb/top");
		}elseif(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE){
			return $url . "/" . SOYShop_DataSets::get("config.mypage.smartphone.top", "i/top");
		}
	}

	return $url . "/" . SOYShop_DataSets::get("config.mypage.top", "top");
}

/**
 * マイページのログインページ
 */
function soyshop_get_mypage_login_url(bool $isAbsolute=false, bool $isRedirectParam=false){
	$url = soyshop_get_mypage_url($isAbsolute) . "/login";
	if($isRedirectParam) $url .= "?r=" . rawurldecode($_SERVER["REQUEST_URI"]);
	return $url;
}

/**
 * マイページのページタイトルを取得
 */
function soyshop_get_mypage_page_title(array $args){
	if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp" && class_exists("UtilMultiLanguageUtil")){
		return UtilMultiLanguageUtil::getPageTitle("mypage", SOYSHOP_PUBLISH_LANGUAGE);
	}else{
		return MyPageLogic::getMyPage()->getTitleFormat($args);
	}
}

/**
 * マイページにリダイレクトする
 */
function soyshop_redirect_mypage(string $param=""){
	$url = soyshop_get_mypage_url();
	if(strlen($param)) $url .= "?" . $param;
	header("Location: ". $url);
	exit;
}

/**
 * ログインページにリダイレクトする
 */
function soyshop_redirect_login_form(string $param=""){
	$url = soyshop_get_mypage_url() . "/login";
	if(strlen($param)) $url .= "?" . $param;
	header("Location: ". $url);
	exit;
}

/**
 * プロフィールページからリダイレクトする
 */
function soyshop_redirect_from_profile(){
	$referer = (isset($_SERVER["HTTP_REFERER"]) && strlen($_SERVER["HTTP_REFERER"]) > 0) ? $_SERVER["HTTP_REFERER"] : soyshop_get_site_url();
	header('Location:' . $referer);
	exit;
}

/**
 * マイページにログインしているかどうか
 * @TODO 実装。カートと共通させるか？
 * @return boolean
 */
function soyshop_is_login_mypage(){
	return false;
}

/**
 * ログイン後に指定したページにリダイレクトする
 */
function soyshop_redirect_designated_page($param, $postfix = null){
	$location = "Location: ". rawurldecode($param);

	if(isset($postfix) && strlen($postfix)){
		$location .= "?" . $postfix;
	}
	header($location);
	exit;
}

function soyshop_remove_get_value($param){
	if(strpos($param, "?")){
		$param = substr($param, 0, strrpos($param, "?"));
	}

	return $param;
}

/**
 * ページャのリンクの出力の際に検索やソートも考慮するURLを作る
 * @param string url
 * @return string url
 */
function soyshop_add_get_value(string $url){
	if(count($_GET) > 0){
		$query = http_build_query($_GET);
		if(strpos($url, "?")){
			$url .= "&" . $query;
		}else{
			$url .= "?" . $query;
		}
	}

	//ページャのURLの整形
	if(strpos($url, "//page-")) $url = str_replace("//page-", "/page-", $url);

	return $url;
}

/**
 * 携帯切替プラグインと多言語化プラグイン用の画像パスの変換とファイルがあるか調べる
 * @param Object SOYShop_Item, String path 画像の絶対パス
 * @return path 画像ファイルの絶対パス
 */
function soyshop_convert_file_path(string $path, SOYShop_Item $item, bool $isAbsolute=false){
	static $isOwnDomain;
	if(is_null($isOwnDomain)){
		if(defined("SOYSHOP_SITE_URL")){
			$siteUrl = trim(SOYSHOP_SITE_URL, "/") . "/";
			//siteUrl内に/siteId/がなければ独自URLとみなす(ルート設定していないことも調べておく)
			$isOwnDomain = (!SOYSHOP_IS_ROOT && is_bool(strpos($siteUrl, "/" . SOYSHOP_ID . "/")));
		}else{
			$isOwnDomain = false;
		}
	}

	//値が無ければそのまま返す
	if(strlen($path) === 0) return $path;

	//独自ドメイン + ルート設定してない場合は画像のURLをSITE_IDなしに変換する
	if($isOwnDomain && strpos($path, "/" . SOYSHOP_ID . "/") !== false) $path = str_replace("/" . SOYSHOP_ID, "", $path);

	$tmp = $path;

	//スマホ用で書き換え
	if(defined("SOYSHOP_CARRIER_PREFIX") && strlen(SOYSHOP_CARRIER_PREFIX)){
		$codeWithCarrierPrefix = $item->getCode() . "_" . SOYSHOP_CARRIER_PREFIX;
		$tmp = str_replace("/" . $item->getCode() . "/", "/" . $codeWithCarrierPrefix . "/", $tmp);
	}

	//多言語用で書き換え
	if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
		//携帯自動振り分けプラグイン分が加味されている場合
		if(isset($codeWithCarrierPrefix)){
			$codeWithLangPrefix = $codeWithCarrierPrefix . "_" . SOYSHOP_PUBLISH_LANGUAGE;
			$tmp = str_replace("/" . $codeWithCarrierPrefix . "/", "/" . $codeWithLangPrefix . "/", $tmp);
		}else{
			$codeWithLangPrefix = $item->getCode() . "_" . SOYSHOP_PUBLISH_LANGUAGE;
			$tmp = str_replace("/" . $item->getCode() . "/", "/" . $codeWithLangPrefix . "/", $tmp);
		}
	}

	//変更したパスの先にファイルがあるか調べる
	if($path != $tmp){
		$tmpPath = $tmp;
		//httpからはじまる場合はURL分を除く
		if(strpos($tmpPath, SOYSHOP_SITE_URL) === 0){
			$tmpPath = str_replace(SOYSHOP_SITE_URL, "", $tmpPath);

			//ショップIDを付与しておく
			$tmpPath = "/" . SOYSHOP_ID . "/" . $tmpPath;
		}

		$tmpPath = str_replace("/" . SOYSHOP_ID . "/", "", SOYSHOP_SITE_DIRECTORY) . $tmpPath;
		if(!is_dir($tmpPath) && file_exists($tmpPath)){
			$path = $tmp;
		}
	}

	return ($isAbsolute) ? soyshop_get_page_url($path) : $path;
}

/**
 * 独自ドメインで表示している場合、管理画面で画像のパスがずれることがあるのでパスを修正する
 */
function soyshop_convert_file_path_on_admin(string $path){
	if(!strlen($path)) return $path;

	if(is_bool(strpos(SOYSHOP_SITE_URL, $_SERVER["HTTP_HOST"])) && is_bool(strpos(SOYSHOP_SITE_URL, "/" . SOYSHOP_ID))){
		$path = "/" . SOYSHOP_ID . "/" . $path;
	}

	// /SOYSHOP_ID/の出現回数が2回以上の場合は/SOYSHOP_ID/を削る	
	if(substr_count($path, "/" . SOYSHOP_ID . "/") > 1){
		$path = str_replace("/" . SOYSHOP_ID . "/", "", $path);
		$path = "/" . SOYSHOP_ID . "/" . $path;
	}
	return $path;
}

function soyshop_get_item_sample_image(){
	$sampleImagePath = SOYSHOP_SITE_DIRECTORY . "themes/sample/noimage.jpg";
	if(!file_exists($sampleImagePath)){
		$dir = SOYSHOP_SITE_DIRECTORY . "themes/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "sample/";
		if(!file_exists($dir)) mkdir($dir);
		copy(SOYSHOP_WEBAPP. "/src/logic/init/theme/bryon/sample/noimage.jpg", $sampleImagePath);
	}
	return "/" . SOYSHOP_ID . "/themes/sample/noimage.jpg";
}

function soyshop_get_zip_2_address_js_filepath(){
	$zipJsPath = SOYSHOP_SITE_DIRECTORY . "themes/common/js/zip2address.js";
	if(!file_exists($zipJsPath)){
		$dir = SOYSHOP_SITE_DIRECTORY . "themes/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "common/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "js/";
		if(!file_exists($dir)) mkdir($dir);
		copy(SOYSHOP_WEBAPP. "/src/logic/init/theme/bryon/common/js/zip2address.js", $zipJsPath);
	}
	return "/" . SOYSHOP_ID . "/themes/common/js/zip2address.js";
}

function soyshop_create_random_string(int $n = 10, bool $isIncludeSymbol=false){
	$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));

	//記号も含む
	if($isIncludeSymbol) $str = array_merge($str, str_split(soyshop_get_symbols()));

	$r_str = "";
	for ($i = 0; $i < $n; ++$i) {
		$r_str .= $str[rand(0, count($str) - 1)];
	}
	return $r_str;
}

function soyshop_get_symbols(){
	return "+-*/_!,.#$%&";
}

//親商品のIDを取得する
function soyshop_get_parent_id_by_child_id($itemId){
	static $parentIds, $dao;
	if(is_null($dao)) $dao = new SOY2DAO();
	if(isset($parentIds[$itemId])) return $parentIds[$itemId];

	try{
		$res = $dao->executeQuery("SELECT item_type FROM soyshop_item WHERE id = :itemId LIMIT 1", array(":itemId" => $itemId));
	}catch(Exception $e){
		$res = array();
	}

	$parentIds[$itemId] = (isset($res[0]["item_type"]) && is_numeric($res[0]["item_type"])) ? (int)$res[0]["item_type"] : 0;
	return $parentIds[$itemId];
}

/**
 * 携帯自動振り分けプラグインと多言語化プラグインでも詳細ページが開ける様にページIDを変更する
 * @param Object SOYShop_Item, Object SOYShop_Page
 * @return Object SOYShop_Item
 */
function soyshop_convert_item_detail_page_id(SOYShop_Item $item, SOYShop_Page $page){
	if($page->getType() !== SOYShop_Page::TYPE_DETAIL) return $item;

	if(
		(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE) ||
		(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE) ||
		(defined("SOYSHOP_SMARTPHONE_MODE") && SOYSHOP_SMARTPHONE_MODE) ||
		(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp")
	){
		$item->setDetailPageId($page->getId());
	}
	return $item;
}

if(!function_exists("_empty")){
	function _empty($arg){
		return empty($arg);
	}
}

/**
 * 商品オプションプラグインのHTMLの組み立て パターン1
 */
function soyshop_build_item_option_html_on_item_order(SOYShop_ItemOrder $itemOrder){
	SOYShopPlugin::load("soyshop.item.option");
	$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
		"mode" => "display",
		"item" => $itemOrder,
	))->getHtmls();

	if(!is_array($htmls) || !count($htmls)) return "";

	$html = array();
	foreach($htmls as $h){
		if(!is_string($h) || !strlen($h)) continue;
		$html[] = $h;
	}

	return implode("<br>", $html);
}

/**
 * 時刻からタイムスタンプへ変換
 * @param string $str, string mode:startとendがある
 * @return integer
 */
function soyshop_convert_timestamp(string $str, string $mode="start"){
	$array = explode("-", $str);

	if(
		(!isset($array[0]) || !isset($array[1]) || !isset($array[2])) ||
		(!is_numeric($array[0]) || !is_numeric($array[1]) || !is_numeric($array[2]))
	) {
		return ($mode == "start") ? 0 : 2147483647;
	}

	if($mode == "start"){
		return mktime(0, 0, 0, $array[1], $array[2], $array[0]);
	}else{
		return mktime(23, 59, 59, $array[1], $array[2], $array[0]);
	}
}

function soyshop_convert_timestamp_on_array(array $array, string $mode = "start"){
	if(
		(!isset($array["year"]) || !isset($array["month"]) || !isset($array["day"])) ||
		(!is_numeric($array["year"]) || !is_numeric($array["month"]) || !is_numeric($array["day"]))
	) {
		return ($mode == "start") ? 0 : 2147483647;
	}

	if($mode == "start"){
		return mktime(0, 0, 0, (int)$array["month"], (int)$array["day"], (int)$array["year"]);
	}else{
		return mktime(23, 59, 59, (int)$array["month"], (int)$array["day"], (int)$array["year"]);
	}
}

function soyshop_shape_timestamp(int $timestamp, string $mode="start"){
	$array = explode("-", date("Y-n-j", $timestamp));
	if($mode == "start"){
		return mktime(0, 0, 0, (int)$array[1], (int)$array[2], (int)$array[0]);
	}else{
		return mktime(23, 59, 59, (int)$array[1], (int)$array[2], (int)$array[0]);
	}
}

/**
 * タイムスタンプから日付へ変換
 * @param integer $timestamp, string $divide
 * @return string
 */
function soyshop_convert_date_string(int $timestamp, string $divide="-"){
	if($timestamp == 0 || $timestamp == 2147483647) return "";
	return date("Y" . $divide . "m" . $divide . "d", $timestamp);
}

/**
 * タイムスタンプから時刻付き日付へ変換する
 * @param int timestamp, string divide
 * @return string
 */
function soyshop_convert_date_with_time_string(int $timestamp, string $divide="-"){
	if($timestamp == 0 || $timestamp == 2147483647) return "";
	return soyshop_convert_date_string($timestamp, $divide) . date("H:i:s", $timestamp);
}

/**
 * タイムスタンプからarray("year"=>int, "month"=>1, "day"=>1)の配列に変換
 * @param integer $timestamp
 * @return array("year"=>int, "month"=>int, "day"=>int)
 */
function soyshop_convert_date_array_by_timestamp(int $timestamp){
	if($timestamp === 0) return array("year" => 0, "month" => 0, "day" => 0);
	$dateArr = array("year" => date("Y", $timestamp), "month" => date("n", $timestamp), "day" => date("j", $timestamp));
	if(strlen($dateArr["year"]) < 4){
		$dateArr["month"] = 0;
		$dateArr["day"] = 0;
	}
	return $dateArr;
}

function soyshop_convert_date_string_by_array(array $arr){
	foreach(array("year", "month", "day") as $lab){
		if(!isset($arr[$lab])) $arr[$lab] = "";
	}
	return $arr["year"] . "-" . $arr["month"] . "-" . $arr["day"];
}

/**
 * 今から○ヶ月前のタイムスタンプを取得
 */
function soyshop_get_a_few_months_ago(int $n=1, int $timestamp=0){
	if($timestamp == 0) $timestamp = time();
	if(!is_numeric($n)) $n = 1;
	$ago = strtotime("-" . $n . " month");

	//月末問題　変換前と変換後の月が同じであれば月末ではない
	$nowM = date("n", $timestamp);
	$nowM -= $n;
	if($nowM <= 0) $nowM = 12 + $nowM;

	if(date("n", $ago) == $n) return $ago;

	//月末であった場合
	$dates = explode("-", date("Y-m", $timestamp));
	$year = (int)$dates[0];
	$month = (int)$dates[1];

	$month -= $n;
	if($month <= 0){
		$month = 12 + $month;
		$year--;
	}
	return mktime(0, 0, 0, $month + 1, 1, $year) - 1;
}

/**
 * 任意のタイムスタンプから月始めのタイムスタンプを取得
 */
function soyshop_get_begin_of_month(int $timestamp){
	if($timestamp === 0) $timestamp = time();
	$dates = explode("-", date("Y-n", $timestamp));
	return mktime(0, 0, 0, $dates[1], 1, $dates[0]);
}

function soyshop_file_put_contents(string $f, string $v){
	$dir = SOYSHOP_SITE_DIRECTORY . ".cache/var_export/";
	if(!file_exists($dir)) mkdir($dir);
	file_put_contents($dir . $f . ".txt", var_export($v, true) . "\n", FILE_APPEND);
}

/**
 * 顧客の年齢を調べる
 * @param string $birthday
 * @return integer
 */
function soyshop_get_user_age($birthday){
	if(is_null($birthday)) return null;
	preg_match('/^\d{4}-\d{1,}-\d{1,}/', $birthday, $tmp);
	if(isset($tmp[0])){
		$array = explode("-", $tmp[0]);
		$y = (int)$array[0];
		$m = (int)$array[1];
		$d = (int)$array[2];
	}else{	//birthdayの他の文字列の渡し方用
		return null;
	}

	$age = date("Y") - $y;
	if($m < date("n")) return $age;	//誕生月が今月よりも前の場合はそのまま返す
	if($m > date("n")) return --$age;	//誕生月が今月よりも後の場合は-1で返す

	//誕生月が今月の場合
	return ($d <= date("j")) ? $age : --$age;
}

/**
 * 顧客の年齢の○ヶ月の方を調べる
 * @param string $birthday, timestamp $now
 * @return integer
 */
function soyshop_get_user_age_month($birthday, $now=null){
	if(is_null($birthday)) return null;
	preg_match('/^\d{4}-\d{1,}-\d{1,}/', $birthday, $tmp);
	if(isset($tmp[0])){
		$array = explode("-", $tmp[0]);
		$m = (int)$array[1];
		$d = (int)$array[2];
	}else{	//birthdayの他の文字列の渡し方用
		return null;
	}

	//nowがない場合は通常計算、nowがある場合はdate("j")の箇所の値をnowに合わせる
	$nowD = (isset($now) && is_numeric($now)) ? date("j", $now) : date("j");

	if(is_null($now) || !is_numeric($now)) $now = time();
	$diff = $m - date("n", $now);

	if($diff === 0){
		//誕生日がきてない場合は11ヶ月で返す
		if($d > $nowD){
			return 11;
		}else{
			return 0;
		}
	}

	//誕生日になっていない時はプラス1
	if($d > $nowD) $diff++;

	return ($diff > 0) ? 12 - $diff : $diff * -1;
}
/**
 * 顧客の生後○日の方を調べる
 * @param string $birthday, timestamp $now
 * @return integer
 */
function soyshop_get_days_after_birth($birthday, $now=null){
	if(is_null($birthday)) return null;
 	preg_match('/^\d{4}-\d{1,}-\d{1,}/', $birthday, $tmp);
 	if(!isset($tmp[0])) return null;
	$array = explode("-", $tmp[0]);
	$y = (int)$array[0];
	$m = (int)$array[1];
	$d = (int)$array[2];

	if(is_null($now)) $now = time();

	$time = mktime(0, 0, 0, $m, $d, $y);
	$diff = $now - $time;

	return (int)($diff / (24 * 60 * 60));
}

/**
 * プラグインの拡張ポイントで得られた結果を整形する
 * @param array
 * @return array
 */
function soyshop_shape_extension_point_result_array(array $results){
	if(!count($results)) return array();

	$list = array();
	foreach($results as $arr){
		if(!is_array($arr)) continue;
		foreach($arr as $fieldId => $values){
			$list[$fieldId] = $values;
		}
	}
	return $list;
}

/**
 * 商品一覧から商品IDの一覧を取得
 * @param array
 * @return array
 */
function soyshop_get_item_id_by_items(array $items){
	if(!count($items)) return array();

	$ids = array();
	foreach($items as $items){
		$ids[] = (int)$item->getId();
	}
	return $ids;
}