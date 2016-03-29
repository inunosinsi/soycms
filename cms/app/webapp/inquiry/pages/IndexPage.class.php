<?php

SOY2HTMLFactory::importWebPage("_common.FormList");

class IndexPage extends WebPage{

	private $fomrs;

	function doPost(){
		if(isset($_POST['trackId'])){
    		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    		try{
				$inquiry = $dao->getByTrackingNumber($_POST['trackId']);
		    	CMSApplication::jump("Inquiry.Detail." . $inquiry->getId());
 		    	exit;
    		}catch(Exception $e){
    			//tracking number が間違ってる
    		}
    		
		}

	}
    function IndexPage() {
    	WebPage::WebPage();

    	try{
	    	$formDAO = SOY2DAOFactory::create("SOYInquiry_FormDAO");
	    	$this->forms = $formDAO->get();
    	}catch(Exception $e){
    		$this->forms = array();
    	}

		$this->buildFormList();
		
		$this->buildInquiryList();
		    	
    }
    
    function buildFormList(){
    	
    	$this->createAdd("form_list","FormList",array(
    		"list" => $this->forms    	
    	));	
    }
    
    function buildInquiryList(){
    	$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	$dao->setLimit(30);
    	$inquiries = $dao->search(null, null, null,null, 0);	//未読のみ
    	
    	$this->createAdd("form_name_th","HTMLModel",array(
    		"visible" => count($this->forms) >= 2,
    	));
    	$this->createAdd("inquiry_list","InquiryList",array(
    		"forms" => $this->forms,
			"list" => $inquiries,
    	));
    	$this->createAdd("trackId", "HTMLInput",array(
    		"name" => "trackId",
    		"value" => "受付番号",
    		"style" => "color: grey;",
    		"onfocus" => "if(this.value == '受付番号'){ this.value = ''; this.style.color = '';}",
    		"onblur"  => "if(this.value.length == 0){ this.value='受付番号'; this.style.color = 'grey'}"
    	));
    	
    	$this->createAdd("no_inquiry","HTMLModel",array(
    		"visible" => (count($inquiries) == 0)
    	));
    	$this->createAdd("no_inquiry_text","HTMLModel",array(
    		"colspan" => ( count($this->forms) >= 2 ) ? "4" : "3" 
    	));
    }
}

class InquiryList extends HTMLList{
	
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
?>