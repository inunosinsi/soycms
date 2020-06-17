<?php

class IndexPage extends CMSWebPageBase{

	public function __construct() {
		parent::__construct();
		SOY2HTMLFactory::createInstance("Page.List.TreePage")->display();
	}
}
