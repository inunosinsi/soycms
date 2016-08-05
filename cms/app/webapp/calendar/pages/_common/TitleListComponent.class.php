<?php

class TitleListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		$this->createAdd("title","HTMLLabel",array(
			"text" => $entity->getTitle()
		));
		$this->createAdd("attribute","HTMLLabel",array(
			"text" => $entity->getAttribute()
		));
		
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Title.Detail.".$entity->getId())
		));
		
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Title.Remove.".$entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
		
	}
}
?>