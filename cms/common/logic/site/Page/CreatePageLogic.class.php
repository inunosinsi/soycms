<?php

class CreatePageLogic extends SOY2LogicBase{
	
    function create(Page $page,$tempname = ""){
    	
    	switch($page->getPageType()){
    		case Page::PAGE_TYPE_BLOG:
    			
    			//テンプレートを指定していた場合
    			if(strlen($tempname) != 0){
			    	//$contents = $this->getTemplateContents($tempname."/");
	    			$templateDAO = SOY2DAOFactory::create("cms.TemplateDAO");
	    			$template = $templateDAO->getById($tempname);
	    			$contents = $template->getTemplateContent();
    			}else{
    				$contents = $this->getDefaultTemplateContents($page->getPageType());
    			}

				$contents = $this->replaceTitle($contents, $page->getTitle());
				$contents = $this->replaceEncoding($contents);
				
				$page->setTemplate(serialize($contents));    				

    			$dao = $this->getPageDAO($page->getPageType());
    			$id = $dao->insert($page);
    			
    			//ラベルがあるかどうかチェック
    			$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
    			try{
    				$count = $labelDAO->countLabel();
    			}catch(Exception $e){
    				$count = 0;
    			}
    			
    			//0個の時、ラベルを追加して設定する。
    			if($count == 0){
    				$label = new Label();
    				$label->setCaption($page->getTitle());
    				
    				$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
    				$labelId = $logic->create($label);
    				
    				$blogPage = $dao->getById($id);
    				$blogPage->setBlogLabelId($labelId);
    				$dao->updatePageConfig($blogPage);
    			}
    			
    			return $id;
    			break;
    		
    		case Page::PAGE_TYPE_MOBILE:
    		default:

    			//テンプレートを指定していた場合
				if(strlen($tempname) != 0){
			    	$contents = $this->getTemplateContents($tempname);
    			}else{
    				$contents = $this->getDefaultTemplateContents($page->getPageType());
    			}

				$contents = $this->replaceTitle($contents, $page->getTitle());
				$contents = $this->replaceEncoding($contents);
				
				$page->setTemplate($contents);
    			$page->setPageTitleFormat("%PAGE%");
    	
    			$id = $this->getPageDAO($page->getPageType())->insert($page);
    			return $id;
    			break;
												
    	}
    }
    
    /**
     * ページタイプの応じたPageDAOを返す
     */
    function getPageDAO($pageType = null){
    	static $dao;

    	if(isset($dao[$pageType])) return $dao[$pageType];
    	
    	switch($pageType){
    		case Page::PAGE_TYPE_BLOG:
    			$dao[$pageType] = SOY2DAOFactory::create("cms.BlogPageDAO");
				break;
    		case Page::PAGE_TYPE_MOBILE:
    			$dao[$pageType] = SOY2DAOFactory::create("cms.MobilePageDAO");
				break;
    		default:
		    	$dao[$pageType] = SOY2DAOFactory::create("cms.PageDAO");
				break;
    	}
    	return $dao[$pageType];
    }
    
    /**
     * テンプレートパックから指定されたテンプレートの中身を返す
     * @param String id/name 
     */
    function getTemplateContents($tempname){
    	list($id,$name) = explode("/",$tempname);
    				    			    	
    	$dao = SOY2DAOFactory::create("cms.TemplateDAO");
    	$template = $dao->getById($id);
    	
    	$contents = $template->getTemplateContent($name);
    	
    	return $contents;
    }
    
    /**
     * デフォルトのテンプレートを返す
     */
    function getDefaultTemplateContents($pageType){
    	//BlogPageを読み込む
    	$this->getPageDAO($pageType);
    	
    	switch($pageType){
    		case Page::PAGE_TYPE_BLOG:
				$contents = array(  				
					BlogPage::TEMPLATE_TOP => file_get_contents(dirname(__FILE__)."/blog/top.html"),
					BlogPage::TEMPLATE_ENTRY => file_get_contents(dirname(__FILE__)."/blog/entry.html"),
					BlogPage::TEMPLATE_ARCHIVE => file_get_contents(dirname(__FILE__)."/blog/archive.html"),
				);
				break;
    		case Page::PAGE_TYPE_MOBILE:
    				$contents = file_get_contents(dirname(__FILE__)."/mobile_default.html");
				break;
    		default:
				$contents = file_get_contents(dirname(__FILE__)."/default.html");
				break;
    	}
    	
    	return $contents;
    }

    /**
     * テンプレート中のタイトル（@@TITLE@@）を置換する
     */
    function replaceTitle($contents, $title){
    	return $this->_replaceStrings($contents, "@@TITLE@@", $title);
    }
    /**
     * テンプレート中の文字コード（@@ENCODING@@）を置換する
     */
    function replaceEncoding($contents){
    	$charset = $this->getCharset();
    	return $this->_replaceStrings($contents, "@@ENCODING@@", $charset);
    }
	
	/**
	 * テンプレート中の$fromを$toに置換する
	 */
    private function _replaceStrings($contents, $from, $to){
    	
    	if(is_array($contents)){
    		foreach($contents as $key => $value){
    			$contents[$key] = $this->_replaceStrings($value, $from, $to);
    		}
    	}else{
			$contents = str_replace($from, $to, $contents);
    	}
    	
    	return $contents;

    }
    
    private function getCharset(){
    	static $charset;
    	if(!$charset){
	    	try{
		    	$charset = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getCharsetText();
	    	}catch(Exception $e){
	    		$charset = "UTF-8";
	    	}
    	}
    	return $charset;
    }
}
?>