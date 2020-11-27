<?php

/**
 * 記事を表示するコンポーネント
 */
class EntryComponent extends SOYBodyComponentBase{

	var $entryPageUri;
	var $categoryPageUri;
	var $blogLabelId;
	var $categoryLabelList;
	var $labels;
	var $entryLogic;

	function setEntryPageUri($uri){
		$this->entryPageUri = $uri;
	}

	function setCategoryPageUri($uri){
		$this->categoryPageUri = $uri;
	}

	function setBlogLabelId($blogLabelId){
		$this->blogLabelId = $blogLabelId;
	}

	function setCategoryLabelList($categoryLabelList){
		$this->categoryLabelList = $categoryLabelList;
	}

	function setLabels($labels){
		$this->labels = $labels;
	}
	function setEntryLogic($entryLogic){
		$this->entryLogic = $entryLogic;
	}

	function setEntry($entry){
		$id = (is_numeric($entry->getId())) ? (int)$entry->getId() : 0;
		$link = $this->entryPageUri . rawurlencode($entry->getAlias());

		$this->createAdd("entry_id","CMSLabel",array(
			"text" => $id,
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"html"=> "<a href=\"$link\">".htmlspecialchars($entry->getTitle(), ENT_QUOTES, "UTF-8")."</a>",
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entry->getTitle(),
			"soy2prefix"=>"cms"
		));

		//本文
		$content = trim($entry->getContent());

		$this->createAdd("content","CMSLabel",array(
			"html"=> $content,
			"soy2prefix"=>"cms"
		));
		/** 一度目でcms:id="content" cms:length="*"を使用してしまうと、以後もcms:lengthに引き連れてしまうため、予備でcms:id="contents"を設ける **/
		$this->createAdd("content2","CMSLabel",array(
			"html"=> $content,
			"soy2prefix"=>"cms"
		));
		$this->addModel("has_content",array(
			"visible"=> strlen($content),
			"soy2prefix"=>"cms",
		));

		$more = trim($entry->getMore());

		$this->createAdd("more","CMSLabel",array(
			"html"=> '<a name="more"></a>'.$more,
			"soy2prefix"=>"cms",
		));

		// 2015-07-09追加 1.8.13以降
		$this->addLabel("more_only",array(
			"html"=> $more,
			"soy2prefix"=>"cms",
		));
		$this->addModel("has_more",array(
			"visible"=> strlen($more),
			"soy2prefix"=>"cms",
		));

		//ページ分割 3.0.1-
		$currentPage = isset($_GET["p"]) && is_numeric($_GET["p"]) && $_GET["p"] > 0 ? $_GET["p"] : 1 ;
		$numberOfPages = 1;

		$contentIsPaginated = ( strpos($content, '<!--nextpage-->') !== false );
		if($contentIsPaginated){
			$paginatedContents = explode('<!--nextpage-->', $content);
			$numberOfPages = count($paginatedContents);
		}else{
			$paginatedContents = array($content);
		}

		$moreIsPaginated = ( strpos($more, '<!--nextpage-->') !== false );
		if($moreIsPaginated){
			$paginatedMores = explode('<!--nextpage-->', $more);
			$numberOfPages = max($numberOfPages, count($paginatedContents));
		}else{
			$paginatedMores = array($more);
		}

		$this->addModel("content_is_paginated",array(
				"visible"=>$contentIsPaginated,
				"soy2prefix"=>"cms"
		));
		$this->addModel("content_is_not_paginated",array(
				"visible"=>!$contentIsPaginated,
				"soy2prefix"=>"cms"
		));
		$this->addLabel("paginated_content",array(
				"html"=>isset($paginatedContents[$currentPage -1]) ? $paginatedContents[$currentPage -1] : "",
				"soy2prefix"=>"cms"
		));

		$this->addModel("more_is_paginated",array(
				"visible"=>$moreIsPaginated,
				"soy2prefix"=>"cms",
		));
		$this->addModel("more_is_not_paginated",array(
				"visible"=>!$moreIsPaginated,
				"soy2prefix"=>"cms",
		));
		$this->addLabel("paginated_more",array(
				"html"=>isset($paginatedMores[$currentPage -1]) ? $paginatedMores[$currentPage -1] : "",
				"soy2prefix"=>"cms"
		));

		$this->addModel("entry_is_paginated",array(
				"visible"=>$contentIsPaginated || $moreIsPaginated,
				"soy2prefix"=>"cms"
		));
		$this->addModel("entry_is_not_paginated",array(
				"visible"=>!( $contentIsPaginated || $moreIsPaginated ),
				"soy2prefix"=>"cms"
		));
		$this->addLabel("current_page",array(
				"text"=> $currentPage,
				"soy2prefix"=>"cms"
		));
		$this->addLabel("pages",array(
				"text"=> $numberOfPages,
				"soy2prefix"=>"cms"
		));
		$this->addLabel("total_pages",array(
				"text"=> $numberOfPages,
				"soy2prefix"=>"cms"
		));
		$this->addModel("is_first_page",array(
				"visible"=> $currentPage == 1,
				"soy2prefix"=>"cms"
		));
		$this->addModel("is_middle_page",array(
				"visible"=> 1 < $currentPage && $currentPage < $numberOfPages,
				"soy2prefix"=>"cms"
		));
		$this->addModel("is_last_page",array(
				"visible"=> $currentPage == $numberOfPages,
				"soy2prefix"=>"cms"
		));

		$this->addLink("next_page_link",array(
			"link"=> ( $currentPage < $numberOfPages ? $link."?p=".($currentPage +1) : ""),
			"soy2prefix"=>"cms"
		));
		$this->addModel("has_next_page",array(
			"visible"=> ($currentPage < $numberOfPages),
			"soy2prefix"=>"cms"
		));

		$this->addLink("prev_page_link",array(
			"link"=> ( $currentPage > 1 ? $link."?p=".($currentPage -1) : ""),
			"soy2prefix"=>"cms"
		));
		$this->addModel("has_prev_page",array(
			"visible"=> ($currentPage > 1),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("page_list","PagerListComponent",array(
			"list"=> range(1,$numberOfPages),
			"current" => $currentPage,
			"url" => $link,
			"soy2prefix"=>"cms"
		));


		$this->createAdd("create_date","DateLabel",array(
			"text"=>$entry->getCdate(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("create_time","DateLabel",array(
			"text"=>$entry->getCdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->createAdd("update_date","DateLabel",array(
			"text"=>$entry->getUdate(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("update_time","DateLabel",array(
			"text"=>$entry->getUdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->addLink("entry_link", array(
			"soy2prefix"=>"cms",
			"link" => $link
		));

		$this->addLink("entry_link_id_ed", array(
			"soy2prefix"=>"cms",
			"link" => $this->entryPageUri . $id
		));

		$this->addLabel("entry_link_text_id_ed", array(
			"soy2prefix"=>"cms",
			"text" => $this->entryPageUri . $id
		));

		$this->addLink("more_link", array(
			"soy2prefix"=>"cms",
			"link" => $link ."#more",
			"visible"=>(strlen($entry->getMore()) != 0)
		));

		$this->addLink("more_link_no_anchor", array(
			"soy2prefix"=>"cms",
			"link" => $link,
			"visible"=>(strlen($entry->getMore()) != 0)
		));

		$this->addLink("trackback_link", array(
			"soy2prefix"=>"cms",
			"link" => $link ."#trackback_list"
		));

		$this->createAdd("trackback_count","CMSLabel",array(
			"soy2prefix"=>"cms",
			"text" => $entry->getTrackbackCount()
		));

		$this->addLink("comment_link", array(
			"soy2prefix"=>"cms",
			"link" => $link ."#comment_list"
		));

		$this->createAdd("comment_count","CMSLabel",array(
			"soy2prefix"=>"cms",
			"text" => $entry->getCommentCount()
		));

		$categoryLabel = array();
		$entryCount = array();
		foreach($this->labels as $labelId => $label){
			if(in_array($labelId, $this->categoryLabelList)){
				$categoryLabel[] =  $label;
				try{
					//記事の数を数える。
					$counts = $this->entryLogic->getOpenEntryCountByLabelIds(array_unique(array($this->blogLabelId,$labelId)));
				}catch(Exception $e){
					$counts= 0;
				}
				$entryCount[$labelId] = $counts;
			}
		}

		//カテゴリリンク
		$this->createAdd("category_list", "CategoryListComponent", array(
			"list" => $entry->getLabels(),
			"categoryUrl" => $this->categoryPageUri,
			"entryCount" => $entryCount,
			"soy2prefix" => "cms"
		));

		$this->addLabel("entry_url", array(
			"text" => $link,
			"soy2prefix" => "cms",
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$id,"SOY2HTMLObject"=>$this,"entry"=>$entry));

/**
		//Messageの追加
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
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]["entry_id"]; ?>','<?php echo strip_tags($'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}
}
