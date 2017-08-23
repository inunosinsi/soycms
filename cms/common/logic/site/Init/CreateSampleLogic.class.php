<?php

class CreateSampleLogic extends SOY2LogicBase {

    function CreateSampleLogic() {
    }

	/**
	 * Insert Sample Data
	 */
	function createSampleData() {
    	SOY2::import("domain.cms.Page");
    	SOY2::import("domain.cms.Block");
    	
    	$pagelogic  = SOY2Logic::createInstance("logic.site.Page.CreatePageLogic");
		$entrylogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$labellogic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$blocklogic = SOY2Logic::createInstance("logic.site.Block.BlockLogic");
				
		//Sample Block ID
		$soyid = "omame_news";

		// Upload sample template pack
    	$templatelogic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
    	
    	if((!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja")){
			$sampleTemplateFile="sampleomame.zip";
			$id = "0_sampleomame_manifest";

    	}else{
    		$sampleTemplateFile="sampleomame_".SOYCMS_LANGUAGE.".zip";
    		$id = "0_sampleomame_".SOYCMS_LANGUAGE."_manifest";

    		if(!file_exists(dirname(__FILE__)."/".$sampleTemplateFile)){
    			$sampleTemplateFile="sampleomame.zip";
    			$id = "0_sampleomame_manifest";
  
    		}
    	} 	
    	   	
   		$templatelogic->uploadTemplate("", dirname(__FILE__)."/".$sampleTemplateFile);

		// Install sample template pack

		$template = $templatelogic->getById($id);
		$filelist = $template->getFileList();
    	$templatelogic->installTemplate($id,array_keys($filelist));

    	
    	// Setting of the templates
    	$subs = $template->getTemplate();
		$tmp1 = array_shift($subs);
		$tmp2 = array_shift($subs);
		$topPageTemplate = null;
		$companyInfoTemplate = null;
		if ($tmp1["name"] == CMSMessageManager::get("SOYCMS_SAMPLE_TOP_TITLE")) {
			$topPageTemplate = $template->getTemplateContent($tmp1["id"]);
			$companyInfoTemplate = $template->getTemplateContent($tmp2["id"]);
		} else {
			$topPageTemplate = $template->getTemplateContent($tmp2["id"]);
			$companyInfoTemplate = $template->getTemplateContent($tmp1["id"]);
		}

		// Create sample label
		$caption = CMSMessageManager::get("SOYCMS_SAMPLE_NEWS_LABEL");
		try{
			$label = $labellogic->getByCaption($caption);
			$labelid = $label->getId();
		}catch(Exception $e){
			$label = new Label();
			$label->setCaption($caption);
			$labelid = $labellogic->create($label);
		}
		
		$dao = new SOY2DAO();
		$dao->executeUpdateQuery("delete from Page where page_type != ".Page::PAGE_TYPE_ERROR,array());
		
		
		// Create top page
    	$page = new Page();
    	$page->setTitle(CMSMessageManager::get("SOYCMS_SAMPLE_TITLE"));
		$page->setIsPublished(Page::PAGE_ACTIVE);
		$page->setUri("");
		$page->setPageType(Page::PAGE_TYPE_NORMAL);

		// Apply template pack to the webpage
		$encoding = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getCharsetText();
    	$topPageTemplate = str_replace("@@TITLE@@",$page->getTitle(),$topPageTemplate);
		$topPageTemplate = str_replace("@@ENCODING@@",$encoding,$topPageTemplate);
		$page->setTemplate($topPageTemplate);
		$page->setPageTitleFormat("%PAGE%");

		// Insert top page via DAO
		$pageid = $pagelogic->getPageDAO()->insert($page);
	
		// Create company information page
    	$infopage = new Page();
    	$infopage->setTitle(CMSMessageManager::get("SOYCMS_SAMPLE_COMPANY_INFORMATION"));
		$infopage->setIsPublished(Page::PAGE_ACTIVE);
		$infopage->setUri("company_information");
		$infopage->setPageType(Page::PAGE_TYPE_NORMAL);
		
		// Apply template pack to the webpage
    	$companyInfoTemplate = str_replace("@@TITLE@@",$infopage->getTitle(),$companyInfoTemplate);
		$companyInfoTemplate = str_replace("@@ENCODING@@",$encoding,$companyInfoTemplate);
		$infopage->setTemplate($companyInfoTemplate);
		$infopage->setPageTitleFormat("%PAGE%");
	
		// Insert company info page via DAO
		$infopageid = $pagelogic->getPageDAO()->insert($infopage);
		
	
		// Create LabelBlock setting in these webpages
		$block = new Block();
		$block->setClass("LabeledBlockComponent");
		$block->setPageId($pageid);
		$block->setSoyId($soyid);
		$component = $block->getBlockComponent();
		$component->setLabelId($labelid);
		$block->setObject($component);
		
		try{
			$blockid = $blocklogic->create($block);
		}catch(Exception $e){
			//soyid "omame_news" is already set.
		}				
						
		// Create sample entry
		$entry = new Entry();
		$entry->setTitle(CMSMessageManager::get("SOYCMS_SAMPLE_ENTRY_TITLE"));
		$entry->setContent(CMSMessageManager::get("SOYCMS_SAMPLE_ENTRY_CONTENT"));
		$entry->setIsPublished(true);
		$entry->setOpenPeriodEnd(CMSUtil::encodeDate(null,false));
		$entry->setOpenPeriodStart(CMSUtil::encodeDate(null,true));
		$entryid = $entrylogic->create($entry);
		
		// Label sample entry
		$entrylogic->setEntryLabel($entryid,$labelid);
	

		return true;
    }
}
?>