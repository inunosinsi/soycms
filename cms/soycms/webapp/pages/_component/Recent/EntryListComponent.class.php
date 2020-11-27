<?php

class EntryListComponent extends HTMLList{

	var $labels = array();

	function setLabels($array){
		if(is_array($array)){
			$this->labels = $array;
		}
	}

	function populateItem($entity){

		$this->addLink("title", array(
			"link" => SOY2PageController::createLink("Entry.Detail") . "/" . $entity->getId(),
			"text" => (strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
			"onmouseover" => 'var ele=$(\'#popup_entry_comment_' . $entity->getId() . '\');if(!ele)return;ele.show();',
			"onmouseout" => 'var ele=$(\'#popup_entry_comment_' . $entity->getId() . '\');if(!ele)return;ele.hide();',
		));

		$popupText = ($entity->getDescription()) ? CMSUtil::getText("[メモ]") . $entity->getDescription() : "";
		$this->addLabel("popup", array(
			"id" => "popup_entry_comment_" . $entity->getId(),
			"text" => $popupText,
			"visible" => strlen($popupText)
		));

		$this->addLabel("content", array(
			"text"  => SOY2HTML::ToText($entity->getContent()),
			"width" => 60,
			"title" => mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 1000, "..."),
		));


		$this->addLabel("udate", array(
			"text"  => CMSUtil::getRecentDateTimeText($entity->getUdate()),
			"title" => (is_numeric($entity->getUdate())) ? date("Y-m-d H:i:s", $entity->getUdate()) : null
		));
	}
}
