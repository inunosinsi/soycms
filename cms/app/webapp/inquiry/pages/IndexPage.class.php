<?php

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

    function __construct() {
    	parent::__construct();

		//データベースの更新を調べる
		$checkVer = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic")->checkVersion();
		DisplayPlugin::toggle("has_db_update", $checkVer);

		//データベースの更新終了時に表示する
		$doUpdated = (isset($_GET["update"]) && $_GET["update"] == "finish");
		DisplayPlugin::toggle("do_db_update", $doUpdated);

		//上記二つのsoy:displayの表示用
		DisplayPlugin::toggle("do_update", ($checkVer || $doUpdated));

    	try{
	    	$formDAO = SOY2DAOFactory::create("SOYInquiry_FormDAO");
	    	$this->forms = $formDAO->get();
    	}catch(Exception $e){
    		$this->forms = array();
    	}

		self::buildFormList();
		self::buildInquiryList();

    }

    private function buildFormList(){

    	$this->createAdd("form_list","_common.FormListComponent",array(
    		"list" => $this->forms
    	));
    }

    private function buildInquiryList(){
    	$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	$dao->setLimit(30);
    	$inquiries = $dao->search("", 0, 0, "", 0);	//未読のみ

    	$this->createAdd("form_name_th","HTMLModel",array(
    		"visible" => count($this->forms) >= 2,
    	));
    	$this->createAdd("inquiry_list","_common.InquiryListComponent",array(
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

		DisplayPlugin::toggle("no_inquiry", !count($inquiries));
    	$this->addModel("no_inquiry_text", array(
    		"colspan" => ( count($this->forms) >= 2 ) ? "4" : "3"
    	));
    }
}
