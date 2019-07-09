<?php
GravatarPlugin::registerPlugin();

class GravatarPlugin {

	const PLUGIN_ID = "gravatar";

	private $thumbnail_size = 80;
	private $detail_size = 40;	//記事詳細ページで表示するサイズ
	private $gravatarListPageId;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "Gravatar連携プラグイン",
			"description"=> "アバター管理のWebサービスGravatarと連携するプラグインです",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::imports("site_include.plugin.gravatar.domain.*");

			//管理側
			if(!defined("_SITE_ROOT_")){
				//PageLogicファイルを削除したい ファイルをGravatarPageLogicにリネームしたため
				$path = SOY2::RootDir() . "site_include/plugin/gravatar/logic/PageLogic.class.php";
				if(file_exists($path)) unlink($path);

				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}else{
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
			CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
		}else{
			CMSPlugin::setEvent('onActive', $this->getId(), array($this, "createTable"));
		}
	}

	function config_page($message){
		SOY2::import("site_include.plugin.gravatar.config.GravatarConfigPage");
		$form = SOY2HTMLFactory::createInstance("GravatarConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onPageOutput($obj){
		$logic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic");

		//キャッシュファイルを読み込む
		if($logic->isCacheFile()){
			$list = $logic->readCacheFile();
		}else{
			 $accounts = $logic->getGravatars();
			 $list = array();
			 $error = false;
			 if(count($accounts)){
				 foreach($accounts as $account){
					 $values = $logic->getGravatarValuesByAccount($account);
					 if(count($values)){
						 $list[] = $values;
					 }else{
						 $error = true;
					 }
				 }
			}

			//キャッシュ
			if(!$error) $logic->generateCacheFile($list);
		}
		SOY2::import("site_include.plugin.gravatar.component.public.GravatarProfileListComponent");
		$obj->createAdd("gravatar_list", "GravatarProfileListComponent", array(
			"soy2prefix" => "p_block",
			"list" => $list,
			"thumbnailSize" => $this->thumbnail_size,
			"url" => SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarPageLogic")->getPageUrl($this->gravatarListPageId)
		));

		//ページャ
		SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
		$entryLogic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarEntryLogic");

		$args = $entryLogic->getArgs();
		if(isset($args[0])){
			$url = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarPageLogic")->getSiteUrl() . $_SERVER["SOYCMS_PAGE_URI"] . "/" . $args[0] . "/";
		}else{
			$url = null;
		}

		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$limit = PluginBlockUtil::getLimitByPageId((int)$_SERVER["SOYCMS_PAGE_ID"]);
		if(is_null($limit)) $limit = 100000;

		$current = (isset($args[1]) && strpos($args[1], "page-") === 0) ? (int)str_replace("page-", "", $args[1]) : 0;
		$last_page_number = (int)ceil($entryLogic->getTotalEachAuthorEntries() / $limit);

		$obj->createAdd("g_pager", "BlockPluginPagerComponent", array(
			"list" => array(),
			"current" => $current,
			"last"	 => $last_page_number,
			"url"		=> $url,
			"soy2prefix" => "p_block",
		));

		$obj->addModel("g_has_pager", array(
				"soy2prefix" => "p_block",
				"visible" => ($last_page_number >1)
		));
		$obj->addModel("g_no_pager", array(
				"soy2prefix" => "p_block",
				"visible" => ($last_page_number <2)
		));

		$obj->addLink("g_first_page", array(
				"soy2prefix" => "p_block",
				"link" => $url,
		));

		$obj->addLink("g_last_page", array(
				"soy2prefix" => "p_block",
				"link" => $url . "page-" . ($last_page_number - 1),
		));

		$obj->addLabel("g_current_page", array(
				"soy2prefix" => "p_block",
				"text" => max(1, $current + 1),
		));

		$obj->addLabel("g_pages", array(
				"soy2prefix" => "p_block",
				"text" => $last_page_number,
		));
	}

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$obj = self::getAttrValueByEntryId($entryId);
		$logic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic");
		$account = $logic->getGravatarByMailAddress($obj->getValue());
		$values = $logic->getGravatarValuesByAccount($account);

		$url = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarPageLogic")->getPageUrl($this->gravatarListPageId);

		$htmlObj->addImage("gravatar_thumbnail", array(
			"soy2prefix" => "gra",
			"src" => (isset($values["thumbnailUrl"])) ? $values["thumbnailUrl"] . ".jpg?s=" . $this->detail_size : ""
		));

		//gra:id="thumbnail"が使用できなかったので、gra:id="thumbnail_src"を作成
		$htmlObj->addImage("thumbnail_src", array(
			"soy2prefix" => "gra",
			"src" => (isset($values["thumbnailUrl"])) ? $values["thumbnailUrl"] . ".jpg?s=" . $this->detail_size : ""
		));

		$htmlObj->addLink("gravatar_link", array(
			"soy2prefix" => "gra",
			"link" => (isset($values["profileUrl"])) ? $values["profileUrl"] : ""
		));

		$htmlObj->addLink("list_page_link", array(
			"soy2prefix" => "gra",
			"link" => (isset($values["name"])) ? $url . $account->getName() : null
		));

		$htmlObj->addLabel("name", array(
			"soy2prefix" => "gra",
			"text" => (isset($values["name"])) ? $values["name"] : ""
		));

		$htmlObj->addLabel("reading", array(
			"soy2prefix" => "gra",
			"text" => (isset($values["reading"])) ? $values["reading"] : ""
		));

		$htmlObj->addLabel("display_name", array(
			"soy2prefix" => "gra",
			"text" => (isset($values["displayname"])) ? $values["displayname"] : ""
		));

		$htmlObj->addLabel("fullname", array(
			"soy2prefix" => "gra",
			"text" => (isset($values["fullname"])) ? $values["fullname"] : ""
		));

		$htmlObj->addLabel("about_me", array(
			"soy2prefix" => "gra",
			"html" => (isset($values["aboutMe"])) ? nl2br(htmlspecialchars($values["aboutMe"], ENT_QUOTES, "UTF-8")) : ""
		));
	}

	function onLoad(){
		return SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarEntryLogic")->getEachAuthorEntries();
	}

	function returnPluginId(){
			return self::PLUGIN_ID;
	}

	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		$arg = SOY2PageController::getArguments();
		$mailAddress = (isset($_POST["gravatar"])) ? $_POST["gravatar"] : null;

		$dao = self::dao();
		$obj = new EntryAttribute();
		$obj->setEntryId($entry->getId());
		$obj->setFieldId(self::PLUGIN_ID);
		$obj->setValue($mailAddress);

		try{
			$dao->insert($obj);
		}catch(Exception $e){
			try{
				$dao->update($obj);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){

		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	private function buildFormOnEntryPage($entryId){
		$html = array();
		$html[] = '<div class="section custom_field">';
		$html[] = '<p class="sub"><label for="gravatar">著者(Gravatar)</label></p>';
		$html[] = '<select name="gravatar">';
		$html[] = '<option></option>';

		$accounts = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic")->getGravatars();
		if(count($accounts)){
			$obj = self::getAttrValueByEntryId($entryId);
			foreach($accounts as $account){
				//管理画面からアカウントを消してしまっても良いように、メールアドレスで登録する
				$mailAddress = trim($account->getMailAddress());
				if($obj->getValue() == $mailAddress){
					$html[] = '<option value="' . $mailAddress . '" selected="selected">' . $account->getName() . '</option>';
				}else{
					$html[] = '<option value="' . $mailAddress . '">' . $account->getName() . '</option>';
				}
			}
		}

		$html[] = '</select>';
		$html[] = '</div>';
		return implode("\n", $html);
	}

	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM GravatarAccount", array());
			return;//テーブル作成済み
		}catch(Exception $e){
		}

		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");

		try{
			$dao->executeUpdateQuery($sql, array());
		}catch(Exception $e){
			//
		}
	}

	function getThumbnailSize(){
		return $this->thumbnail_size;
	}
	function setThumbnailSize($thumbnailSize){
		$this->thumbnail_size = $thumbnailSize;
	}

	function getDetailSize(){
		return $this->detail_size;
	}
	function setDetailSize($detailSize){
		$this->detail_size = $detailSize;
	}

	function getGravatarListPageId(){
		return $this->gravatarListPageId;
	}
	function setGravatarListPageId($gravatarListPageId){
		$this->gravatarListPageId = $gravatarListPageId;
	}

	private function getAttrValueByEntryId($entryId){
		try{
			return self::dao()->get($entryId, self::PLUGIN_ID);
		}catch(Exception $e){
			return new EntryAttribute();
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new GravatarPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
