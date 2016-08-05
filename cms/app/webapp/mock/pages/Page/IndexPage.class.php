<?php

class IndexPage extends WebPage{
	
	private $sampleDao;
	
	function doPost(){
		
		if(soy2_check_token()){
			
		}
	}
	
	function __construct(){
		
		$dao = SOY2DAOFactory::create("sample.SOYMock_SampleDAO");
		
		WebPage::WebPage();
		
		try{
			$array = $dao->get();
		}catch(Exception $e){
			$array = array();
		}
		
		$this->createAdd("display", "HTMLModel", array(
			"visible" => (count($array) > 0)
		));
		
		$this->createAdd("list", "SampleList", array(
			"list" => $array
		));
	}
}

class SampleList extends HTMLList{
	
	protected function populateItem($entity){
	
		$this->createAdd("name", "HTMLLabel", array(
			"text" => $entity->getName()
		));
		
		$this->createAdd("description", "HTMLLabel", array(
			"html" => nl2br($entity->getDescription())
		));
		
		$link = SOY2PageController::createLink(APPLICATION_ID . ".Page.Detail." . $entity->getId());
		$this->createAdd("detail_link", "HTMLLink", array(
			"link" => $link
		));
		
		$removeLink = SOY2PageController::createLink(APPLICATION_ID . ".Page.Remove." . $entity->getId());
		$this->createAdd("remove_link", "HTMLActionLink", array(
			"link" => $removeLink,
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}
?>