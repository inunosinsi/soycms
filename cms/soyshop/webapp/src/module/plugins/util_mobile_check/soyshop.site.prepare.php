<?php
class MobileCheckPrepareAction extends SOYShopSitePrepareAction{

	//スマートフォンの転送先設定用定数
	const CONFIG_SP_REDIRECT_PC = 0;//PCサイト
	const CONFIG_SP_REDIRECT_SP = 1;//スマートフォンサイト
	const CONFIG_SP_REDIRECT_MB = 2;//ケータイサイト

	const REDIRECT_PC = 0;//PCサイト表示（何もしない）
	const REDIRECT_SP = 1;//スマートフォンサイト転送
	const REDIRECT_MB = 2;//ケータイサイト転送

	/**
	 * @return string
	 */
	function prepare(){

		//二度実行しない
		if(defined("SOYSHOP_CARRIER_PREFIX")) return;

		SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
		$cnf = UtilMobileCheckUtil::getConfig();

		//クッキー非対応機種の設定 → 廃止
		//if(!defined("SOYSHOP_COOKIE")) define("SOYSHOP_COOKIE", ( isset($cnf["cookie"]) && $cnf["cookie"] == 1) );

		$redirect = self::REDIRECT_PC;
		$isMobile = false;
		$isSmartPhone = false;

		//
		// //セッションIDを再生成しておく（DoCoMo i-mode1.0 限定）→ 廃止
		// if(
		// 	self::isMobile() && defined("SOYSHOP_MOBILE_CARRIER") && SOYSHOP_MOBILE_CARRIER == "DoCoMo" && SOYSHOP_COOKIE
		// 	&&
		// 	(isset($_GET[session_name()]) || isset($_POST[session_name()])) && !isset($_COOKIE[session_name()])
		// ){
		//
		// 	$session_time = $cnf["session"] * 60;
		//
		// 	ini_set("session.gc_maxlifetime", $session_time);
		//
		// 	if(isset($_POST[session_name()])){
		// 		session_id($_POST[session_name()]);
		// 	}else{
		// 		session_id($_GET[session_name()]);
		// 	}
		//
		// 	session_start();
		// 	session_regenerate_id(true);
		// 	output_add_rewrite_var(session_name(), session_id());
		// }
		//
		//ケータイ
		if(SOYSHOP_IS_MOBILE){
			$redirect = self::REDIRECT_MB;
			$isMobile = true;
		//iPad
		}else if(SOYSHOP_IS_TABLET){
			if($cnf["redirect_ipad"] == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		//スマートフォン(iPadだった場合はチェックしない)
		}else if(SOYSHOP_IS_SMARTPHONE){
			if($cnf["redirect_iphone"] == self::CONFIG_SP_REDIRECT_SP){
				$redirect = self::REDIRECT_SP;
				$isSmartPhone = true;
			}elseif($cnf["redirect_iphone"] == self::CONFIG_SP_REDIRECT_MB){
				//ケータイ
				$redirect = self::REDIRECT_MB;
				$isMobile = true;
			}else{
				//PC
				$redirect = self::REDIRECT_PC;
			}
		}else{
			//何もしない
		}

		//tabletをスマホと見立てる場合
		if(!defined("SOYSHOP_SMARTPHONE_MODE")) define("SOYSHOP_SMARTPHONE_MODE", $isSmartPhone);

		//別キャリアを見ている場合は一旦PCにとばす。
		if(SOYSHOP_IS_MOBILE || SOYSHOP_IS_SMARTPHONE){
			//モバイルとスマホで同じプレフィックスを設定するとリダイレクトループになるので、別の端末でも同じ端末として解釈
			if($cnf["prefix"] != $cnf["prefix_i"]){
				$redirectPrefix = ($redirect == self::REDIRECT_MB) ? $cnf["prefix_i"] : $cnf["prefix"];
				self::_checkCarrier($redirectPrefix);
			}
		}

		//PCの場合以外のリダイレクト処理
		if($redirect != self::REDIRECT_PC){
			//prefixの決定
			if($redirect == self::REDIRECT_MB && strlen($cnf["prefix"])){
				$prefix = $cnf["prefix"];
				define("SOYSHOP_DOCOMO_CSS", $cnf["css"]);
				//カートのお買物に戻るリンクの設定
				define("SOYSHOP_RETURN_LINK", $cnf["url"]);
			}
			if($redirect == self::REDIRECT_SP && strlen($cnf["prefix_i"])){
				$prefix = $cnf["prefix_i"];
			}

			//リダイレクト先の絶対パス
			$path = self::_getRedirectPath($prefix);

			if($path){
				//if do not work Location header
				ob_start();
				echo "<a href=\"" . htmlspecialchars($path, ENT_QUOTES, "UTF-8") . "\">" . htmlspecialchars($cnf["message"], ENT_QUOTES, "UTF-8") . "</a>";

				//リダイレクト
				if($cnf["redirect"]) SOY2PageController::redirect($path);

				exit;
			}

		//PCの場合、念の為、別キャリアのページを見ていないか調べる
		}else{
			self::_checkCarrier($cnf["prefix"]);
			self::_checkCarrier($cnf["prefix_i"]);

			//PC版の場合はprefixはなし
			$prefix = null;
		}

		//リダイレクトをしなかった場合、prefixを定数に入れておく
		if(!defined("SOYSHOP_CARRIER_PREFIX")) define("SOYSHOP_CARRIER_PREFIX", $prefix);
	}

	/**
	 * キャリア判定でパスとキャリアが間違っている時、
	 * 一旦PCサイトにリダイレクトさせてから、サイドキャリアに対応したサイトにリダイレクト
	 */
	private function _checkCarrier($prefix){
		//PATH_INFO
		$pathInfo = UtilMobileCheckUtil::getPathInfo();

		if($pathInfo === "/" . $prefix || strpos($pathInfo, "/" . $prefix . "/") === 0){
			$path = self::_getRedirectPcPath($prefix);
			SOY2PageController::redirect($path);
			exit;
		}
	}

	/**
	 * URLにプレフィックスを付けた絶対パスを返す
	 */
	private function _getRedirectPath($prefix){
		//スマホのプレフィックスと多言語のプレフィックスが付与されている場合はfalseを返す
		if(UtilMobileCheckUtil::checkMultiLanguage()) return false;

		//無限ループになるときはfalseを返す
		if(UtilMobileCheckUtil::checkLoop($prefix)) return false;

		$path = UtilMobileCheckUtil::buildUrl($prefix);
		return UtilMobileCheckUtil::addQueryString($path);
	}

	/**
	 * 各キャリアのprefixを除いたパスを返す
	 */
	private function _getRedirectPcPath($prefix){
		//各キャリアのprefixを除いたREQUEST URIを取得
		$path = UtilMobileCheckUtil::removeCarrierPrefixUri($prefix);
		return UtilMobileCheckUtil::addQueryString($path);
	}
}

SOYShopPlugin::extension("soyshop.site.prepare", "util_mobile_check", "MobileCheckPrepareAction");
