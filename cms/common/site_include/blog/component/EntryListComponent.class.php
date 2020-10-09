<?php

/**
 * 記事を表示
 */
class EntryListComponent extends HTMLList{

	var $entryPageUrl;
	var $categoryPageUrl;

	var $blogLabelId;

	var $categoryLabelList;
	var $entryCount;

	function setEntryPageUrl($entryPageUrl){
		$this->entryPageUrl = $entryPageUrl;
	}
	function setCategoryPageUrl($categoryPageUrl){
		$this->categoryPageUrl = $categoryPageUrl;
	}
	function setBlogLabelId($blogLabelId){
		$this->blogLabelId = $blogLabelId;
	}
	function setCategoryLabelList($categoryLabelList){
		$this->categoryLabelList = $categoryLabelList;
	}
	function setEntryCount($entryCount){
		$this->entryCount = $entryCount;
	}

	protected function populateItem($entry){

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=>$entry->getId(),
			"soy2prefix"=>"cms"
		));

		$link = $this->entryPageUrl . rawurlencode($entry->getAlias()) ;

		$this->createAdd("title","CMSLabel",array(
			"html"=> "<a href=\"$link\">".htmlspecialchars($entry->getTitle(), ENT_QUOTES, "UTF-8")."</a>",
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entry->getTitle(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("content","CMSLabel",array(
			"html"=>$entry->getContent(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entry->getMore(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("create_date","DateLabel",array(
			"text"=>$entry->getCdate(),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("create_time","DateLabel",array(
			"text"=>$entry->getCdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->createAdd("update_date","DateLabel",array(
			"text"=>$entry->getUdate(),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("update_time","DateLabel",array(
			"text"=>$entry->getUdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->createAdd("entry_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $link
		));

		$more = trim($entry->getMore());

		$this->createAdd("more_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $link ."#more",
			"visible"=>(strlen($more) != 0)
		));

		$this->createAdd("more_link_no_anchor", "HTMLLink", array(
			"soy2prefix"=>"cms",
			"link" => $link,
			"visible"=>(strlen($more) != 0)
		));

		$this->addModel("has_more",array(
			"visible"=> strlen($more),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("trackback_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $link ."#trackback_list"
		));

		$this->createAdd("trackback_count","CMSLabel",array(
			"soy2prefix"=>"cms",
			"text" => $entry->getTrackbackCount()
		));

		$this->createAdd("comment_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $link ."#comment_list"
		));

		$this->createAdd("comment_count","CMSLabel",array(
			"soy2prefix"=>"cms",
			"text" => $entry->getCommentCount()
		));

		$this->createAdd("category_list","CategoryListComponent",array(
			"list" => $entry->getLabels(),
			"categoryUrl" => $this->categoryPageUrl,
			"entryCount" => $this->entryCount,
			"soy2prefix" => "cms"
		));

		$this->createAdd("entry_url", "HTMLLabel", array(
			"text" => $link,
			"soy2prefix" => "cms",
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entry->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entry));

		//Messageの追加
		/**
		$this->addMessageProperty("entry_id",'<?php echo $'.$this->_soy2_id.'["entry_id"]; ?>');
		$this->addMessageProperty("title",'<?php echo $'.$this->_soy2_id.'["title_plain"]; ?>');
		$this->addMessageProperty("content",'<?php echo $'.$this->_soy2_id.'["content"]; ?>');
		$this->addMessageProperty("more",'<?php echo $'.$this->_soy2_id.'["more"]; ?>');
		$this->addMessageProperty("create_date",'<?php echo $'.$this->_soy2_id.'["create_date"]; ?>');
		$this->addMessageProperty("entry_link",'<?php echo $'.$this->_soy2_id.'["entry_link_attribute"]["href"]; ?>');
		$this->addMessageProperty("more_link",'<?php echo $'.$this->_soy2_id.'["more_link_attribute"]["href"]; ?>');
		$this->addMessageProperty("trackback_link",'<?php echo $'.$this->_soy2_id.'["trackback_link_attribute"]["href"]; ?>');
		$this->addMessageProperty("comment_link",'<?php echo $'.$this->_soy2_id.'["comment_link_attribute"]["href"]; ?>');
		**/
	}

	function getStartTag(){

		if(defined("CMS_PREVIEW_MODE")){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->_soy2_id.'["entry_id"]; ?>','<?php echo strip_tags($'.$this->_soy2_id.'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}

}
