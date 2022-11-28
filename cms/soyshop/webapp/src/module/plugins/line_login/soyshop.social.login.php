<?php
/*
 */
class LINELoginSocialLogin extends SOYShopSocialLoginBase{

	function buttonOnMyPageLogin(){
		/**
		 * @ToDo ログインしている時はボタンを表示しない
		 */

		$logic = SOY2Logic::createInstance("module.plugins.line_login.logic.LINELoginLogic");
		if(is_null($logic->getChannelId())) return null;

		//ボタン画像があるディレクトリ
		$dir = "/" . SOYSHOP_ID . "/themes/social/line/";

		$html = array();
		$html[] = "<a href=\"" . $logic->createAuthorizeLink() . "\">";
		$html[] = "<img src=\"" . $dir . "btn_login_base.png\" alt=\"LINE Login\">";
		$html[] = "</a>";

		return implode("", $html);
	}
}
SOYShopPlugin::extension("soyshop.social.login", "line_login", "LINELoginSocialLogin");
