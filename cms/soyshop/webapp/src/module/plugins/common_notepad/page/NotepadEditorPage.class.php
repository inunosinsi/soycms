<?php

class NotepadEditorPage extends WebPage{

	private $configObj;
	private $detailId;
	private $loginId;

	function __construct(){
		SOY2::import("module.plugins.common_notepad.util.NotepadUtil");
		SOY2::import("module.plugins.common_notepad.domain.SOYShop_NotepadDAO");
		$this->loginId = SOY2ActionSession::getUserSession()->getAttribute("loginid");
	}

	function doPost(){
		if(soy2_check_token()){
			$notepad = self::getNotepad();
			$notepad = SOY2::cast($notepad, $_POST["Notepad"]);

			if(!strlen($notepad->getTitle())) $notepad->setTitle("[無題]");

			if(is_null($notepad->getId())){
				try{
					$id = self::dao()->insert($notepad);
					$notepad->setId($id);
				}catch(Exception $e){
					var_dump($e);
				}
			}else{
				try{
					self::dao()->update($notepad);
				}catch(Exception $e){
					var_dump($e);
				}
			}


			$params = array();
			$params[] = "updated";
			if(isset($_GET["plugin_id"])) $params[] = "plugin_id=" . htmlspecialchars($_GET["plugin_id"], ENT_QUOTES, "UTF-8");

			NotepadUtil::deleteBackup($this->loginId);
			SOY2PageController::jump("Extension.Detail.common_notepad." . $notepad->getId() . "?" . implode("&", $params));
		}

		SOY2PageController::jump("Extension.Detail.common_notepad." . $this->detailId . "?failed");
	}

	function execute(){
		parent::__construct();

		$notepad = self::getNotepad();

		$this->addLabel("label", array(
			"text" => NotepadUtil::getLabel($notepad)
		));

		$pluginId = (isset($_GET["plugin_id"])) ? $_GET["plugin_id"] : null;
		$this->addLink("back_link", array(
			"link" => NotepadUtil::buildBackLink($notepad, $pluginId) . "#notepad_section"
		));

		self::_buildForm($notepad);
		self::_buildJsTags();
	}

	private function _buildForm(SOYShop_Notepad $notepad){
		$this->addForm("form");

		$this->addInput("title", array(
			"name" => "Notepad[title]",
			"value" => $notepad->getTitle()
		));

		$this->addTextArea("content", array(
			"name" => "Notepad[content]",
			"value" => $notepad->getContent()
		));

		DisplayPlugin::toggle("show_auto_load_button", NotepadUtil::checkBackupFile($this->loginId));
	}

	private function _buildJsTags(){
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
			"src" => SOY2PageController::createRelativeLink("./js/tools/datepicker.js")
		));
		// $this->addModel("data_picker_css", array(
		// 	"href" => SOY2PageController::createRelativeLink("./js/tools/soy2_date_picker.css")
		// ));
	}

	private function getNotepad(){
		try{
			return self::dao()->getById($this->detailId);
		}catch(Exception $e){
			$obj = new SOYShop_Notepad();
			if(isset($_GET["item_id"]) && is_numeric($_GET["item_id"])) $obj->setItemId($_GET["item_id"]);
			if(isset($_GET["category_id"]) && is_numeric($_GET["category_id"])) $obj->setCategoryId($_GET["category_id"]);
			if(isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) $obj->setUserId($_GET["user_id"]);
			return $obj;
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_NotepadDAO");
		return $dao;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}
