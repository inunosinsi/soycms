<?php
class BlogTemplateSettingStage extends StageBase{
	
	function BlogTemplateSettingStage() {
    	WebPage::WebPage();
    }
    
    //表示部分はここに書く
    function execute(){		
    	$this->createAdd("import","HTMLCheckbox",array(
    		"name" => "operation",
    		"value" => "import",
    		"label" => "ブログからインポート",
    		"selected" => true,
    		"type" => "radio"
    	));
    	
    	$this->createAdd("create","HTMLCheckbox",array(
    		"name" => "operation",
    		"value" => "create",
    		"label" => "新規に設定",
    		"type" => "radio"
    	));
    	
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	$blogPages = $dao->get();
    	
    	$blogList = array();
    	foreach($blogPages as $blog){
    		$blogList[$blog->getId()] = htmlspecialchars($blog->getTitle(), ENT_QUOTES, "UTF-8");
    	}
    	$blogPages = null;
    	$dao = null;
    	
    	$this->createAdd("blog_list","HTMLSelect",array(
    		"name" => "blog_id",
    		"options" => $blogList
    	));
    	
    }
    
    //次へが押された際の動作
    function checkNext(){
    	
    	$operation = @$_POST["operation"];
    	$blogId = @$_POST["blog_id"];
    	    
    	$tmpDir = $this->getTempDir();
    	
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	
    	if($operation == "import"){
    		
    		try{
    			$blogPage = $dao->getById($blogId);
    		}catch(Exception $e){
    			return false;
    		}
    			
    	}else{
    		$blogPage = new BlogPage();
    	}
    	
    	file_put_contents($tmpDir ."/". BlogPage::TEMPLATE_TOP , $blogPage->getTopTemplate());
		file_put_contents($tmpDir ."/". BlogPage::TEMPLATE_ARCHIVE , $blogPage->getArchiveTemplate());
		file_put_contents($tmpDir ."/". BlogPage::TEMPLATE_ENTRY , $blogPage->getEntryTemplate());
		
    	$this->wizardObj->template->setTemplate(array(
			BlogPage::TEMPLATE_TOP => array(
				"name" => "トップページ",
			),
			BlogPage::TEMPLATE_ENTRY => array(
				"name" => "記事ページ",
			),
			BlogPage::TEMPLATE_ARCHIVE => array(
				"name" => "アーカイブページ",
			)
		));
    	
    	return true;
    }
    
    //前へが押された際の動作
    function checkBack(){
    	return true;
    }
    
    //次のオブジェクト名、終了の際はEndStageを呼び出す
    function getNextObject(){
    	return "TemplateSettingStage";
    }
    
    //前のオブジェクト名、nullの場合は表示しない
    function getBackObject(){
    	return "StartStage";
    }

}
?>
