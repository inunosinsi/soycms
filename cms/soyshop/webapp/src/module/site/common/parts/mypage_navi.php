<?php
function soyshop_parts_mypage_navi($html, $page){

	$obj = $page->create("soyshop_parts_mypage_navi", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_mypage_navi", $html)
	));

	if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());

	$mypage = MyPageLogic::getMyPage();
	if($mypage->getIsLoggedin()){
		SOY2::import("util.SOYShopPluginUtil");

		$html = array();
		$html[] = "<ul class=\"nav nav-tabs\">";
		$pages = array(
			"profile" => "プロフィール",
			"order" => "注文履歴",
			"board" => "掲示板",
			//"reserve" => "予約の変更",	//隠しモード 仕様が決まるまで
			"mail" => "メールボックス",
			"edit" => "登録情報の変更",
			"mailaddress" => "メールアドレスの変更",
			"password" => "パスワードの変更",
			"review" => "レビュー",
			"point" => "ポイント履歴",
			"inquiry" => "お問い合わせ",
			"payjp" => "クレジットカード",
			"card" => "カード",
			"bank" => "振込先",
			"withdraw" => "退会",
			"logout" => "ログアウト"
		);
		$currentUri = trim(str_replace("/" . soyshop_get_mypage_uri(), "", $_SERVER["PATH_INFO"]), "/");
		$user = $mypage->getUser();
		foreach($pages as $type => $label){
			//お問い合わせはプラグインが有効の時のみ
			$isThrow = false;
			switch($type){
				case "profile":
					if($user->getIsProfileDisplay() != SOYShop_User::PROFILE_IS_DISPLAY || !strlen($user->getProfileId())) $isThrow = true;
					break;
				case "order":
				case "mail":
				case "mailaddress":
				case "withdraw":
					if(SOYShopPluginUtil::checkIsActive("bulletin_board")) $isThrow = true;
					break;
				case "board":
					if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $isThrow = true;
					break;
				case "review":
					if(!SOYShopPluginUtil::checkIsActive("item_review")) $isThrow = true;
					break;
				case "point":
					if(!SOYShopPluginUtil::checkIsActive("common_point_base")) $isThrow = true;
					break;
				case "inquiry":
					if(!SOYShopPluginUtil::checkIsActive("inquiry_on_mypage")) $isThrow = true;
					break;
				case "payjp":
					if(!SOYShopPluginUtil::checkIsActive("payment_pay_jp")) $isThrow = true;
					break;
				case "card":
					SOYShopPlugin::load("soyshop.mypage.card");
					if(!SOYShopPlugin::invoke("soyshop.mypage.card")->hasOptionPage()) $isThrow = true;
					break;
				case "bank":
					if(!SOYShopPluginUtil::checkIsActive("transfer_information")) $isThrow = true;
					break;
				case "reserve":
					//if(!SOYShopPluginUtil::checkIsActive("reserve_calendar") || soyshop_get_mypage_id() != "bootstrap") $isThrow = true;
					break;
				default:
					//
			}
			if($isThrow) continue;

			$html[] = " <li class=\"nav-item\">";
			switch($type){
				case "mail":
					$cls = ($currentUri == $type) ? "nav-link active" : "nav-link";
					break;
				default:
					$cls = (strpos($currentUri, $type) === 0) ? "nav-link active" : "nav-link";
			}
			switch($type){
				case "profile":
					$html[] = "		<a class=\"" . $cls . "\" href=\"" . soyshop_get_mypage_url() . "/" . $type . "/" . $user->getProfileId() . "\">" . $label . "</a>";
					break;
				case "logout":	//soy2_check_tokenを追加
					$html[] = "		<a class=\"" . $cls . "\" href=\"" . soyshop_get_mypage_url() . "/" . $type . "\?soy2_token=" . soy2_get_token() . "\" onclick=\"return confirm('ログアウトしますか？');\">" . $label . "</a>";
					break;
				case "payjp":
				$html[] = "		<a class=\"" . $cls . "\" href=\"" . soyshop_get_mypage_url() . "/credit/payJp\">" . $label . "</a>";
				break;
				default:
					$html[] = "		<a class=\"" . $cls . "\" href=\"" . soyshop_get_mypage_url() . "/" . $type . "\">" . $label . "</a>";
			}
			$html[] = "	</li>";
		}

		$html[] = "</ul>";
		echo implode("\n", $html);
/**
  <label class="btn btn-secondary active">
    <input type="radio" name="options" id="option1" autocomplete="off" checked> Active
  </label>
  <label class="btn btn-secondary">
    <input type="radio" name="options" id="option2" autocomplete="off"> Radio
  </label>
  <label class="btn btn-secondary">
    <input type="radio" name="options" id="option3" autocomplete="off"> Radio
  </label>
**/
	}
	//ログインチェック
	// $isLoggedIn = $mypage->getIsLoggedin();
	//
	// //display area on loggedin
	// $obj->addModel("is_loggedin", array(
	// 	"visible" => $isLoggedIn,
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// //display area on isn't loggedin
	// $obj->addModel("not_loggedin", array(
	// 	"visible" => !$isLoggedIn,
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addForm("login_form", array(
	// 	"method" => "POST",
	// 	"action" =>  soyshop_get_mypage_url() . "/login",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addInput("mail", array(
	// 	"name" => "loginId",
	// 	"value" => "",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addInput("login_id", array(
	// 	"name" => "loginId",
	// 	"value" => "",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addInput("password", array(
	// 	"name" => "password",
	// 	"value" => "",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addCheckBox("auto_login", array(
	// 	"name" => "login_memory",
	// 	"elementId" => "login_memory",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// //name
	// $user = $mypage->getUser();
	// $obj->addLabel("user_name", array(
	// 	"text" => $user->getName(),
	// 	"visible" => ($isLoggedIn),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addLabel("point", array(
	// 	"text" => $user->getPoint(),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $timeLimit = null;
	// if($isLoggedIn && SOYShopPluginUtil::checkIsActive("common_point_base")){
	// 	$timeLimit = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->getPointObjByUserId($user->getId())->getTimeLimit();
	// }
	//
	// $obj->addModel("is_point_time_limit", array(
	// 	"visible" => (isset($timeLimit)),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addLabel("point_time_limit", array(
	// 	"text" => (isset($timeLimit)) ? date("Y-m-d", $timeLimit) : "",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addModel("is_profile_display", array(
	// 	"visible" => ($user->getIsProfileDisplay() == SOYShop_User::PROFILE_IS_DISPLAY && strlen($user->getProfileId()) > 0),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addLink("profile_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/profile/" . $user->getProfileId(),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addLink("register_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/register",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("login_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/login",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// //ログイン時に表示するリンク
	// $obj->addLink("top_link", array(
	// 	"link" => soyshop_get_mypage_top_url(),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addModel("is_favorite", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("common_favorite_item")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("favorite_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/favorite",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addModel("is_notice", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("common_notice_arrival")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("notice_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/notice",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("order_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/order",
	// 	"attr:id" => "mypage_link",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("edit_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/edit",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("password_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/password",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("address_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/address",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("download_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/download",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("mail_log_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/mail",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addModel("is_review", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("item_review")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("review_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/review",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addModel("is_point", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("common_point_base")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("point_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/point",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addModel("is_pay_jp", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("payment_pay_jp")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("pay_jp_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/credit/payJp",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addModel("is_inquiry", array(
	// 	"visible" => (SOYShopPluginUtil::checkIsActive("inquiry_on_mypage")),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	// $obj->addLink("inquiry_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/inquiry",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->addLink("withdraw_link", array(
	// 	"link" => soyshop_get_mypage_url() . "/withdraw",
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $config = SOYShop_ShopConfig::load();
	// $logoutLink = soyshop_get_mypage_url() . "/logout";
	// if($config->getDisplayPageAfterLogout() == 1){
	// 	$logoutLink .= "?r=" . soyshop_remove_get_value(rawurldecode($_SERVER["REQUEST_URI"]));
	// }
	//
	// $obj->addActionLink("logout_link", array(
	// 	"link" => $logoutLink,
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	//
	// //ログインしている時だけカートを表示したい場合用
	// $obj->addLink("cart_link", array(
	// 	"link" => soyshop_get_cart_url(),
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX
	// ));
	//
	// $obj->display();
}
