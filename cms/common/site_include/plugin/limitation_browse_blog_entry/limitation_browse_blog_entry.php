<?php

class LimitationBrowseBlogEntryPlugin{

	const PLUGIN_ID = "LimitationBrowseBlogEntry";
	
	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ブログ記事閲覧制限プラグイン",
			"description"=>"ブログ記事を表示する際にパスワードの入力を要求する",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com",
			"mail"=>"info@n-i-agroinformatics.com",
			"version"=>"0.8"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.limitation_browse_blog_entry.util.LimitationBrowseUtil");

			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			//公開画面側
			if(defined("_SITE_ROOT_")){

				//記事の閲覧
				CMSPlugin::setEvent('onEntryOutput',self::PLUGIN_ID, array($this, "onEntryOutput"));
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));

			//管理画面側
			}else{
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', self::PLUGIN_ID, array($this, "onEntryCopy"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}
	}

	function onEntryOutput($arg){
		$htmlObj = $arg["SOY2HTMLObject"];
		$entryId = (int)$arg["entryId"];

		//詳細ページのみで動く。数字の時のみ処理を行う。
		$on = ($htmlObj instanceof EntryComponent && $entryId > 0) ? LimitationBrowseUtil::checkIsAllowBrowse($entryId) : true;
		
		$htmlObj->addModel("allow_browse_entry", array(
			"soy2prefix" => "cms",
			"visible" => ($on)
		));

		$htmlObj->addModel("not_allow_browse_entry", array(
			"soy2prefix" => "cms",
			"visible" => (!$on)
		));

		$htmlObj->addForm(LimitationBrowseUtil::FIELD_ID . "_form", array(
			"soy2prefix" => "cms"
		));

		$htmlObj->addInput(LimitationBrowseUtil::FIELD_ID . "_input", array(
			"soy2prefix" => "cms",
			"name" => LimitationBrowseUtil::FIELD_ID,
			"value" => ""
		));

		$htmlObj->addCheckBox(LimitationBrowseUtil::FIELD_ID . "_save", array(
			"soy2prefix" => "cms",
			"name" => LimitationBrowseUtil::FIELD_ID . "_save",
			"value" => 1
		));
	}

	//パスワードのチェック
	function onPageOutput($obj){
		if($obj instanceof CMSBlogPage && isset($obj->entry) && $obj->entry instanceof LabeledEntry){	//詳細ページで動きます。
			$entryId = (int)$obj->entry->getId();
			$pw = soycms_get_entry_attribute_value($entryId, LimitationBrowseUtil::FIELD_ID, "string");
			
			//入力したパスワードが一致しているならば
			if(soy2_check_token() && isset($_POST[LimitationBrowseUtil::FIELD_ID]) && $_POST[LimitationBrowseUtil::FIELD_ID] == $pw){
				$on = (isset($_POST[LimitationBrowseUtil::FIELD_ID . "_save"]) && $_POST[LimitationBrowseUtil::FIELD_ID . "_save"] == 1);
				LimitationBrowseUtil::save($entryId, $on);
				
				//元のページにリダイレクト
				header("Location:" . $_SERVER["REQUEST_URI"]);
			}

			$obj->addModel("meta_robots", array(
				"soy2prefix" => "b_block",
				"attr:name" => "robots",
				"attr:content" => (strlen($pw)) ? "noindex,nofollow,noarchive" : "all"
			));
		}
	}

	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		$v = (isset($_POST[LimitationBrowseUtil::FIELD_ID]) && strlen($_POST[LimitationBrowseUtil::FIELD_ID])) ? $_POST[LimitationBrowseUtil::FIELD_ID] : "";
		$attr = soycms_get_entry_attribute_object((int)$entry->getId(), LimitationBrowseUtil::FIELD_ID);
		$attr->setValue($v);
		soycms_save_entry_attribute_object($attr);
	}

	function onEntryCopy($args){
		list($old, $new) = $args;

		$oldAttrValue = soycms_get_entry_attribute_value($old, LimitationBrowseUtil::FIELD_ID, "string");
		$attr = soycms_get_entry_attribute_object($new, LimitationBrowseUtil::FIELD_ID);
		$attr->setValue($oldAttrValue);
		soycms_save_entry_attribute_object($attr);
	}

	function onEntryRemove($args){
		foreach($args as $entryId){
			$attr = soycms_get_entry_attribute_object($entryId, LimitationBrowseUtil::FIELD_ID);
			$attr->setValue(null);
			soycms_save_entry_attribute_object($attr);
		}

		return true;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildForm($entryId);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildForm($entryId);
	}

	private function _buildForm(int $entryId){
		$html = array();
		$html[] = '<div class="form-group">';
		$html[] = '<label>閲覧用パスワード(空の場合はパスワード制限なし)</label>';
		$html[] = '<input type="text" class="form-control" name="' . LimitationBrowseUtil::FIELD_ID . '" value="'. soycms_get_entry_attribute_value($entryId, LimitationBrowseUtil::FIELD_ID, "string") . '" style="width:50%;">';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	function config_page($message){
		include_once(dirname(__FILE__) . "/config/LimitationBrowseBlogEntryConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("LimitationBrowseBlogEntryConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new LimitationBrowseBlogEntryPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
}
LimitationBrowseBlogEntryPlugin::register();
