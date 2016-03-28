<?php

class LimitationBrowseBlogEntryPlugin{
	
	const PLUGIN_ID = "LimitationBrowseBlogEntry";
	const FIELD_ID = "browse_password";
	
	private $entryAttributeDao;
	
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
			"version"=>"0.5"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
		
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"	
			));
			
			$this->entryAttributeDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		
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
		
		$allowBrowse = false;
		
		//詳細ページのみで動く。数字の時のみ処理を行う。
		if(get_class($htmlObj) == "BlogPage_EntryComponent" && is_numeric($entryId) && $entryId > 0){
			$obj = $this->getCustomFieldObject($entryId);
			
			//文字列がある時は条件付きと見なして、セッションに入れた値を確認しにいく
			if(!is_null($obj->getValue()) && strlen($obj->getValue())){
				
				$userSession = SOY2ActionSession::getUserSession();
				$allowBrowse = $userSession->getAttribute("allow_browse_" . $entryId);
				
				if(is_null($allowBrowse)) $allowBrowse = false;
				
			//文字列が登録されていない場合は無条件と見なして常にtrue
			}else{
				$allowBrowse = true;
			}
		}
		
		$htmlObj->addModel("allow_browse_entry", array(
			"soy2prefix" => "cms",
			"visible" => ($allowBrowse)
		));
		
		$htmlObj->addModel("not_allow_browse_entry", array(
			"soy2prefix" => "cms",
			"visible" => (!$allowBrowse)
		));
		
		$htmlObj->addForm("browse_password_form", array(
			"soy2prefix" => "cms"
		));
		
		$htmlObj->addInput("browse_password_input", array(
			"soy2prefix" => "cms",
			"name" => "browse_password",
			"value" => ""
		));
	}
	
	//パスワードのチェック
	function onPageOutput($obj){

		if(get_class($obj) == "CMSBlogPage"){
			
			//詳細ページで動きます。
			if(isset($obj->entry)){
				$entryId = (int)$obj->entry->getId();
				
				$attr = $this->getCustomFieldObject($entryId);
				
				if(soy2_check_token() && isset($_POST[self::FIELD_ID])){
					
					//入力したパスワードが一致しているならば
					if($_POST[self::FIELD_ID] == $attr->getValue()){
						$userSession = SOY2ActionSession::getUserSession();
						$userSession->setAttribute("allow_browse_" . $entryId, true);
					}	
					
					//元のページにリダイレクト
					header("Location:" . $_SERVER["REQUEST_URI"]);
				}
				
				$limitBrowse = (!is_null($attr->getValue()) && strlen($attr->getValue()));
				
				$obj->addModel("meta_robots", array(
					"soy2prefix" => "b_block",
					"attr:name" => "robots",
					"attr:content" => ($limitBrowse) ? "noindex,nofollow,noarchive" : "all"
				));
			}
		}		
	}
	
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		
		try{
			$this->entryAttributeDao->delete($entry->getId(), self::FIELD_ID);
		}catch(Exception $e){
			//
		}
		
		if(isset($_POST[self::FIELD_ID]) && strlen($_POST[self::FIELD_ID])){
			//新規作成の場合
			try{
				$obj = new EntryAttribute();
				$obj->setEntryId($entry->getId());
				$obj->setFieldId(self::FIELD_ID);
				$obj->setValue($_POST[self::FIELD_ID]);
				$this->entryAttributeDao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}
	
	function onEntryCopy($args){
		list($old, $new) = $args;
		
		try{
			$field = $this->entryAttributeDao->get($old, self::FIELD_ID);
		}catch(Exception $e){
			return;
		}
		
		try{
			$obj = new EntryAttribute();
			$obj->setEntryId($new);
			$obj->setFieldId(self::FIELD_ID);
			$obj->setValue($field->getValue());
			$this->entryAttributeDao->insert($obj);
		}catch(Exception $e){
				
		}
	}
	
	function onEntryRemove($args){
		foreach($args as $entryId){
			try{
				$this->entryAttributeDao->deleteByEntryId($entryId);
			}catch(Exception $e){
				
			}
		}
		
		return true;
	}
	
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return $this->buildForm($entryId);
	}
	
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return $this->buildForm($entryId);
	}
	
	function buildForm($entryId){		
		$obj = $this->getCustomFieldObject($entryId);
		
		$html = array();
		
		$html[] = '<div class="section custom_field">';
		$html[] = '<p class="sub">閲覧用パスワード(空の場合はパスワード制限なし)</p>';
		$html[] = '<input type="text" name="' . self::FIELD_ID . '" value="'. $obj->getValue() . '">';
		$html[] = '</div>';
			
		return implode("\n", $html);
	}
	
	function getCustomFieldObject($entryId){
		try{
			$obj = $this->entryAttributeDao->get($entryId, self::FIELD_ID);
		}catch(Exception $e){
			$obj = new EntryAttribute();
		}
		return $obj;
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
		if(!$obj){
			$obj = new LimitationBrowseBlogEntryPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
	
}
LimitationBrowseBlogEntryPlugin::register();
?>