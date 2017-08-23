<?php

class ToolBoxPage extends CMSHTMLPageBase{

	function execute(){

		$links = CMSToolBox::getLinks();
		$linkHtml = "";
		foreach($links as $link){
			$href = htmlspecialchars($link["link"],ENT_QUOTES,"UTF-8");
			$onclick = (strlen($link["onclick"])>0) ? " onclick=\"".htmlspecialchars($link['onclick'],ENT_QUOTES,"UTF-8")."\"" : "" ;
			$text = htmlspecialchars($link["text"],ENT_QUOTES,"UTF-8");

			$linkHtml .= "<a href=\"{$href}\"{$onclick} class=\"list-group-item\">{$text}</a>";
		}

		$htmls = CMSToolBox::getHTMLs();
		$otherHtml = "";
		foreach($htmls as $html){
			$otherHtml.= "<div>".$html."</div>";
		}

		$this->createAdd("toolbox_linkbox","HTMLLabel",array(
			"html" => $linkHtml . $otherHtml,
		));

	}
}
