<?php

class UpperMenuPage extends CMSHTMLPageBase{

	const PARAM_KEY_CLEAR_CACHE = "clear_cache";
	const PARAM_KEY_TARGET_SITE = "site";

	function execute(){

		$this->clearCache();

		//sitePath
		$this->addLink("sitepath", array(
				"text" => "/" . UserInfoUtil::getSite()->getSiteId(),
				"link" => CMSUtil::getSiteUrl(),
				"style" => "text-decoration:none;color:black;"
		));

		$this->addLink("sitepath_link", array(
				"link" => CMSUtil::getSiteUrl(),
				"style" => "text-decoration:none;color:black;"
		));
		$this->addLabel("sitepath_text", array(
				"text" => "/" . UserInfoUtil::getSite()->getSiteId(),
		));

		$this->addLabel("sitename", array(
			"text" => UserInfoUtil::getSite()->getSiteName()
		));

		$this->addModel("biglogo", array(
			"src" => CMSUtil::getLogoFile(CMSUtil::MODE_SOYCMS)
		));

		//管理者名
		$this->addLabel("adminname", array(
			"text" => UserInfoUtil::getUserName(),
			"width" => 18,
			"title" => UserInfoUtil::getUserName(),
		));

		//記事管理者には表示しないもの
		$this->addModel("only_for_site_admin", array(
			"visible" => UserInfoUtil::hasSiteAdminRole(),
		));

		//config.ext.phpがあり、extモード用のディレクトリがあることを確認してからリンクを表示する
		$this->addModel("display_ext_link", array(
			"visible" => file_exists(dirname(SOY2HTMLConfig::PageDir()) . "/config.ext.php") && defined("EXT_MODE_DERECTORY_NAME") && file_exists(dirname(SOY2HTMLConfig::PageDir()) . "/" . EXT_MODE_DERECTORY_NAME),
		));
		$args = SOY2PageController::getArguments();
		$this->addLink("ext_link", array(
			"link" => (count($args)) ? SOY2PageController::createLink(SOY2PageController::getRequestPath().".".implode(".", $args))."?ext_mode" : null,
		));

		//アカウント情報
		$this->addLink("account_link", array(
			"link" => (defined("SOYCMS_ASP_MODE")) ?
						 SOY2PageController::createLink("Login.UserInfo")
						:SOY2PageController::createRelativeLink("../admin/index.php/Account")
		));

		//キャッシュ削除
		$site = UserInfoUtil::getSite();
		$param = self::PARAM_KEY_CLEAR_CACHE . "&". self::PARAM_KEY_TARGET_SITE ."=" . $site->getSiteId() . ( strlen($_SERVER['QUERY_STRING']) ? "&".$_SERVER['QUERY_STRING'] : "");
		$this->addActionLink("delete_cache_link",array(
				"link" => "?".$param,
		));

		//CMS管理へのリンク
		$this->createAdd("admin_link","HTMLLink",array(
				"link" => SOY2PageController::createRelativeLink("../admin/"),
		));
		$this->addModel("show_admin_link","HTMLLink",array(
				"visible" => !defined("SOYCMS_ASP_MODE") && !UserInfoUtil::hasOnlyOneRole()
		));

		/* サイドバーの表示・非表示 */
		$hideSideMenu = ( isset($_COOKIE["soycms-hide-side-menu"]) && $_COOKIE["soycms-hide-side-menu"] == "true" );
		$this->addModel("sidebar", array(
				"class" => $hideSideMenu ? "navbar-default sidebar sidebar-narrow" : "navbar-default sidebar",
		));


	}

	private function clearCache(){
		if(isset($_GET[self::PARAM_KEY_CLEAR_CACHE]) && isset($_GET[self::PARAM_KEY_TARGET_SITE]) && soy2_check_token()){
			$site = UserInfoUtil::getSite();
			if($_GET[self::PARAM_KEY_TARGET_SITE] == $site->getSiteId()){
				CMSUtil::unlinkAllIn($site->getPath() . ".cache/", true);
			}

			unset($_GET[self::PARAM_KEY_CLEAR_CACHE]);
			unset($_GET[self::PARAM_KEY_TARGET_SITE]);
			unset($_GET["soy2_token"]);
			$current = SOY2PageController::getRequestPath().".".implode(".",SOY2PageController::getArguments());
			$param = ( count($_GET) ? "?".http_build_query($_GET) : "");

			//拡張ポイントを追加
			CMSPlugin::callEventFunc("onClearCache");

			SOY2PageController::jump($current.$param);
		}

	}
}
