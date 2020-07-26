<?php
CustomIconFieldPlugin::registerPlugin();

class CustomIconFieldPlugin{

	private $label = "アイコンフィールド";
 	private $iconDirectory = "icons";
	private $labels = array();	//記事投稿画面でのフォームの表示の有無用

	function getId(){
		return SOYCMS_CUSTOM_ICON_FIELD_PLUGIN;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name" => "アイコンフィールド追加プラグイン",
			"description" => "記事編集画面にアイコン編集フィールドを追加します。",
			"author" => "株式会社Brassica",
			"url" => "https://brassica.jp/",
			"mail" => "soycms@soycms.net",
			"version" => "1.4"
		));
		CMSPlugin::addPluginConfigPage($this->getId(), array(
			$this, "config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
			CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));

			CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
			CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));

			CMSPlugin::setEvent('onEntryOutput', $this->getId(), array($this, "display"));
		}else{
			CMSPlugin::setEvent('onActive', $this->getId(), array($this, "createTable"));
		}
	}

	function display($arg){

		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$dao = new SOY2DAO();

		try{
			$result = $dao->executeQuery("select custom_icon_field from Entry where id = :id", array(":id" => $entryId));
		}catch(Exception $e){
			$result = array();
		}

		$icons = array();
		if(isset($result[0]["custom_icon_field"])){
			$icons_array = explode(",", $result[0]["custom_icon_field"]);

			foreach($icons_array as $str){
				if(strlen($str)){
					$icons[] = '<img src="' . htmlspecialchars(substr($str, 0), ENT_QUOTES, 'UTF-8') . '" >';
				}
			}
		}

		$htmlObj->addLabel("custom_icon_field", array(
			"html" => implode("\n", $icons),
			"soy2prefix" => "cms"
		));
	}

	function config_page($message = "")	{
		SOY2::import("site_include.plugin.soycms_custom_icon_field.config.CustomIconFieldConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomIconFieldConfigPage");
		$form->setPluginObj($this);
		$form->setMessage($message);
		$form->execute();
		return $form->getObject();
	}

	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		if(isset($_POST["custom_icon_field"]) && strlen($_POST["custom_icon_field"])){
			$fields = implode(",", array_unique(explode(",", $_POST["custom_icon_field"])));

			$dao = new SOY2DAO();
			try{
				$dao->executeQuery("update Entry set custom_icon_field = :custom where Entry.id = :id",
					array(
						":id" => $entry->getId(),
						":custom" => $fields
				));
			}catch(Exception $e){
				return false;
			}

			return true;
		}

		return false;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? $arg[0] : null;

		SOY2::import("site_include.plugin.soycms_custom_icon_field.component.IconFieldComponent");
		return IconFieldComponent::buildForm($entryId, $this->iconDirectory, $this->label, $this->labels);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? $arg[1] : null;

		SOY2::import("site_include.plugin.soycms_custom_icon_field.component.IconFieldComponent");
		return IconFieldComponent::buildForm($entryId, $this->iconDirectory, $this->label, $this->labels);
	}

	/**
	 * アイコンディレクトリを設定
	 */
	function setIconDirectory($dir){
		//先頭は必ず「/」
		if($dir[0] != "/") $dir = "/" . $dir;

		//末尾が/なら除く
		if($dir[strlen($dir) - 1] == "/") $dir = substr($dir, 0, strlen($dir) - 2);
		$this->iconDirectory = $dir;
	}

	function createTable(){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("alter table Entry add custom_icon_field text", array());
		}catch(Exception $e){
			//
		}

		//アイコン用のディレクトリを作成
		$dir = UserInfoUtil::getSiteDirectory(). "icons";
		if(!file_exists($dir)) mkdir($dir);

		return;
	}

	function getIconDirecotry(){
		return $this->iconDirectory;
	}

	function getLabels(){
		return $this->labels;
	}
	function setLabels($labels){
		$this->labels = $labels;
	}

	public static function registerPlugin(){
		define('SOYCMS_CUSTOM_ICON_FIELD_PLUGIN', "SOYCMS_CUSTOM_ICON_FIELD_PLUGIN");

		$obj = CMSPlugin::loadPluginConfig(SOYCMS_CUSTOM_ICON_FIELD_PLUGIN);
		if(is_null($obj)) $obj = new CustomIconFieldPlugin();
		CMSPlugin::addPlugin(SOYCMS_CUSTOM_ICON_FIELD_PLUGIN, array($obj, "init"));
	}
}
