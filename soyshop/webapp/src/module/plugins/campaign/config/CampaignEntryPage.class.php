<?php

class CampaignEntryPage extends WebPage{

	private $configObj;
	private $id;
	private $loginId;

	function __construct(){
		SOY2::import("module.plugins.campaign.util.CampaignUtil");
		SOY2::imports("module.plugins.campaign.domain.*");
		$this->loginId = SOY2ActionSession::getUserSession()->getAttribute("loginid");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["Campaign"]["postPeriodStart"]) && strlen($_POST["Campaign"]["postPeriodStart"]) > 0){
				$_POST["Campaign"]["postPeriodStart"] = soyshop_convert_timestamp($_POST["Campaign"]["postPeriodStart"], "start");
			}

			if(isset($_POST["Campaign"]["postPeriodEnd"]) && strlen($_POST["Campaign"]["postPeriodEnd"]) > 0){
				$_POST["Campaign"]["postPeriodEnd"] = soyshop_convert_timestamp($_POST["Campaign"]["postPeriodEnd"], "end");
			}

			$_POST["Campaign"]["isLoggedIn"] = (isset($_POST["Campaign"]["isLoggedIn"])) ? (int)$_POST["Campaign"]["isLoggedIn"] : 0;

			$old = self::getCampaign();
			$campaign = SOY2::cast($old, $_POST["Campaign"]);

			//更新
			if(isset($this->id)){
				try{
					self::dao()->update($campaign);
				}catch(Exception $e){
					var_dump($e);
				}
			//新規
			}else{
				try{
					$this->id = self::dao()->insert($campaign);
				}catch(Exception $e){
					var_dump($e);
				}
			}

			//バックアップの削除
			CampaignUtil::deleteBackup($this->loginId);
			$this->configObj->redirect("updated&mode=entry&id=" . $this->id);
		}
	}

	function execute(){
		$this->id = (isset($_GET["id"])) ? $_GET["id"] : null;
		parent::__construct();

		self::buildForm();

		$this->addLabel("insert_image_url", array(
			"text" => SOY2PageController::createLink("Site.File?display_mode=free")
		));

		$this->addLabel("insert_link_url", array(
			"text" => SOY2PageController::createLink("Site.Link?display_mode=free")
		));

		$this->addLabel("auto_save_url", array(
			"text" => SOY2PageController::createLink("Site.AutoSave.Save")
		));

		$this->addLabel("auto_load_url", array(
			"text" => SOY2PageController::createLink("Site.AutoSave.Load")
		));

		$this->addLabel("current_login_id", array(
			"text" => $this->loginId
		));

		$this->addLabel("auto_save_js", array(
			"html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/post.js") . "\n"
		));

		$this->addModel("data_picker_ja_js", array(
			"src" => SOY2PageController::createRelativeLink("./js/tools/datepicker-ja.js")
		));

		$this->addModel("data_picker_js", array(
			//"src" => SOY2PageController::createRelativeLink("./js/tools/soy2_date_picker.pack.js")
			"src" => SOY2PageController::createRelativeLink("./js/tools/datepicker.js")
		));
		// $this->addModel("data_picker_css", array(
		// 	"href" => SOY2PageController::createRelativeLink("./js/tools/soy2_date_picker.css")
		// ));
	}

	private function buildForm(){
		$campaign = self::getCampaign();

		$this->addForm("form");

		$this->addInput("title", array(
			"name" => "Campaign[title]",
			"value" => $campaign->getTitle()
		));

		$this->addTextArea("content", array(
			"name" => "Campaign[content]",
			"value" => $campaign->getContent()
		));

		DisplayPlugin::toggle("show_auto_load_button", CampaignUtil::checkBackupFile($this->loginId));

		$this->addCheckBox("is_logged_in", array(
			"name" => "Campaign[isLoggedIn]",
			"value" => SOYShop_Campaign::IS_LOGGED_IN,
			"selected" => $campaign->getIsLoggedIn(),
			"label" => "マイページにログインしている会員にのみ表示する"
		));

		$this->addInput("post_period_start", array(
			"name" => "Campaign[postPeriodStart]",
			"value" => soyshop_convert_date_string($campaign->getPostPeriodStart())
		));

		$this->addInput("post_period_end", array(
			"name" => "Campaign[postPeriodEnd]",
			"value" => soyshop_convert_date_string($campaign->getPostPeriodEnd())
		));

		$this->addCheckBox("no_open", array(
			"name" => "Campaign[isOpen]",
			"value" => SOYShop_Campaign::NO_OPEN,
			"selected" => (is_null($campaign->getIsOpen()) || $campaign->getIsOpen() == SOYShop_Campaign::NO_OPEN),
			"label" => "非公開"
		));

		$this->addCheckBox("is_open", array(
			"name" => "Campaign[isOpen]",
			"value" => SOYShop_Campaign::IS_OPEN,
			"selected" => ($campaign->getIsOpen() == SOYShop_Campaign::IS_OPEN),
			"label" => "公開"
		));
	}

	private function getCampaign(){
		try{
			return self::dao()->getById($this->id);
		}catch(Exception $e){
			return new SOYShop_Campaign();
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_CampaignDAO");
		return $dao;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
