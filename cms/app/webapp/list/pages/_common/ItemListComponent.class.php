<?php

class ItemListComponent extends HTMLList{
	
	private $categories;
	
	protected function populateItem($entity){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("name","HTMLLabel",array(
    		"text" => $entity->getName()
    	));
    	
    	$this->createAdd("category","HTMLLabel",array(
    		"text" => (isset($this->categories[$entity->getCategory()])) ? $this->categories[$entity->getCategory()] : ""
    	));
    	
    	$this->createAdd("price","HTMLLabel",array(
    		"text" => number_format($entity->getPrice())
    	));
    	
    	$this->createAdd("description","HTMLLabel",array(
    		"text" => $entity->getDescription()
    	));
    	
    	$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Item.Detail.".$entity->getId())
		));
		
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Item.Remove.".$entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
	
	function setCategories($categories){
		$this->categories = $categories;
	}
}

?>