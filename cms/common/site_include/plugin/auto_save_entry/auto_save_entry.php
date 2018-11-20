<?php
AutoSaveEntryPlugin::register();
class AutoSaveEntryPlugin{

	const PLUGIN_ID = "auto_save_entry";

	private $period = 30;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"記事自動バックアッププラグイン",
			"description"=>"記事の簡易的なバックアップを定期的に行う",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".util.AutoSaveEntryUtil");

			//管理画面
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));

				CMSPlugin::addCustomFiledFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFiledFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}

			//設定画面
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this, "config_page"
			));
		}
	}

	function onEntryUpdate($arg){
		AutoSaveEntryUtil::deleteBackup(UserInfoUtil::getLoginId());
	}

	function onCallCustomField(){
		echo self::build();
	}

	function onCallCustomField_inBlog(){
		echo self::build();
	}

	private function build(){
		$loginId = UserInfoUtil::getLoginId();

		$html = array();

		$html[] = "<div class=\"section\">";
		$html[] = "	<p class=\"sub\">記事のバックアップ</p>";
		$html[] = "	<div id=\"labels\">";

		$style = (AutoSaveEntryUtil::checkBackupFile($loginId)) ? "display:inline;" : "display:none;";

		$html[] = "<span id=\"restoratoin_area\" style=\"" . $style . "\">";
		$html[] = "記事を復元する:<input type=\"button\" id=\"restore_from_backup\" value=\"復元\">";
		$html[] = "</span>";

		$html[] = "<span id=\"auto_save_entry_message\">記事のバックアップは実行されていません。</span>";

		$html[] = "	</div>";
		$html[] = "</div>";

		//現在ログインしているアカウント
		$html[] = "<input type=\"hidden\" id=\"current_login_id\" value=\"" . $loginId . "\">";

		//自動バックアップの間隔設定
		$html[] = "<input type=\"hidden\" id=\"save_period_seconds\" value=\"" . $this->getPeriod() . "\">";

		//自動バックアップのAjaxのPOST先
		$html[] = "<input type=\"hidden\" id=\"auto_save_action\" value=\"" . SOY2PageController::createLink("Entry.Editor.SaveEntry") . "\">";
		$html[] = "<input type=\"hidden\" id=\"restore_action\" value=\"" . SOY2PageController::createLink("Entry.Editor.LoadEntry") . "\">";

		$html[] = "<script>";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/post.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}

	function config_page(){
		include(dirname(__FILE__) . "/config/AutoSaveEntryConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("AutoSaveEntryConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getPeriod(){
		return $this->period;
	}
	function setPeriod($period){
		$this->period = $period;
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(AutoSaveEntryPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new AutoSaveEntryPlugin();
		}

		CMSPlugin::addPlugin(AutoSaveEntryPlugin::PLUGIN_ID, array($obj, "init"));
	}
}
