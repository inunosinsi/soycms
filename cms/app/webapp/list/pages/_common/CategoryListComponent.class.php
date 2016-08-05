<?php

class CategoryListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		$this->createAdd("memo","HTMLLabel",array(
			"text" => $entity->getMemo()
		));
		
		$this->createAdd("sort","HTMLInput",array(
			"name" => "Category[sort][".$entity->getId()."]",
			"value" => ((int)$entity->getSort() < 100000) ? $entity->getSort() : "",
			"style" => "text-align:right;ime-mode:inactive;"
		));
		
		$this->createAdd("list_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Item.List.".$entity->getId())
		));
		
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Category.Detail.".$entity->getId())
		));
		
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Category.Remove.".$entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}

?>