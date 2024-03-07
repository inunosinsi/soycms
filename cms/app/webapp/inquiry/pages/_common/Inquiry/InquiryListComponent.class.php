<?php

class InquiryListComponent extends HTMLList{

	private $forms;
	private $formId;

	protected function populateItem($entity){

		$formId = (is_string($entity->getFormId()) || is_numeric($entity->getFormId())) ? $entity->getFormId() : "";
		$detailLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry.Detail." . $entity->getId());
		$formLink = SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $formId);

		$this->addCheckBox("inquiry_check", array(
			"type"=>"checkbox",
			"name"=>"bulk_modify[inquiry][]",
			"value"=>$entity->getId(),
			//"label" => $entity->getId(),
		));

    	//フォームが一つしかないときとフォームが指定されているときはフォーム名は表示しない
		$this->addModel("form_name_td", array(
			"style"   => "cursor:pointer;". (($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : ""),
    		"visible" => (is_null($this->formId) && count($this->forms) >= 2),
    		"onclick" => "location.href='{$detailLink}'"
		));
		$this->addLink("form_name", array(
			"text" => ( (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "" ),
			//"link" => $formLink,
			"title" => ( (isset($this->forms[$formId])) ? $this->forms[$formId]->getName() : "" ),
		));

		$this->addLink("traking_number", array(
			"text" => $entity->getTrackingNumber(),
			"link" => $detailLink,
			"style" => ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : ""
		));

		//getContentの中身はhtmlspecialcharsがかかっている
		$this->addLabel("content", array(
			"html"  => (mb_strlen($entity->getContent()) >= 80) ? mb_substr($entity->getContent(), 0, 80) . "..." : $entity->getContent(),
			"style" => "cursor:pointer;". ( ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : "" ),
			"title" => $entity->getContent(),
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s",$entity->getCreateDate()) : "",
			"style" => "cursor:pointer;".( ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "color:black;font-weight: bold;" : "" ),
			"onclick" => "location.href='{$detailLink}'"
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($entity->getUpdateDate())) ? date("Y-m-d H:i:s",$entity->getUpdateDate()) : "",
		));

		$this->addLabel("auto_delete_date", array(
			"html" => ((int)$entity->getUpdateDate() > 0) ? date("Y-m-d",strtotime("+".SOYInquiryUtil::SOYINQUIRY_PHYSICAL_DELETE_DAYS." days", (int)$entity->getUpdateDate())) : "<center>---</center>"
		));
		
		$this->addLink("flag", array(
			"text" => $entity->getFlagText(),
			"link" => $detailLink,
			"style" => ($entity->getFlag() == SOYInquiry_Inquiry::FLAG_NEW) ? "font-weight: bold;" : ""
		));

		$this->addModel("traking_number_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
		$this->addModel("create_date_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
		$this->addModel("flag_td", array("onclick" => "location.href='{$detailLink}'","style" => "cursor:pointer;"));
	}

	function getForms() {
		return $this->forms;
	}
	function setForms($forms) {
		$this->forms = $forms;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}
}