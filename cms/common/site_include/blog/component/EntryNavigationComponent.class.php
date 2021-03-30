<?php
class EntryNavigationComponent extends SOYBodyComponentBase{

	var $entryPageUri;

	function setEntryPageUri($uri){
		$this->entryPageUri = $uri;
	}

	function setEntry($entry){
		$this->createAdd("title","CMSLabel",array(
			"text" => $entry->getTitle(),
			"soy2prefix" => "cms"
		));

		//同じ意味だけど、他のブロックと合わせてtitle_plainを追加しておく
		$this->createAdd("title_plain","CMSLabel",array(
			"text" => $entry->getTitle(),
			"soy2prefix" => "cms"
		));

		$this->addLink("entry_link", array(
			"link" => $this->entryPageUri . rawurlencode($entry->getAlias()),
			"soy2prefix" => "cms"
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entry->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entry));
	}
}
