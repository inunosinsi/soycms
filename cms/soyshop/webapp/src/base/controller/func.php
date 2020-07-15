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
			$args = implode($args,"/");
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
				for($i = 0; $i < count($args); $i++){
					$args[$i] = (isset($args[$i + 1])) ? trim($args[$i + 1]) : null;
					if(is_null($args[$i])) unset($args[$i]);
				}
			}
		}
	}

	return array($uri, $args);
}

function get_page_object_on_controller($uri){
	$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
	try{
		return $dao->getByUri($uri);
	}catch(Exception $e){
		//
	}

	//ルートでページャ対策
	try{
		return $dao->getByUri(SOYShop_Page::URI_HOME);
	}catch(Exception $e){
		//ページが存在しない場合
		return $dao->getByUri(SOYSHOP_404_PAGE_MARKER);
	}
}

function include_page_class($pageType){
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
