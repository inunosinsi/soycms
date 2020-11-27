<?php

class CommentListComponent extends HTMLList{

	private $url;

	public function setUrl($url){
		$this->url = $url;
	}

	public function populateItem($entry){

		if(strlen($entry->getTitle()) == 0){
			$title = CMSMessageManager::get("SOYCMS_NO_TITLE");
		}else{
			$title = $entry->getTitle();
		}

		$this->addLink("submitdate", array(
			"text"	=> (is_numeric($entry->getSubmitDate())) ? date('Y-m-d', $entry->getSubmitDate()) : "",
			"link"	=> SOY2PageController::createLink("Blog.CommentDetail.".$entry->getId()),
			"title"   => (is_numeric($entry->getSubmitDate())) ? date('Y-m-d H:i:s', $entry->getSubmitDate()) : "",
			"onclick" => "return common_click_to_layer(this,{header: 'コメント詳細 - ".$title."'});"
		));

		$this->addLabel("approved", array(
				"text"=>($entry->getIsApproved() == 0)? CMSMessageManager::get("SOYCMS_WORD_DENY") : CMSMessageManager::get("SOYCMS_WORD_ALLOW"),
		));

		$this->addLink("entry_title", array(
			"html" => $this->mb_cut_length_html($entry->getEntryTitle(),18),
			"link" => $this->url."/".((strlen($entry->getAlias())) ? rawurlencode($entry->getAlias()) : $entry->getId())."#comment_list"
		));

		$hTtitle = $this->mb_cut_length_html($title,18);
		if(strlen($entry->getUrl())){
			$hTtitle = "<a href=\"".htmlspecialchars($entry->getUrl(), ENT_QUOTES, "UTF-8")."\" target=\"_blank\">{$hTtitle}</a>";
		}
		$this->addLabel("title", array(
			"html" => $hTtitle
		));

		$hAuthor = $this->mb_cut_length_html($entry->getAuthor(),18);
		if(strlen($entry->getMailAddress())){
			$hAuthor = "<a href=\"".htmlspecialchars("mailto:".$entry->getMailAddress(), ENT_QUOTES, "UTF-8")."\" >{$hAuthor}</a>";
		}
		$this->addLabel("author", array(
			"html" => $hAuthor
		));

		$this->addLink("body", array(
			"html"=>$this->mb_cut_length_html($entry->getBody(),40),
			"link"	=> SOY2PageController::createLink("Blog.CommentDetail.".$entry->getId()),
			"onclick" => "return common_click_to_layer(this,{header: 'コメント詳細 - ".$title."'});"
		));

		$this->addInput("comment_id", array(
			"value"=>$entry->getId(),
			"name"=>"comment_id[]"
		));

	}

	private function mb_cut_length_html($text,$length){
		$hText = htmlspecialchars($text, ENT_QUOTES, "UTF-8");

		if(mb_strwidth($text) > $length){
			$sText = mb_strimwidth($text,0,$length);
			$sText .= "...";

			$hText = "<span title=\"{$hText}\">".htmlspecialchars($sText, ENT_QUOTES, "UTF-8")."</span>";
		}
		return $hText;
	}
}
