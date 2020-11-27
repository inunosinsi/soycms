<?php

class PagerListComponent extends HTMLList{

	//今のページ番号
	var $current;
	//最大ページ数
	var $last;
	//ベースURL=最初のページのURL
	var $url;
	function setCurrent($current){
		$this->current = $current;
	}
	function setUrl($url){
		$this->url = $url;
	}

	protected function populateItem($page_num){

		$this->last = count($this->list);

		$url = $this->url;
		if($page_num >1){
			$url = $url."?p=".$page_num;
		}
		if($page_num == $this->current){
			$url = "";
		}

		$class = array();
		if($page_num == $this->current) $class[] = "current_page_number";
		if($page_num == 1) $class[] = "first_page_number";
		if($page_num == $this->last) $class[] = "last_page_number";

		$html = "";
		if(strlen($url)){
			$html .= "<a href=\"".htmlspecialchars($url, ENT_QUOTES, "UTF-8")."\"";
			if(count($class)) $html .= " class=\"".implode(" ",$class)."\"";
			$html .= ">";
		}
		$html .= htmlspecialchars($page_num, ENT_QUOTES, "UTF-8");
		if(strlen($url)) $html .= "</a>";

		$this->addLabel("pager_item", array(
				"html" => $html,
				"soy2prefix" => "cms"
		));
		$this->addLink("pager_item_link", array(
				"link" => $url,
				"soy2prefix" => "cms"
		));
		$this->addLabel("pager_item_number", array(
				"text" => $page_num,
				"soy2prefix" => "cms"
		));

		$this->addModel("is_first", array(
				"visible" => ($page_num == 1),
				"soy2prefix" => "cms"
		));
		$this->addModel("is_last", array(
				"visible" => ($page_num == $this->last),
				"soy2prefix" => "cms"
		));
		$this->addModel("is_middle", array(
				"visible" => ($page_num > 1 && $page_num < $this->last),
				"soy2prefix" => "cms"
		));
		$this->addModel("is_current", array(
				"visible" => ($page_num == $this->current),
				"soy2prefix" => "cms"
		));
	}
}
