<?php

class EditHtaccessPage extends CMSUpdatePageBase {

	const FILENAME = ".htaccess";
	var $id;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){
			if($this->id == $_POST["site_id"] && $this->saveFile($_POST["contents"])){
				$this->addMessage("UPDATE_SUCCESS");
			}

			$this->reload();
		}

		exit;
	}

	function __construct($args) {
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Site");
		}
		$id = (isset($args[0])) ? $args[0] : null;
		$this->id = $id;


		parent::__construct();

		$site = $this->getSite();

		if(!is_writable($site->getPath() . self::FILENAME)){
			$this->addErrorMessage("SOYCMS_NOT_WRITABLE");
		}

		$this->addLabel("site_name", array(
				"text" => $site->getSiteName()
		));

		$this->buildSubMenu();
		$this->buildForm($site);
		$this->showDefault($site);
		$this->showMessage();
		$this->showBackupFiles($site->getPath() . self::FILENAME);
	}

	private function buildForm($site){

		$filepath = $site->getPath() . self::FILENAME;

		$this->addForm("update_site_form", array(
			"disabled" => !is_writable($filepath)
		));

		$this->addInput("site_id", array(
			"type"  => "hidden",
			"name"  => "site_id",
			"value" => $this->id
		));

		$content= is_readable($filepath) ? file_get_contents($filepath) : "";
		$this->addTextArea("contents", array(
			"name"  => "contents",
			"value" => $content,
			"rows" => count(explode("\n", $content)),
			"disabled" => !is_writable($filepath)
		));

		$this->addInput("button", array(
			"value"	 => CMSMessageManager::get("SOYCMS_SAVE"),
			"disabled" => !is_writable($filepath)
		));
	}

	private function buildSubMenu(){
		$this->addLink("detail_link", array(
				"link" => SOY2PageController::createLink("Site.Detail." . $this->id),
		));

	}

	private function showDefault($site){

		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteCreateLogic");

		$this->addTextArea("default_contents", array(
				"name"  => "default_contents",
				"value" => $logic->getHtaccess($site->getSiteId(), $site->getIsDomainRoot()),
				"readonly" => true,
		));
	}

	private function showBackupFiles($filepath){
		$list = CMSUtil::getBackupList($filepath);
		$this->addModel("has_backup",array(
				"visible" => count($list),
		));
		$this->createAdd("backup_file_list","BackupFileList",array(
				"list" => $list,
		));
	}

	private function showMessage(){
		$messages = CMSMessageManager::getMessages();
		$errors= CMSMessageManager::getErrorMessages();
		$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => count($messages),
		));
		$this->addLabel("error", array(
			"text" => implode($errors),
			"visible" => count($errors),
		));
		$this->addModel("has_message_or_error",array(
				"visible" => count($messages) || count($errors),
		));
	}

	private function saveFile($contents){
		$site = $this->getSite();
		return file_put_contents($site->getPath() . self::FILENAME, $contents);
	}

	private function getSite(){
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Site");
		}

		return $site;
	}

}

class BackupFileList extends HTMLList{
	protected function populateItem($filepath, $key, $counter){
		$this->addModel("heading",array(
				"id" => "heading_".$counter,
		));

		$this->addLink("collapse-link",array(
				"link" => "#collapse_".$counter,
		));
		$this->addLabel("filename",array(
				"text" => basename($filepath),
		));

		$this->addLabel("filemtime",array(
				"text" => date("Y-m-d H:i:s", filemtime($filepath)),
		));

		$this->addModel("collapse-body",array(
				"aria-labelledby" => "heading_".$counter,
				"class" => "panel-collapse collapse" . ( $counter ==1 ? " in" : "" ),
				"id" => "collapse_".$counter,
		));

		$content= is_readable($filepath) ? file_get_contents($filepath) : "";
		$this->addTextArea("contents", array(
				"name"  => "contents",
				"value" => $content,
				"rows" => count(explode("\n",$content)),
				"readonly" => true,
				"style" => "background: white;"
		));
	}
}
