<?php

class InquiryListComponent extends HTMLList{

	private $forms;

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$formId = (is_string($entity->getFormId()) || is_numeric($entity->getFormId())) ? $entity->getFormId() : "";
		$detailLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry.Detail." . $id);

		$this->addModel("inquiry_item", array(
			"style" => "cursor:pointer;",
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->addModel("form_name_td", array(
    		"visible" => count($this->forms) >= 2
		));
		$this->addLink("form_name", array(
			"text" => (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "",
			//"link" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $formId),
		));

		$this->addLink("id", array(
			"text" => $id,
			"link" => $detailLink,
		));
		$this->addLink("traking_number", array(
			"text" => $entity->getTrackingNumber(),
			"link" => $detailLink,
		));

		//getContentの中身はhtmlspecialcharsがかかっている
		$this->addLink("content", array(
			"html"  => (mb_strlen($entity->getContent()) >= 80) ? mb_substr($entity->getContent(), 0, 80) . "..." : $entity->getContent(),
			"link"  => $detailLink,
			"title" => $entity->getContent(),
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d", $entity->getCreateDate()) : ""
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
