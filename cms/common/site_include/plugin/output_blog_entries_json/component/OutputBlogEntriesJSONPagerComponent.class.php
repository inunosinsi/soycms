<?php

class OutputBlogEntriesJSONPagerComponent {

    public static function pager($htmlObj, string $url, int $current, int $total, int $lim){
		$last_page_number = (int)ceil($total / $lim);

        SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
		$htmlObj->createAdd("pager", "BlockPluginPagerComponent", array(
			"list" => array(),
			"current" => $current,
			"last"	 => $last_page_number,
			"url"		=> $url,
			"soy2prefix" => "p_block",
		));

		$htmlObj->addModel("has_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number > 1)
		));
		$htmlObj->addModel("no_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number < 2)
		));

		$htmlObj->addLink("first_page", array(
			"soy2prefix" => "p_block",
			"link" => $url,
		));

		$htmlObj->addLink("last_page", array(
			"soy2prefix" => "p_block",
			"link" => ($last_page_number > 0) ? $url . "page-" . ($last_page_number - 1) : null,
		));

		$htmlObj->addLabel("current_page", array(
			"soy2prefix" => "p_block",
			"text" => max(1, $current + 1),
		));

		$htmlObj->addLabel("pages", array(
			"soy2prefix" => "p_block",
			"text" => $last_page_number,
		));
    }
}