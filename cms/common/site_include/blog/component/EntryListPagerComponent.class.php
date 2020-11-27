<?php

/**
 * ページャーを表示
 */
class EntryListPagerComponent extends HTMLList{
	//今のページ番号
	var $current;
	//最大ページ数
	var $last;
	//ベースURL=最初のページのURL
	var $url;
	function setCurrent($current){
		$this->current = $current;
	}
	function setLast($last){
		$this->last = $last;
	}
	function setUrl($url){
		$this->url = $url;
	}

	/**
	 * cms:pager_numのためにオーバーライド
	 */
	function execute(){
		//ページャーの表示件数（デフォルトは10）
		$pager_display_number = $this->getAttribute("cms:pager_num");
		if(strlen($pager_display_number) ==0) $pager_display_number = 10;

		$display_start = max(1, min($this->current - floor($pager_display_number/2), $this->last - $pager_display_number+1));
		$display_end   = min($this->last, max($pager_display_number, $this->current + floor(($pager_display_number-1)/2)));

		$this->list = array();
		for($page_num=$display_start;$page_num<=$display_end;$page_num++){
			$url = $this->url;

			//2ページ以降は/page-2を付ける
			if($page_num > 1){
				$url .= (strlen($url) ==0 OR $url[strlen($url)-1] != "/") ? "/" : "" ;
				$url .= "page-" . ($page_num -1);
			}

			$this->list[] = array(
				"display_number" => $page_num,
				"url" => $url
			);
		}

		parent::execute();
	}

	protected function populateItem($pager_list){

		$html = "<a href=\"".htmlspecialchars($pager_list["url"], ENT_QUOTES, "UTF-8")."\"";

		$class = array();
		if($pager_list["display_number"] == $this->current) $class[] = "current_page_number";
		if($pager_list["display_number"] == 1) $class[] = "first_page_number";// 1.3.4-
		if($pager_list["display_number"] == $this->last) $class[] = "last_page_number";// 1.3.4-
		if(count($class)) $html .= " class=\"".implode(" ",$class)."\"";

		$html .= ">";
		$html .= htmlspecialchars($pager_list["display_number"], ENT_QUOTES, "UTF-8");
		$html .= "</a>";

		$this->addLink("pager_item_link", array(
			"link" => $pager_list["url"],
			"text" => $pager_list["display_number"],
			"soy2prefix" => "cms"
		));
		$this->addLabel("pager_item", array(
			"html" => $html,
			"soy2prefix" => "cms"
		));

		//1.2.8～
		$this->addModel("is_first", array(
			"visible" => ($pager_list["display_number"] == 1),
			"soy2prefix" => "cms"
		));
		$this->addModel("is_last", array(
			"visible" => ($pager_list["display_number"] == $this->last),
			"soy2prefix" => "cms"
		));
		$this->addModel("is_current", array(
			"visible" => ($pager_list["display_number"] == $this->current),
			"soy2prefix" => "cms"
		));

		//3.0.1-
		$this->addModel("is_middle", array(
				"visible" => ($pager_list["display_number"] > 1 && $pager_list["display_number"] < $this->last),
				"soy2prefix" => "cms"
		));
	}
}
