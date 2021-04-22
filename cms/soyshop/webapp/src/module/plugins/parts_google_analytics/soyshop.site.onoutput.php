<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class GoogleAnalyticsOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){

		SOY2::import("module.plugins.parts_google_analytics.util.GoogleAnalyticsUtil");
		$config = GoogleAnalyticsUtil::getConfig();
		$code = $config["tracking_code"];

		if(!strlen($code)) return $html;

		//アナリティクスタグの挿入設定　カートとマイページは無条件で挿入→マイページはログインが必要なページは除く
		if(defined("SOYSHOP_PAGE_ID")){
			$displayConfig = GoogleAnalyticsUtil::getPageDisplayConfig();
			if(isset($displayConfig[SOYSHOP_PAGE_ID]) && $displayConfig[SOYSHOP_PAGE_ID] == GoogleAnalyticsUtil::INSERT_TAG_NOT_DISPLAY){
				return $html;
			}
		}

		if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE && !self::_checkInsertTagOnMypage()) return $html;

		//XHTMLではないXMLでは出力しない
		if(
			strpos($html, '<?xml version="1.0"') !== false
			&&
			strpos($html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML') === false
		){
			return $html;
		}

		//RSS, Atomでは出力しない
		if(
			strpos($html, '<rss version="2.0">') !== false
			||
			strpos($html, '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">') !== false
		){
			return $html;
		}

		$isCompletePage = false;	//カートの注文完了画面かどうかのフラグ

		if(SOYSHOP_CART_MODE){	//現在のページがカートページであるか？をチェック
			$query = "_gaq.push(['_trackPageview', '" . $_SERVER["REDIRECT_URL"] . "/complete/']);";
			if(is_numeric(strpos($html,$query))) $isCompletePage = true;	//注文完了画面ならばtrue
		}

		//完了ページならば、eコマーストラッキングを挿入する
		if($isCompletePage) $code = self::_convertTrackingCode($html, $code);
		
		switch($config["insert_to_head"]){
			case GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_HEAD:	//<head>の直後
				if(is_numeric(stripos($html,'<head>'))){
					return str_ireplace('<head>','<head>'."\n".$code,$html);
				}else if(preg_match('/<body\\s[^>]+>/',$html)){
					return preg_replace('/(<head\\s[^>]+>)/',"\$0\n".$code,$html);
				}else if(is_numeric(stripos($html,'<html>'))){
					return str_ireplace('<html>','<html>'."\n".$code,$html);
				}else if(preg_match('/<html\\s[^>]+>/',$html)){
					return preg_replace('/(<html\\s[^>]+>)/',"\$0\n".$code,$html);
				}
				break;
			case GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HEAD:	//</head>の直前
				if(is_numeric(stripos($html,'</head>'))){
					return str_ireplace('</head>', $code . "\n" . '</head>', $html);
				}else if(is_numeric(stripos($html, '<body>'))){
					return str_ireplace('<body>', '<body>' . "\n" . $code, $html);
				}else if(preg_match('/<body\\s[^>]+>/', $html)){
					return preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $code, $html);
				}else if(is_numeric(stripos($html, '<head>'))){
					return str_ireplace('<head>', '<head>' . "\n" . $code, $html);
				}else if(is_numeric(stripos($html, '<html>'))){
					return str_ireplace('<html>', '<html>' . "\n" . $code, $html);
				}else if(preg_match('/<html\\s[^>]+>/', $html)){
					return preg_replace('/(<html\\s[^>]+>)/', "\$0\n" . $code, $html);
				}
				break;
			case GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_BODY:	//<body>の直後
				if(is_numeric(stripos($html, '<body>'))){
					return str_ireplace('<body>', '<body>' . "\n" . $code, $html);
				}else if(preg_match('/<body\\s[^>]+>/', $html)){
					return preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $code, $html);
				}else if(is_numeric(stripos($html, '</head>'))){
					return str_ireplace('</head>', $code . "\n" . '</head>', $html);
				}else if(is_numeric(stripos($html, '<head>'))){
					return str_ireplace('<head>', '<head>' . "\n" . $code, $html);
				}else if(is_numeric(stripos($html, '<html>'))){
					return str_ireplace('<html>', '<html>' . "\n" . $code, $html);
				}else if(preg_match('/<html\\s[^>]+>/', $html)){
					return preg_replace('/(<html\\s[^>]+>)/', "\$0\n" . $code, $html);
				}
				break;
			case GoogleAnalyticsUtil::INSERT_AFTER_THE_END_OF_BODY:	//</body>の直後
				if(is_numeric(stripos($html,'</body>'))){
					return str_ireplace('</body>','</body>'."\n".$code,$html);
				}else if(preg_match('/</body\\s[^>]+>/',$html)){
					return preg_replace('/(</body\\s[^>]+>)/',"\$0\n".$code,$html);
				}
				break;
			case GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HTML:	//意図的に末尾
				//何もしない
				break;
			default:	//</body>直前に挿入
				if(is_numeric(stripos($html, '</body>'))){
					return str_ireplace('</body>', $code . '</body>', $html);
				}else if(is_numeric(stripos($html, '</html>'))){
					return str_ireplace('</html>', $code . '</html>', $html);
				}
		}

		return $html.$code;
	}

	//マイページでanalyticsタグを挿入するか？
	private function _checkInsertTagOnMypage(){
		if(!isset($_SERVER["REQUEST_URI"])) return false;

		//トラッキングタグを出力しないページ
		$uri = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], SOYSHOP_MYPAGE_URI));
		$uri = substr($uri, strpos($uri, "/") + 1);

		//uriに下記の文字列があればtrue
		$types = array("login", "logout", "register", "remind");
		foreach($types as $t){
			if(is_numeric(strpos($uri, $t))) return true;
		}

		//boardの場合のみ特殊
		if(is_numeric(strpos($uri, "board"))){
			//uriに下記の文字列があればfalse
			$types = array("edit", "confirm", "complete", "remove");
			foreach($types as $t){
				if(is_numeric(strpos($uri, $t))) return false;
			}

			return true;
		}

		return false;
	}

	private function _convertTrackingCode($html, $code){
		/**
		 * 注文情報取得で良い方法が思いつかないので、htmlからトラッキングナンバーを取得する
		 */
		preg_match('/\d{1,7}-\d{4}-\d{4}/', $html, $tmp);

		//値を取得できたか？
		if(!isset($tmp[0])) return $code;

		//トラッキングコードが新しいバージョンかどうか？
		$query = "_gaq.push(['_trackPageview']);";
		if(strpos($code, $query) === false) return $code;

		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		foreach($tmp as $trackingNumber){
			//市外局番は必ず0から始まるので、0から始まっている番号は除く
			if(strpos($trackingNumber, "0") === 0) continue;

			try{
				$order = $orderDao->getByTrackingNumber($trackingNumber);
			}catch(Exception $e){
				$order = new SOYShop_Order();
			}
			if(!is_null($order->getId())) break;
		}

		//注文情報を取得できたか？
		if(is_null($order->getId())) return $code;

		$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		try{
			$items = $itemOrderDao->getByOrderId($order->getId());
		}catch(Exception $e){
			$items = array();
		}

		//注文された商品が一つでもあったか？
		if(count($items) === 0) return $code;

		$insertCode = self::_buildInsertCode($order, $items);
		$changeCode = $query . "\n" . $insertCode;

		return str_replace($query, "$changeCode", $code);
	}

	private function _buildInsertCode(SOYShop_Order $order, $items){
		return self::_buildInsertOrderCode($order) . "\n\n" .
				self::_buildInsertItemCode($items, $order->getTrackingNumber()) . "\n\n" .
				"  _gaq.push(['_trackTrans']);";
	}

	//店舗情報と注文の総額
	private function _buildInsertOrderCode(SOYShop_Order $order){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();

		$s = "  ";	//表示の調整用

		$html = array();

		$html[] = $s."_gaq.push(['_addTrans',";

		$html[] = $s . $s . "'" . $order->getTrackingNumber() . "',";		//トラッキングナンバー
		$html[] = $s . $s . "'" . $config->getShopName() . "',";			//店舗名
		$html[] = $s . $s . "'" . $order->getPrice() . "',";				//総額
		$html[] = $s . $s . "'0',";									//税額

		$deliveryPrice = 0;

		$modules = $order->getModuleList();
		foreach($modules as $key => $module){
			if(strpos($key, "delivery_") === 0){
				$deliveryPrice = $module->getPrice();
				break;
			}
		}

		$html[] = $s . $s . "'" . $deliveryPrice . "',";					//送料

		//郵便番号から住所情報を取得する
		$company = $config->getCompanyInformation();
		if(isset($company["address1"])){
			$zipcode = soyshop_cart_address_validate($company["address1"]);
			$addressSearchLogic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");
			$res = $addressSearchLogic->search($zipcode);

			$html[] = $s . $s . "'" . $res["address1"] . "',";			//市区町村
			$html[] = $s . $s . "'" . $res["prefecture"] . "',";			//県
		}else{
			$html[] = $s . $s . "'',";
			$html[] = $s . $s . "'',";
		}
		$html[] = $s . $s . "'日本'";									//国名

		$html[] = $s . "]);";

		return implode("\n", $html);
	}

	//各商品の情報
	private function _buildInsertItemCode($items, $trackingNumber){

		$s = "  ";	//表示の調整用

		$html = array();

		$categoryList = soyshop_get_category_list();
		foreach($items as $orderItem){
			$item = soyshop_get_item_object($orderItem->getItemId());
			if(is_null($item->getId())) continue;

			$html[] = $s . "_gaq.push(['_addItem',";

			$html[] = $s . $s . "'" . $trackingNumber . "',";	//トラッキングナンバー
			$html[] = $s . $s . "'" . $item->getCode() . "',";
			$html[] = $s . $s . "'" . $item->getName() . "',";

			$categoryName = (isset($categoryList[$item->getCategory()])) ? $categoryList[$item->getCategory()] : "";

			$html[] = $s . $s . "'" . $categoryName . "',";			//カテゴリー
			$html[] = $s . $s . "'" . $orderItem->getItemPrice() . "',";	//商品単価
			$html[] = $s . $s . "'" . $orderItem->getItemCount() . "'";	//注文個数

			$html[] = $s . "]);";
		}

		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "parts_google_analytics", "GoogleAnalyticsOnOutput");
