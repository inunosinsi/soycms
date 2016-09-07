<?php

class RemovePage extends CMSWebPageBase{

	function __construct($args) {
		
		if(soy2_check_token()){

			$pageId = (int)$args[0];
			$labelId = (int)$args[1];
			
			$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
			
			//ブログの設定から指定したラベルを削除する
			$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
			$labelDao->begin();
			try{
				$blogPage = $pageDao->getById($pageId);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_REMOVE_FAILED");
				$this->jump("Blog.Category.".$pageId);
			}
				
			$categoryLabelList = $blogPage->getCategoryLabelList();
			
			$list = array();
			foreach($categoryLabelList as $id){
				if((int)$id === $labelId) continue;
				$list[] = (int)$id;
			}
			
			$blogPage->setCategoryLabelList($list);
			
			try{
				$pageDao->updatePageConfig($blogPage);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_REMOVE_FAILED");
				$this->jump("Blog.Category.".$pageId);
			}
			
			//ラベルを削除する
			$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
			try{
				$labelDao->delete($labelId);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_REMOVE_FAILED");
				$this->jump("Blog.Category.".$pageId);
			}
			
			$labelDao->commit();
			
			$this->addMessage("BLOG_CATEGORY_REMOVE_SUCCESS");
			$this->jump("Blog.Category." . $pageId);
		}
		
		exit;
	}
}
?>