<?php

class LpoListComponent extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("title","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Detail.".$entity->getId()),
			"text" => $entity->getTitle()
		));
		
		$this->createAdd("mode","HTMLLabel",array(
			"text" => $entity->getModeText()
		));
		
		$this->createAdd("keyword","HTMLLabel",array(
			"text" => $entity->getKeyword()
		));
		
		$this->createAdd("public","HTMLLabel",array(
			"text" => ($entity->getIsPublic()==1) ? "公開" : "非公開"
		));
		
		$this->createAdd("update_date","HTMLLabel",array(
			"text" => date("Y-m-d H:i:s",$entity->getUpdateDate())
		));
		
		$this->createAdd("edit","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Detail.".$entity->getId())
		));
		
		$this->createAdd("remove","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Remove.".$entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');",
			"visible" => ($entity->getId()!=1)
		));
	}
}
?>