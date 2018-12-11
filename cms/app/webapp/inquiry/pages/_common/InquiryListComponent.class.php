<?php

class InquiryListComponent extends HTMLList{

	private $forms;

	protected function populateItem($entity){

		$formId = $entity->getFormId();
		$detailLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry.Detail." . $entity->getId());

		$this->createAdd("inquiry_item","HTMLModel",array(
			"style" => "cursor:pointer;",
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->createAdd("form_name_td","HTMLModel",array(
    		"visible" => count($this->forms) >= 2
		));
		$this->createAdd("form_name","HTMLLink",array(
			"text" => (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "",
			//"link" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $formId),
		));

		$this->createAdd("id","HTMLLink",array(
			"text" => $entity->getId(),
			"link" => $detailLink,
		));
		$this->createAdd("traking_number","HTMLLink",array(
			"text" => $entity->getTrackingNumber(),
			"link" => $detailLink,
		));

		//getContentの中身はhtmlspecialcharsがかかっている
		$this->createAdd("content","HTMLLink",array(
			"html"  => $entity->getContent(),
			"link"  => $detailLink,
			"title" => $entity->getContent(),
		));

		$this->createAdd("create_date","HTMLLabel",array(
			"text" => date("Y-m-d",$entity->getCreateDate())
		));

		$this->createAdd("flag","HTMLLink",array(
			"text" => $entity->getFlagText(),
			"link" => $detailLink,
			"style" => (!$entity->getFlag()) ? "color:red" : ""
		));

	}

	function getForms() {
		return $this->forms;
	}
	function setForms($forms) {
		$this->forms = $forms;
	}
}
