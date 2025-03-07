<?php
SOY2::import("module.plugins.skip_cart_page.util.SkipCartPageUtil");
class SkipCartPageSkip extends SOYShopCartSkipBase{

	/**
	 * @return bool
	 */
	function isSkip01Page(){
		return SkipCartPageUtil::isSkip(1);
	}

	/**
	 ` @param string
	 */
	private function _redirect(string $url){
		header("location:".$url);
		exit;
	}

	private function _getHomePageUrl(){
		try{
			$res = soyshop_get_hash_table_dao("page")->executeQuery(
				"SELECT id FROM soyshop_page WHERE uri = :uri", 
				array(":uri" => SOYShop_Page::URI_HOME)
			);
		}catch(Exception $e){
			$res = array();
		}

		//if(count($res)) 
		$pageId = (isset($res[0]["id"])) ? (int)$res[0]["id"] : 1;
		return soyshop_get_page_url(soyshop_get_page_object($pageId)->getUri());
	}

	/**
	 * @param int, bool
	 * @return string
	 */
	private function _getListPageUrl(int $categoryId, bool $isDefaultCategory=true){
		static $pages;
		if(is_null($pages)){
			try{
				$pages = soyshop_get_hash_table_dao("page")->getByType(SOYShop_Page::TYPE_LIST);
			}catch(Exception $e){
				$pages = array();
			}
		}

		if($isDefaultCategory){
			foreach($pages as $pageId => $page){
				if($categoryId === (int)$page->getPageObject()->getDefaultCategory()){
					return $page->getUri();
				}
			}
		}else{
			foreach($pages as $pageId => $page){
				$categories = $page->getPageObject()->getCategories();
				if(!is_array($categories) || !count($categories)) continue;

				if(is_numeric(array_search($categoryId, $categories))){
					$category = soyshop_get_category_object($categoryId);
					return $page->getUri()."/".$category->getAlias();
				}
			}
		}

		return "";
	}

	/**
	 * @param CartLogic
	 */
	function exitFromCart(CartLogic $cart){
		$itemOrders = $cart->getItems();
		if(!count($itemOrders)) self::_redirect(self::_getHomePageUrl());

		// カートの一番最後に入れた商品の詳細ページを探す
		$lastItemOrder = $itemOrders[array_key_last($itemOrders)];
		$item = soyshop_get_item_object((int)$lastItemOrder->getItemId());

		$detailPageId = (int)$item->getDetailPageId();
		if($detailPageId > 0){
			$pageUri = soyshop_get_page_object($detailPageId)->getUri();
			self::_redirect(soyshop_get_page_url($pageUri."/".$item->getAlias()));
		}

		// 商品一覧ページを調べる
		$categoryId = (int)$item->getCategoryId();
		if($categoryId > 0){
			$pageUri = self::_getListPageUrl($categoryId);
			if(strlen($pageUri)) self::_redirect(soyshop_get_page_url($pageUri));

			$pageUri = self::_getListPageUrl($categoryId, false);
			if(strlen($pageUri)) self::_redirect(soyshop_get_page_url($pageUri));
		}

		self::_redirect(self::_getHomePageUrl());
	}

	/**
	 * @return bool
	 */
	function isSkip02Page(){
		return SkipCartPageUtil::isSkip(2);
	}

	/**
	 * @param CartLogic
	 */
	function runVirtually02Page(CartLogic $cart){
		if(!$cart->getCustomerInformation() instanceof SOYShop_User || !is_numeric($cart->getCustomerInformation()->getId())){
			//ログインをしている場合はカートに顧客情報をいれる
			$mypage = MyPageLogic::getMyPage();
			if($mypage->getIsLoggedin() && (int)$mypage->getUserId() > 0){
				$cart->setCustomerInformation(soyshop_get_user_object((int)$mypage->getUserId()));
				$cart->save();
			}
		}
	}

	/**
	 * @return bool
	 */
	function isSkip03Page(){
		return SkipCartPageUtil::isSkip(3);
	}

	/**
	 * @param CartLogic
	 */
	function runVirtually03Page(CartLogic $cart){
		SOY2::import("cart._common.fn", ".php");
		$moduleId = SkipCartPageUtil::getPaymentModuleConfig();
		if(strlen($moduleId) && is_numeric(soyshop_get_plugin_object($moduleId)->getId())){
			soyshop_cart_register_payment_module($cart, $moduleId);	
		}

		$moduleId = SkipCartPageUtil::getDeliveryModuleConfig();
		if(strlen($moduleId) && is_numeric(soyshop_get_plugin_object($moduleId)->getId())){
			soyshop_cart_register_delivery_module($cart, $moduleId);
		}
	}
}
SOYShopPlugin::extension("soyshop.cart.skip", "skip_cart_page", "SkipCartPageSkip");
