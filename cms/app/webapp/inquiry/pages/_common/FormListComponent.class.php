<?php
class FormListComponent extends HTMLList{

	private $inquiryLogic;

	function getInquiryLogic(){
		if(!$this->inquiryLogic){
			$this->inquiryLogic = SOY2Logic::createInstance("logic.InquiryLogic");
		}
		return $this->inquiryLogic;
	}

	protected function populateItem($entity){

		$this->createAdd("formId","HTMLLabel",array(
			"text" => $entity->getFormId()
		));

		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getName()
		));

		SOY2DAOFactory::importEntity("SOYInquiry_Inquiry");
		$count = (is_numeric($entity->getId())) ? $this->getInquiryLogic()->countUndeletedInquiryByFormId($entity->getId()) : 0;
		$this->createAdd("inquiry_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $entity->getId()),
			"text" => "{$count}件"
		));
		$count = (is_numeric($entity->getId())) ? $this->getInquiryLogic()->countInquiryByFormIdByFlag($entity->getId(), SOYInquiry_Inquiry::FLAG_NEW) : 0;
		$this->createAdd("unread_inquiry_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Inquiry?formId=" . $entity->getId() . "&flag=".SOYInquiry_Inquiry::FLAG_NEW),
			"text" => "({$count}件)",
			"style" => ($count >0) ? "font-weight:bold; color: black;" : ""
		));

		$this->createAdd("design_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design." . $entity->getId())
		));

		$this->createAdd("config_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $entity->getId())
		));

		$this->createAdd("template_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Template." . $entity->getId())
		));

		$this->addActionLink("delete_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除してよろしいですか？')"
		));
	}
}
