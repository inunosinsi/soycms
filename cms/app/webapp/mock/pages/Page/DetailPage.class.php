<?php

class DetailPage extends WebPage{
	
	private $id;
	private $sampleDao;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Sample"])){
			
			try{
				$sampleObj = $this->sampleDao->getById($this->id);
			}catch(Exception $e){
				$sampleObj = new SOYMock_Sample();
			}
			
			$obj = SOY2::cast($sampleObj, (object)$_POST["Sample"]);
			
			try{
				$this->sampleDao->update($obj);
			}catch(Exception $e){
				//
			}
			
			CMSApplication::jump("Page.Detail." . $this->id . "?updated");
		}
	}
	
	/**
	 * $args[0]にURLの末尾のIDが入る
	 */
	function __construct($args){
		
		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		$this->sampleDao = SOY2DAOFactory::create("Sample.SOYMock_SampleDAO");
		
		WebPage::WebPage();
		
		try{
			$obj = $this->sampleDao->getById($this->id);
		}catch(Exception $e){
			$obj = new SOYMock_Sample();
		}
		
		$this->createAdd("form", "HTMLForm");
		
		$this->createAdd("name", "HTMLInput", array(
			"name" => "Sample[name]",
			"value" => $obj->getName()
		));
		
		$this->createAdd("description", "HTMLTextArea", array(
			"name" => "Sample[description]",
			"value" => $obj->getDescription()
		));
	}
}
?>