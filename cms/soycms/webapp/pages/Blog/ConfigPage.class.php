<?php

class ConfigPage extends CMSWebPageBase{
	
	var $id;
	var $page;
		
	function doPost(){
		
    	if(soy2_check_token()){
			$result = $this->run("Blog.UpdateBlogConfigAction",array(
				"id" => $this->id
			));
			
			if($result->success()){
				$this->addMessage("BLOG_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("BLOG_UPDATE_FAILED");
			}
    	}
		
		$this->jump("Blog.Config.".$this->id);		
	}
	
	function __construct($args) {
    	
    	$id = $args[0];
    	$this->id = $id;
    	
    	WebPage::WebPage();
    	
    	//新規作成してから来たときのメッセージ表示
    	if(isset($_GET["msg"]) && $_GET["msg"] == "create"){
    		$this->addMessage("BLOG_CREATE_SUCCESS");
    	}
    	
    	$result = $this->run("Blog.DetailAction",array("id"=>$id));
    	if(!$result->success()){
    		$this->addMessage("PAGE_DETAIL_GET_FAILED");
    		$this->jump("Page");
    		exit;
    	}
    	
    	
    	$page = $result->getAttribute("Page");
    	
    	
    	$this->createAdd("uri","HTMLInput",array(
    		"value"=>$page->getUri(),
    		"name"=>"uri"
    	));
    	
    	$this->createAdd("uri_prefix","HTMLLabel",array(
    		"text"=>$this->getURIPrefix($id)
    	));
    	
    	//アイコン設定
    	$this->createAdd("page_icon_show","HTMLImage",array(
			"src" => $page->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$page->getId().");"
		));
		
    	//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $id
		));
		
    	//見出しに現在編集しているページ名を表示
    	$this->createAdd("page_name","HTMLLabel",array("text"=>$page->getTitle()));
    	
    	$labels = $this->getLabels();
    	
    	//ラベルが無いときのメッセージ
    	include_once(dirname(__FILE__).'/_LabelBlankPage.class.php');
		$this->createAdd("no_label_message","_LabelBlankPage",array(
			"visible"=>(count($labels) == 0)
		));
		$this->createAdd("no_label_message2","_LabelBlankPage",array(
			"visible"=>(count($labels) == 0)
		));
		$this->createAdd("label_exists","HTMLModel",array(
			"visible"=>(count($labels) > 0)
		));
		$this->createAdd("label_exists2","HTMLModel",array(
			"visible"=>(count($labels) > 0)
		));

		/* フォームの部品　開始 */
    	$form = $this->create("page_detail_form","CMSFormBase");	
		
    	$form->createAdd("title","HTMLInput",array(
    		"value"=>$page->getTitle(),
    		"name"=>"title"
    	));
    	
    	$form->createAdd("description","HTMLTextArea",array(
    		"name" => "description",
    		"text" => $page->getDescription()
    	));
    	
    	$form->createAdd("parent_page","HTMLSelect",array(
    		"selected"=>$page->getParentPageId(),
    		"options"=>$this->getPageList(),
    		"indexOrder"=>true,
    		"name"=>"parentPageId"
    	));
    	
    	$form->createAdd("state_draft","HTMLCheckBox",array(
    		"selected"=>!$page->getIsPublished(),
    		"name"=>"isPublished",
    		"value"=>0,
    		"label" => CMSMessageManager::get("SOYCMS_DRAFT")
    	));
    	$form->createAdd("state_public","HTMLCheckBox",array(
    		"selected"=>$page->getIsPublished(),
    		"name"=>"isPublished",
    		"value"=>1,
    		"label" => CMSMessageManager::get("SOYCMS_PUBLISHED")
    	));
    	
    	$start = $page->getOpenPeriodStart();
		$end   = $page->getOpenPeriodEnd();
		
		
		//公開期間フォームの表示
		$form->createAdd("start_date","HTMLInput",array(
    		"value"=>(is_null($start)) ? "" : date('Y-m-d H:i:s',$start),
    		"name"=>"openPeriodStart"
    	));
    	$form->createAdd("end_date","HTMLInput",array(
    		"value"=>(is_null($end)) ? "" : date('Y-m-d H:i:s',$end),
    		"name"=>"openPeriodEnd"
    	));    	
    	$form->createAdd("open_period_show","HTMLLabel",array(
    		"html" => CMSUtil::getOpenPeriodMessage($start, $end)
    	));
    	
    	//カテゴリリスト
    	//TODO 表示変更
    	$form->createAdd("use_label_list","Blog_LabelList",array(
			"list" => $labels,
			"checkedList" => array($page->getBlogLabelId()),
			"name" => "blogLabelId",
			"idBase" => "BlogLabel_"
		));
		
		$form->createAdd("category_label_list","Blog_LabelList",array(
			"list" => $labels,
			"checkedList" => $page->getCategoryLabelList(),
			"name" => "categoryLabelList[]",
			"idBase" => "CategoryList"				
		));
		
		
		
		//ページ生成設定
		$form->createAdd("top_page_uri_prefix","HTMLLabel",array(
    		"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
    	));
    	
		$form->createAdd("entry_page_uri_prefix","HTMLLabel",array(
    		"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
    	));
    	
    	$form->createAdd("top_page_uri","HTMLInput",array(
			"value" => $page->getTopPageUri(),
			"name"=>"topPageUri",
			"validate" => 'validate-alphanumchar duplication'
		));
    	
		$form->createAdd("entry_page_uri","HTMLInput",array(
			"value" => $page->getEntryPageUri(),
			"name"=>"entryPageUri",
			"validate" => 'validate-alphanumchar duplication'
		));
		
		$form->createAdd("month_page_uri_prefix","HTMLLabel",array(
    		"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
    	));
    	
		$form->createAdd("month_page_uri","HTMLInput",array(
			"value" => $page->getMonthPageUri(),
			"name"=>"monthPageUri",
			"validate" => 'validate-alphanumchar duplication'
		));
		
		$form->createAdd("category_page_uri_prefix","HTMLLabel",array(
    		"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
    	));
    	
		$form->createAdd("category_page_uri","HTMLInput",array(
			"value" => $page->getCategoryPageUri(),
			"name"=>"categoryPageUri",
			"validate" => 'validate-alphanumchar duplication'
		));
		
		$form->createAdd("rss_page_uri_prefix","HTMLLabel",array(
    		"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
    	));
    	
		$form->createAdd("rss_page_uri","HTMLInput",array(
			"value" => $page->getRssPageUri(),
			"name"=>"rssPageUri",
			"validate" => 'validate-alphanumchar duplication'
		));
		
		//表示件数
		$form->createAdd("top_display_count","HTMLInput",array(
			"value" => $page->getTopDisplayCount(),
			"name"=>"topDisplayCount"
		));
		$form->createAdd("month_display_count","HTMLInput",array(
			"value" => $page->getMonthDisplayCount(),
			"name"=>"monthDisplayCount"
		));
		$form->createAdd("category_display_count","HTMLInput",array(
			"value" => $page->getCategoryDisplayCount(),
			"name"=>"categoryDisplayCount"
		));
		$form->createAdd("rss_display_count","HTMLInput",array(
			"value" => $page->getRssDisplayCount(),
			"name"=>"rssDisplayCount"
		));
		
		//表示順
		$form->createAdd("top_entry_sort", "HTMLSelect", array(
			"name" => "topEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getTopEntrySort()
		));
		$form->createAdd("month_entry_sort", "HTMLSelect", array(
			"name" => "monthEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getMonthEntrySort()
		));
		$form->createAdd("category_entry_sort", "HTMLSelect", array(
			"name" => "categoryEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getCategoryEntrySort()
		));
		
		//生成フラグ
		$form->createAdd("generateEntryFlag","HTMLCheckBox",array(
			"selected"=>$page->getGenerateEntryFlag(),
			"value"=>"1"
		));
		$form->createAdd("generateTopFlag","HTMLCheckBox",array(
			"selected"=>$page->getGenerateTopFlag(),
			"value"=>"1"
		));
		$form->createAdd("generateMonthFlag","HTMLCheckBox",array(
			"selected"=>$page->getGenerateMonthFlag(),
			"value"=>"1"
		));
		$form->createAdd("generateCategoryFlag","HTMLCheckBox",array(
			"selected"=>$page->getGenerateCategoryFlag(),
			"value"=>"1"
		));
		$form->createAdd("generateRssFlag","HTMLCheckBox",array(
			"selected"=>$page->getGenerateRssFlag(),
			"value"=>"1"
		));
		
		
		$form->createAdd("top_title_format","HTMLInput",array(
			"value"=>$page->getTopTitleFormat(),
			"name"=>"topTitleFormat"
		));
		
		$form->createAdd("entry_title_format","HTMLInput",array(
			"value"=>$page->getEntryTitleFormat(),
			"name"=>"entryTitleFormat"
		));
		
		$form->createAdd("month_title_format","HTMLInput",array(
			"value"=>$page->getMonthTitleFormat(),
			"name"=>"monthTitleFormat"
		));
		
		$form->createAdd("category_title_format","HTMLInput",array(
			"value"=>$page->getCategoryTitleFormat(),
			"name"=>"categoryTitleFormat"
		));
		
		$form->createAdd("feed_title_format","HTMLInput",array(
			"value"=>$page->getFeedTitleFormat(),
			"name"=>"feedTitleFormat"
		));		
		
		$this->add("page_detail_form",$form);
		/* フォームの部品　ここまで　*/
		
		//ブログメニュー
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($id)
		));
		
		//アイコンリスト
    	$this->createAdd("image_list","LabelIconList",array(
    		"list" => $this->getLabelIconList()
    	));
    	
    	HTMLHead::addScript("innerLink",array(
			"script" => 'var CreateLabelLink = "'.SOY2PageController::createLink("Entry.CreateNewLabel").'";'
		));
    	
    	//ラベルの追加
    	if(count($labels) != 0){
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ADD_NEW_LABEL"),"javascript:void(0);",false,"create_label();");
    	}else{
    		DisplayPlugin::hide("only_exists_label");
    	}

		//ラベルリストのCSS
		HTMLHead::addLink("editor.css",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		//ブログ設定のCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/blog/config.css")
		));

		//ツールボックス
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'"); 	
    	if($page->isActive() == Page::PAGE_ACTIVE){
    		$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($page->getUri()) >0) ? $page->getUri() ."/" : "" ) ;

    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_BLOGPAGE"),
    			$pageUrl,
    			false,
				"this.target = '_blank'"
			);
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_MONTHLYARCHIVEPAGE"),
    			$pageUrl.$page->getMonthPageUri()."/".date("Y/m"),
    			false,
				"this.target = '_blank'"
			);
			
			$categoryLabel = @$labels[array_shift($page->getCategoryLabelList())];
			if(!$categoryLabel)$categoryLabel = new Label();
			
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_CATEGORYARCHIVEPAGE"),
    			$pageUrl.$page->getCategoryPageUri()."/".rawurlencode($categoryLabel->getAlias()),
    			false,
				"this.target = '_blank'"
			);
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_RSS"),
    			$pageUrl.$page->getRssPageUri()."?feed=rss",
    			false,
				"this.target = '_blank'"
			);
    		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_ATOM"),
    			$pageUrl.$page->getRssPageUri()."?feed=atom",
    			false,
				"this.target = '_blank'"
			);
    	}
    	CMSToolBox::addPageJumpBox();

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
     * ラベル一覧を取得
     */
    function getLabels(){
    	$result = $this->run("Label.LabelListAction");
    	
		$labels = $result->getAttribute("list");
		return $labels;
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
    		
    		if(!preg_match('/^blog_/',$file))continue;
    		
    		$return[] = (object)array(
    			"filename" => $file,
    			"url" => CMS_PAGE_ICON_DIRECTORY_URL . $file,
    		);
    	}
    	
    	
    	return $return;    	
    }
}

class Blog_LabelList extends HTMLList{
	
	var $checkedList;
	var $name;
	var $idBase;
	
	function setCheckedList($list){
		$this->checkedList = $list;
	}
	
	function setName($name){
		$this->name = $name;
	}
	
	function setIdBase($idBase){
		$this->idBase = $idBase;
	}
	
	protected function populateItem($entity){
		$elementID = $this->idBase.$entity->getId();

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"      => $this->name,
			"value"     => $entity->getId(),
			"selected"  => in_array($entity->getId(),$this->checkedList),
			"elementId" => $elementID,
		));
		$this->createAdd("label_label","HTMLModel",array(
			"for" => $elementID,
		));
		$this->createAdd("label_caption","HTMLLabel",array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
			         ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";"
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));

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