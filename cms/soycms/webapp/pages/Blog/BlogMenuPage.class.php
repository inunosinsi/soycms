<?php

class BlogMenuPage extends CMSHTMLPageBase{

	var $rule = array(
		"Blog" => "blog_top_link",
		"Blog.Entry" => "blog_entry_link",		
		"Blog.EntryList" => "blog_entry_list_link",
		"Blog.Config" => "blog_config_link",
		"Blog.Trackback" => "blog_trackback_link",
		"Blog.Comment" => "blog_comment_link",
		"Blog.Template" => "blog_template_link",
	);
	
	var $activeTab;

    function BlogMenuPage($args) {
    	
    	$id = $args[0];
    	
    	HTMLPage::HTMLPage();
    	
    	//リクエストされたパスからActiveなパスを取得
    	$requestPath = SOY2PageController::getRequestPath();
    	
    	foreach($this->rule as $rule => $tab){
    		if(preg_match("/".$rule."/",$requestPath)){
    			$this->activeTab = $tab;
    		}
    	}
    	
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	$blog = $dao->getById($id);
    	
    	
    	$this->createAdd("blog_name","HTMLLabel",array(
    		"html" => '<a href="'.SOY2PageController::createLink("Blog.List").'">'.CMSMessageManager::get("SOYCMS_BLOGPAGE_LIST").'</a>&nbsp;&gt;&nbsp;'.
    					'<a href="'.SOY2PageController::createLink("Blog.".$id).'" style="color:black;text-decoration:none;">' . $blog->getTitle() . '</a>'
    	));
    	
    	//上部メニューのリンク
    	/*$this->createAdd("blog_top_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.".$id),
    		"class" => $this->getMenuStatus("blog_top_link")
    	));*/
    	
    	$this->createAdd("blog_entry_list_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.EntryList.".$id),
    		"class" => $this->getMenuStatus("blog_entry_list_link")
    	));
    	
    	$this->createAdd("blog_config_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.Config.".$id),
    		"class" => $this->getMenuStatus("blog_config_link"),
    		"visible" => (UserInfoUtil::hasSiteAdminRole())
    	));
    	
    	$this->createAdd("blog_entry_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.Entry.".$id),
    		"class" => $this->getMenuStatus("blog_entry_link")
    	));
    	
    	$this->createAdd("blog_trackback_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.Trackback.".$id),
    		"class" => $this->getMenuStatus("blog_trackback_link")
    	));
    	
    	$this->createAdd("blog_comment_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.Comment.".$id),
    		"class" => $this->getMenuStatus("blog_comment_link")
    	));
    	
    	$this->createAdd("blog_template_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Blog.Template.".$id.".top"),
    		"class" => $this->getMenuStatus("blog_template_link"),
    		"visible" => (UserInfoUtil::hasSiteAdminRole())
    	));
    	
		$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($blog->getUri()) >0) ? $blog->getUri() ."/" : "" ) ;
    	$this->createAdd("blog_confirm","HTMLLink",array(
    		"link" => $pageUrl,
    		"visible" => $blog->isActive()
    	));
    	
    	$this->createAdd("blog_preview","HTMLLink",array(
    		"link" => SOY2PageController::createLink("Page.Preview.".$id),
    	));
    }
    
    function getMenuStatus($tabName){
    	
    	if($tabName == $this->activeTab){
    		return "active";
    	}else{
    		return "";
    	}
    }
}
?>