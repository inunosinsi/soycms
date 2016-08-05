<?php

class CreatePage extends CMSWebPageBase{

	function doPost(){
    	if(soy2_check_token()){
			$result = SOY2ActionFactory::createInstance("Page.CreateAction")->run();
			
			if($result->success()){
				$id = $result->getAttribute("id");
				$this->getFlashSession()->setAttribute("create","create");
				if($result->getAttribute("pageType") == Page::PAGE_TYPE_BLOG){
					
					//親Windowを詳細ページへ遷移
					echo "<html>";
					echo "<script type=\"text/javascript\">window.parent.location.href='".SOY2PageController::createLink("Blog.".$id)."?msg=create';</script>";
					echo "</html>";	
					
				}else{
					//親Windowを詳細ページへ遷移
					echo "<html>";
					echo "<script type=\"text/javascript\">window.parent.location.href='".SOY2PageController::createLink("Page.Detail.".$id)."?msg=create';</script>";
					echo "</html>";	
				}
				
				
				exit;
			}else{
				$this->addErrorMessage("PAGE_CREATE_FAILED");
				echo $result->getAttribute("failed");			
			}
    	}else{
			$this->addErrorMessage("PAGE_CREATE_FAILED");
    	}
	}

    function __construct() {
    	WebPage::WebPage();
    	
    	$parentPageList = $this->getParentPageList();
    	$this->createAdd("parent_page_select","HTMLSelect",array(
    		"name"=>"parentPageId",
			"options"=> $parentPageList,
			"visible" => count($parentPageList),
    		"indexOrder"=>true
    	));
    	
    	$this->createAdd("title","HTMLInput",array(
    		"name" => "title"    	
    	));
    	
    	$this->createAdd("uri","HTMLInput",array(
    		"name" => "uri"    	
    	));
    	
    	//ページの種類
    	$this->createAdd("page_type_default","HTMLCheckBox",array(
    		"name" => "pageType",
    		"label" => $this->getMessage("SOYCMS_NORMALPAGE"),
    		"selected" => true,
    		"value" => Page::PAGE_TYPE_NORMAL,
    	));
    	$this->createAdd("page_type_blog","HTMLCheckBox",array(
    		"name" => "pageType",
    		"label" => $this->getMessage("SOYCMS_BLOGPAGE"),
    		"value" => Page::PAGE_TYPE_BLOG,
    	));
    	$this->createAdd("page_type_mobile","HTMLCheckBox",array(
    		"name" => "pageType",
    		"label" => $this->getMessage("SOYCMS_MOBILEPAGE"),
    		"value" => Page::PAGE_TYPE_MOBILE,
    	)); 
    	$this->createAdd("page_type_application","HTMLCheckBox",array(
    		"name" => "pageType",
    		"label" => $this->getMessage("SOYCMS_SOYAPPPAGE"),
    		"value" => Page::PAGE_TYPE_APPLICATION,
    	));    	
    	    	
    	$this->createAdd("uri_prefix","HTMLLabel",array(
    		"text" => $this->getURIPrefix()
    	));
    	
		//テンプレート
    	$this->createAdd("normal_template_select","HTMLLabel",array(
    		"html" => $this->buildTemplateList(),
    		"name" => "template"
    	));
    	$this->createAdd("blog_template_select","HTMLLabel",array(
    		"html" => $this->buildBlogTemplateList(),
    		"name" => "_template"
    	));

		//公開設定
    	$this->createAdd("state_draft","HTMLCheckBox",array(
    		"selected"=>true,
    		"name"=>"isPublished",
    		"value"=>0,
    		"label"=>$this->getMessage("SOYCMS_DRAFT")
    	));
    	$this->createAdd("state_public","HTMLCheckBox",array(
    		"name"=>"isPublished",
    		"value"=>1,
    		"label"=>$this->getMessage("SOYCMS_PUBLISHED")
    	));
		
		//フォーム    	
	   	$this->createAdd("create_label","HTMLForm",array(
    		"target" => "_self"
    	));
    	
    	$result = SOY2ActionFactory::createInstance("Page.ListAction")->run();
    	$list = $result->getAttribute("list");
    	$ret_val = array();
    	foreach($list as $key => $val){
    		$ret_val[]=$val->getUri();
    	}
    	HTMLHead::addScript("pageConfirm",array(
			"script" => 'var pageList = '.json_encode($ret_val).';'
		));	
    	
    	
    }
    
    /**
     * テンプレートのIDをキーとする名前のリストを返す
     */
    function getTemplateList(){
    	$result = SOY2ActionFactory::createInstance("Template.TemplateListAction")->run();
    	
    	$list = $result->getAttribute("list");
    	
    	return $list;
    }
    
    /**
     * HTMLページのテンプレートのセレクトボックスを生成
     */
    function buildTemplateList(){
    	$dao = SOY2DAOFactory::create("cms.TemplateDAO");
    	$templates = $dao->get(Page::PAGE_TYPE_NORMAL,true);
    	$html = array();
    	$html[] = '<option value="">'.$this->getMessage("SOYCMS_ASK_TO_CHOOSE_PAGE_TEMPLATE_PACK").'</option>';    	
    	foreach($templates as $template){
    		if(!$template->isActive())continue;
    		
    		$html[] = '<optgroup label="'.$template->getName().'">';
    		
    		foreach($template->getTemplate() as $id => $array){
    			$html[] = '<option value="'.$template->getId()."/". $id .'">' . $array["name"] . '</option>';
    		}
    		
    		$html[] = "</optgroup>";
    	}
    	
    	return implode("\n",$html);
    }
    
    /**
     * ブログページのテンプレートのセレクトボックスを生成
     */
    function buildBlogTemplateList(){
    	$dao = SOY2DAOFactory::create("cms.TemplateDAO");
    	$templates = $dao->get(Page::PAGE_TYPE_BLOG,true);
    	$html = array();
    	$html[] = '<option value="">'.$this->getMessage("SOYCMS_ASK_TO_CHOOSE_PAGE_TEMPLATE_PACK").'</option>';    	
    	foreach($templates as $template){
    		if(!$template->isActive())continue;
    		$html[] = '<option value="'.$template->getId().'">' . $template->getName() . '</option>';
    	}
    	
    	return implode("\n",$html);
    }
    
    /**
     * 親ページのIDをキーとする名前のリストを返す
     */
    function getParentPageList(){
    	return SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"buildTree" => true
    	))->run()->getAttribute("PageTree");
    }
    
    /**
     * このページIDに対する呼び出しURIの定型部分を取得
     */
    function getURIPrefix(){
    	return CMSUtil::getSiteUrl();
    }
}
?>