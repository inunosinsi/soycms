<?php

class UserListComponent extends HTMLList{
	
	protected function populateItem($bean){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $bean->getId()
		));
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $bean->getName()
		));
		
		$this->createAdd("mailaddress","HTMLLabel",array(
			"text" => $bean->getMailAddress(),
			"title" => $bean->getMailAddress()
		));
		
		$this->createAdd("not_send","HTMLLabel",array(
			"text" => ($bean->getNotSend()==0) ? "許可" : "拒否"
		));
		
		$this->createAdd("attribute1","HTMLLabel",array(
			"text" => $bean->getAttribute1()
		));
		
		$this->createAdd("attribute2","HTMLLabel",array(
			"text" => $bean->getAttribute2()
		));
		
		$this->createAdd("attribute3","HTMLLabel",array(
			"text" => $bean->getAttribute3()
		));
		
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("mail.User.Detail") . "/" . $bean->getId()
		));
		
		$this->createAdd("remove_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("mail.User.Remove") . "/" . $bean->getId(),
			"onclick" => "return confirm('削除してよろしいですか？')"
		));
		
	}
}

?>