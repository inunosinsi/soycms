<?php

class GetStyleAjaxPage extends CMSWebPageBase{

	function __construct($args) {

		$id = @$args[0];

		$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction",array(
			"id" => $id
		))->run();
		$template = $result->getAttribute("entity");

		$templates = $template->getTemplates();

		header("Content-Type: text/css");
		echo $templates["style"];

		exit;
	}
}
