<?php

class DetailPage extends CMSWebPageBase{

	var $id;

	function doPost(){

    	if(soy2_check_token()){
			$result = $this->run("Page.UpdateAction",array(
				"id" => $this->id,
				"updateConfig" => true
			));

			if($result->success()){
				$this->addMessage("PAGE_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("PAGE_UPDATE_FAILED");
			}
			$this->jump("Page.Detail.".$this->id);
			exit;
    	}
	}

    function DetailPage($arg){


    	$this->id = @$arg[0];

    	WebPage::WebPage();

    	//新規作成してから来たときのメッセージ表示
    	if(isset($_GET["msg"]) && $_GET["msg"] == "create"){
    		$this->addMessage("PAGE_CREATE_SUCCESS");
    		$this->jump("Page.Detail.".$this->id);
    	}

    	if(is_null($this->id)){
    		$this->jump("Page");
    	}

    	$id = $this->id;


    	$page = $this->getPageObject($id);

    	//ブログだった場合はブログページへ
    	if($page->getPageType() == Page::PAGE_TYPE_BLOG){
    		$this->jump("Blog.".$id);
    	}

    	//mobileページだった時はそっちに
    	if($page->getPageType() == Page::PAGE_TYPE_MOBILE){
    		$this->jump("Page.Mobile.Detail.".$id);
    	}

    	//Applicationページだった場合はそっちに
    	if($page->getPageType() == Page::PAGE_TYPE_APPLICATION){
    		$this->jump("Page.Application.Detail.".$id);
    	}

    	//404ページだった場合の処理
    	if($page->getPageType() == Page::PAGE_TYPE_ERROR){
    		DisplayPlugin::hide("openperiod_section");
    	}else{
    		DisplayPlugin::hide("error_submit_button");
    	}

    	$this->createAdd("title","HTMLInput",array(
    		"value"=>$page->getTitle(),
    		"name"=>"title"
    	));

    	$this->createAdd("uri","HTMLInput",array(
    		"value"=>$page->getUri(),
    		"name"=>"uri"
    	));

    	$this->createAdd("page_icon_show","HTMLImage",array(
			"src" => $page->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$page->getId().");"
		));

		$this->createAdd("page_icon","HTMLInput",array(
			"value"=>$page->getIcon()
		));

    	$this->createAdd("title_format","HTMLInput",array(
    		"value"=>$page->getPageTitleFormat(),
    		"name"=>"pageTitleFormat"
    	));


    	$this->createAdd("uri_prefix","HTMLLabel",array(
    		"text"=>$this->getURIPrefix($id)
    	));

    	$this->createAdd("parent_page","HTMLSelect",array(
    		"selected"=>$page->getParentPageId(),
    		"options"=>$this->getPageList(),
    		"indexOrder"=>true,
    		"name"=>"parentPageId"
    	));

		//CSS保存のボタン
		$this->createAdd("save_css_button", "HTMLModel", array(
    		"visible" => function_exists("json_encode")
		));

    	//template保存のボタン追加
    	$this->createAdd("save_template_button","HTMLModel",array(
    		"id" => "save_template_button",
    		"onclick" => "javascript:save_template('".SOY2PageController::createLink("Page.Editor.SaveTemplate." . $page->getId())."',this);",
    		"visible" => function_exists("json_encode")
    	));

    	$this->createAdd("template","HTMLTextArea",array(
    		"text"=>$page->getTemplate(),
    		"name"=>"template"
    	));

    	$this->createAdd("template_editor","HTMLModel",array(
    		"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
    		"onload" => "init_template_editor();"
    	));

    	$this->createAdd("state_draft","HTMLCheckBox",array(
    		"selected"=>!$page->getIsPublished(),
    		"name"=>"isPublished",
    		"value"=>0,
    		"label"=>$this->getMessage("SOYCMS_DRAFT")
    	));
    	$this->createAdd("state_public","HTMLCheckBox",array(
    		"selected"=>$page->getIsPublished(),
    		"name"=>"isPublished",
    		"value"=>1,
    		"label"=>$this->getMessage("SOYCMS_PUBLISHED")
    	));

    	$start = $page->getOpenPeriodStart();
		$end   = $page->getOpenPeriodEnd();


		//公開期間フォームの表示
		$this->createAdd("start_date","HTMLInput",array(
    		"value"=>(is_null($start)) ? "" : date('Y-m-d H:i:s',$start),
    		"name"=>"openPeriodStart"
    	));
    	$this->createAdd("end_date","HTMLInput",array(
    		"value"=>(is_null($end)) ? "" : date('Y-m-d H:i:s',$end),
    		"name"=>"openPeriodEnd"
    	));

    	$this->createAdd("open_period_show","HTMLLabel",array(
    		"html" => CMSUtil::getOpenPeriodMessage($start, $end)
    	));
    	HTMLHead::addScript("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));

		HTMLHead::addScript("TemplateEditor",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js")
		));

		HTMLHead::addLink("editor",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/editor/editor.css")
		));

		HTMLHead::addLink("section",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/form.css")
		));

		HTMLHead::addLink("form",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));




		$this->createAdd("page_detail_form","HTMLForm",array(
			"name" => "main_form"
		));

		//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $id
		));

    	//見出しに現在編集しているページ名を表示
    	$this->createAdd("page_name","HTMLLabel",array("text"=>$page->getTitle()));
    	HTMLHead::addScript("cssmenu",array(
				"type" => "text/JavaScript",
				"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js")
			));

		//CSS保存先URLをJavaScriptに埋め込みます
		HTMLHead::addScript("cssurl",array(
			"type"=>"text/JavaScript",
			"script"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
					  'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
					  'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));

		//絵文字入力用
		if(SOYCMSEmojiUtil::isInstalled()){
			HTMLHead::addScript("mceSOYCMSEmojiURL",array(
				"script" => 'var mceSOYCMSEmojiURL = "'.SOYCMSEmojiUtil::getEmojiInputPageUrl().'";'
			));
		}

		//アイコンリスト
    	$this->createAdd("image_list","LabelIconList",array(
    		"list" => $this->getLabelIconList()
    	));


    	//ファイルツリーをつかいます。
    	CMSToolBox::enableFileTree();

    	CMSToolBox::addLink($this->getMessage("SOYCMS_CREATE_NEW_WEBPAGE"),SOY2PageController::createLink("Page.Create"),true);
    	CMSToolBox::addLink($this->getMessage("SOYCMS_TEMPLATE_HISTORY"),SOY2PageController::createLink("Page.TemplateHistory.".$this->id),true);
    	CMSToolBox::addLink($this->getMessage("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'");
    	if($page->isActive() == Page::PAGE_ACTIVE){
    		CMSToolBox::addLink($this->getMessage("SOYCMS_SHOW_WEBPAGE"),CMSUtil::getSiteUrl().$page->getUri(),false,"this.target = '_blank'");
    	}
    	CMSToolBox::addLink($this->getMessage("SOYCMS_DOWNLOAD_TEMPLATE"),SOY2PageController::createLink("Page.ExportTemplate.".$this->id),false);
    	CMSToolBox::addLink($this->getMessage("SOYCMS_APPLY_WEBPAGE_TEMPLATEPACK"),SOY2PageController::createLink("Page.ApplyTemplate.".$page->getId()),true);
    	CMSToolBox::addPageJumpBox();


    	//短縮URLのフィールドの呼び出し
    	$res = CMSPlugin::callLocalPluginEventFunc("onPageEdit","UrlShortener",array("page" => $this));

		if(count($res) > 0){
			$display = ($res["UrlShortener"]);
			//createAdd "url_shortener_input" in UrlShortener::onPageEdit
		}else{
			$display = false;
		}

    	//短縮URLの表示
    	$this->createAdd("url_shortener_display", "HTMLModel",array(
    		"visible" => $display
    	));

    }

    /**
     * このページIDに対する呼び出しURIの定型部分を取得
     */
    function getURIPrefix($pageId){
    	return CMSUtil::getSiteUrl();
    }

    /**
     * IDに対するページオブジェクトを取得する
     */
    function getPageObject($id){
    	return SOY2ActionFactory::createInstance("Page.DetailAction",array(
    		"id" => $id
    	))->run()->getAttribute("Page");
    }

    /**
     * ページIDをキーとするリストを取得
     */
    function getPageList(){
    	return SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"buildTree" => true
    	))->run()->getAttribute("PageTree");
    }

    /**
     * ページに使えるアイコンの一覧を返す
     */
    function getLabelIconList(){

    	$dir = CMS_PAGE_ICON_DIRECTORY;

    	$files = scandir($dir);

    	$return = array();

    	foreach($files as $file){
    		if($file[0] == ".")continue;

    		if(!preg_match('/^page_/',$file))continue;

    		$return[] = (object)array(
    			"filename" => $file,
    			"url" => CMS_PAGE_ICON_DIRECTORY_URL . $file,
    		);
    	}


    	return $return;
    }


}

class LabelIconList extends HTMLList{

	function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:setChangeLabelIcon('".$entity->filename."','".$entity->url."');"
		));
	}
}

?>