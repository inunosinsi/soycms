<?php

/**
 * @return
 */
function &get_path_builder(){
	static $builder;
	if(is_null($builder)) {
		SOY2::import("base.site.SOYShopPathInfoBuilder");
		$builder = new SOYShopPathInfoBuilder();
	}
	return $builder;
}

/**
 * PATH_INFOをページのURIとパラメータに分離する
 * @return Array(String, Array)
 */
function get_uri_and_args(){
	/*
	 * パスからURIと引数に変換
	 * 対応するページが存在すれば$uriに値が入る
	 */
	$pathBuilder = get_path_builder();
	$uri  = $pathBuilder->getPath();
	$args = $pathBuilder->getArguments();

	/*
	 * 対応するページがない場合
	 */
	if( empty($uri) ){
		if(empty($args)){
			/*
			 * ルート直下へのアクセスはトップページ
			 */
			$uri = SOYSHOP_TOP_PAGE_MARKER;
		}elseif( is_array($args) && count($args) && strlen($args[0]) && 0 === strpos($args[0], "index.") ){
			/*
			 * http://domain.com/index.*へのアクセスはトップページへリダイレクトする
			 */
			array_shift($args);
			$args = implode($args,"/");
			SOY2PageController::redirect(soyshop_get_site_url(true) . $args);
		}
	}else{
		/*
		 * http://domain.com/_homeへのアクセスはトップページへリダイレクトする
		 */
		if(SOYSHOP_TOP_PAGE_MARKER == $uri){
			$args = implode("/", $args);
			SOY2PageController::redirect(soyshop_get_site_url(true) . $args);
		}

		/**
		 * スマホサイト、もしくは多言語サイトの時は、$uriの末尾にアプリケーションのURIを追加して、$argsの値を一つずらす
		 *    これを行わないと、カートに商品を放り込めないし、マイページは開けない
		 */
		if(
			(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE) ||
			(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE) ||
			(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp")
		){
			//argsに多言語のプレフィックスが入った場合は、$uriに多言語化のプレフィックスを付与して、$argsの値を前にずらす
			if(defined("SOYSHOP_PUBLISH_LANGUAGE") && (isset($args[0]) && $args[0] == SOYSHOP_PUBLISH_LANGUAGE)){
				$uri .= "/" . $args[0];
				array_shift($args);
			}

			$pcCartUri = SOYShop_DataSets::get("config.cart.cart_url", "cart");
			$pcMyPageUri = SOYShop_DataSets::get("config.mypage.url", "user");
			if(isset($args[0]) && ($args[0] == $pcCartUri || $args[0] == $pcMyPageUri)){
				$uri .= "/" . trim($args[0]);
				$argsCnt = count($args);
				for($i = 0; $i < $argsCnt; ++$i){
					$args[$i] = (isset($args[$i + 1])) ? trim($args[$i + 1]) : null;
					if(is_null($args[$i])) unset($args[$i]);
				}
			}
		}
	}

	//拡張ポイントでuriとargsを上書きできる
	SOYShopPlugin::load("soyshop.uri.and.arguments");
	$delegate = SOYShopPlugin::invoke("soyshop.uri.and.arguments", array(
		"uri" => $uri,
		"args" => $args
	));

	$newUri = (string)$delegate->getUri();
	if(strlen($newUri)) $uri = $newUri;

	$newArgs = $delegate->getArgs();
	if(isset($newArgs) && is_array($newArgs)) $args = $newArgs;

	return array($uri, $args);
}

/**
 * @param string
 * @return SOYShop_Page
 */
function get_page_object_on_controller(string $uri){
	//ルートでページャ対策
	$tmp = array();
	if(strlen($uri) > 5 && is_numeric(strpos($uri, "page-"))) preg_match('/^page-[\d]*/', $uri, $tmp);
	
	if(isset($tmp[0])) {
		SOY2::import("domain.site.SOYShop_Page");
		$page = soyshop_get_page_object_by_uri(SOYShop_Page::URI_HOME);
		
		if(is_numeric($page->getId())){
			if($page->getId() == -1) return $page;	//DBの接続エラー等の場合はID:-1のページになっている

			//ページのタイプが一覧もしくは検索結果ページの場合は返す
			switch($page->getType()){
				case SOYShop_Page::TYPE_LIST:
				case SOYShop_Page::TYPE_SEARCH:
					return $page;
			}
		}
	}
	
	//ページャの候補以外
	$page = soyshop_get_page_object_by_uri($uri);
	if(is_numeric($page->getId())) return $page;
	
	//404ページ
	if(!class_exists("SOYShop_Page")) SOY2::import("domain.site.SOYShop_Page");
	$page = soyshop_get_page_object_by_uri(SOYSHOP_404_PAGE_MARKER);
	$page->setUri(SOYShop_Page::NOT_FOUND);
	return $page;
}

function include_page_class(string $pageType){
	SOY2::imports("base.site.classes.*");
	SOY2::import("base.site.SOYShopPageBase");
	switch($pageType){
		case SOYShop_Page::TYPE_COMPLEX:
			SOY2::import("base.site.pages.SOYShop_ComplexPageBase");
			break;
		case SOYShop_Page::TYPE_LIST:
			SOY2::import("base.site.pages.SOYShop_ListPageBase");
			break;
		case SOYShop_Page::TYPE_DETAIL:
			SOY2::import("base.site.pages.SOYShop_DetailPageBase");
			break;
		case SOYShop_Page::TYPE_FREE:
			SOY2::import("base.site.pages.SOYShop_FreePageBase");
			break;
		case SOYShop_Page::TYPE_SEARCH:
			SOY2::import("base.site.pages.SOYShop_SearchPageBase");
			break;
	}
}
