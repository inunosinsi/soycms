<?php
//SOY2::import("soycms.webapp.pages.Entry.ListPage");
include_once(dirname(dirname(__FILE__))."/ListPage.class.php");

class ClosedPage extends ListPage{

	function __construct($arg) {
    	parent::__construct(array("Closed"));

    	DisplayPlugin::hide("no_label");

    	$this->addLabel("label_state", array(
    		"text"=>CMSMessageManager::get("SOYCMS_DRAFT_ENTRY_LIST")
    	));
    }

    function getTemplateFilePath(){

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"){
    		return dirname(dirname(__FILE__)) . "/ListPage.html";
		}else{
			return  SOYCMS_LANGUAGE_DIR. SOYCMS_LANGUAGE . "/Entry/ListPage.html";
		}
    }

    var $_entities = array();

    function getEntries($offset,$limit,$labelIds){
    	$result = $this->run("Entry.ClosedEntryListAction",array(
    		"offset"=>$offset,
    		"limit"=>$limit
    	));

    	$entities = $result->getAttribute("Entities");
    	$totalCount = $result->getAttribute("total");

    	return array($entities,$totalCount,min($offset,$totalCount));
    }

    /**
	 * get child labels
	 */
	function getNarrowLabels(){
		if(empty($this->labelIds)){
			return array();
		}else{
			return parent::getNarrowLabels();
		}
	}
}
