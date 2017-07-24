<?php
GravatarPlugin::registerPlugin();

class GravatarPlugin {

  const PLUGIN_ID = "gravatar";

  private $thumbnail_size = 80;
  private $detail_size = 40;  //記事詳細ページで表示するサイズ
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
			"version"=>"0.1"
		));

    CMSPlugin::addPluginConfigPage($this->getId(),array(
      $this,"config_page"
    ));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
      SOY2::imports("site_include.plugin.gravatar.domain.*");

      //管理側
      if(!defined("_SITE_ROOT_")){
        CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
      }else{
        CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
        CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
      }

      CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
      CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));

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
      "url" => SOY2Logic::createInstance("site_include.plugin.gravatar.logic.PageLogic")->getPageUrl($this->gravatarListPageId)
    ));
	}

  function onEntryOutput($arg){
		$entryId = $arg["entryId"];
    $htmlObj = $arg["SOY2HTMLObject"];

    $obj = self::getAttrValueByEntryId($entryId);
    $logic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic");
    $account = $logic->getGravatarByMailAddress($obj->getValue());
    $values = $logic->getGravatarValuesByAccount($account);

    $url = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.PageLogic")->getPageUrl($this->gravatarListPageId);

    $htmlObj->addImage("thumbnail", array(
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
    $args = self::getArgs();
    if(!isset($args[0])) return array();

    $alias = trim(htmlspecialchars($args[0], ENT_QUOTES, "UTF-8"));
    $account = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic")->getGravatarByAlias($alias);
    if(!strlen($account->getMailAddress())) return array();

    //検索結果ブロックプラグインのUTILクラスを利用する
    SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

    $pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
    $template = PluginBlockUtil::getTemplateByPageId($pageId);
    if(!strlen($template)) return array();

    $block = PluginBlockUtil::getBlockByPageId($pageId);
    if(is_null($block)) return array();

    //ラベルIDを取得とデータベースから記事の取得件数指定
    $count = null;
    if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
        if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
            if(isset($ctmp[1]) && is_numeric($ctmp[1])) $count = (int)$ctmp[1];
        }
    }else{
        return array();
    }

    //gravatarのメールアドレスに紐付いた記事を取得
    $entryDao = SOY2DAOFactory::create("cms.EntryDAO");
    $sql = "SELECT ent.* FROM Entry ent ".
            "INNER JOIN EntryAttribute attr ".
            "ON ent.id = attr.entry_id ".
            "WHERE attr.entry_field_id = :pluginId ".
            "AND attr.entry_value = :email ".
            "AND ent.openPeriodStart < :now ".
            "AND ent.openPeriodEnd > :now ".
            "AND ent.isPublished > " . Entry::ENTRY_NOTPUBLIC . " ".
            "ORDER BY ent.cdate DESC ";

    if(isset($count) && $count > 0){
      $sql .= "LIMIT " . $count;

      //ページャ
      if(isset($args[1]) && strpos($args[1], "page-") === 0){
        $pageNumber = (int)str_replace("page-", "", $args[1]);
        if($pageNumber > 0){
          $offset = $count * $pageNumber;
          $sql .= " OFFSET " . $offset;
        }
      }
    }

    try{
      $res = $entryDao->executeQuery($sql, array(":pluginId" => self::PLUGIN_ID, ":email" => $account->getMailAddress(), ":now" => time()));
    }catch(Exception $e){
      return array();
    }

    if(!count($res)) return array();

    $entries = array();
    foreach($res as $v){
      $entries[] = $entryDao->getObject($v);
    }


    return $entries;
  }

  private function getArgs(){
    if(!isset($_SERVER["PATH_INFO"])) return array();
    $argsRaw = rtrim(str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $_SERVER["PATH_INFO"]), "/");
    return explode("/", $argsRaw);
  }

  function returnPluginId(){
      return self::PLUGIN_ID;
  }

  function onEntryUpdate($arg){
		$entry = $arg["entry"];

		$arg = SOY2PageController::getArguments();
		$entryId = @$arg[0];

    $mailAddress = (isset($_POST["gravatar"])) ? $_POST["gravatar"] : null;

    $dao = self::dao();
    $obj = new EntryAttribute();
    $obj->setEntryId($entryId);
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
