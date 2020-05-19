<?php

class NotepadListAreaComponent {

	private $item;
	private $category;
	private $user;
	private $pluginId;	//エディタの画面へのリンクの時に利用する。エディタの画面のパンくずのURLの書き換え用

	function buildBlock(){
		$html = array();
		$html[] = "<div class=\"row\">";
		$html[] = "	<div class=\"col-lg-12\">";
		$html[] = "		<div class=\"panel panel-default\">";
		$html[] = self::_titleblock();
		$html[] = self::_bodyblock();
		$html[] = "		</div>";
		$html[] = "	</div>";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	private function _titleblock(){
		$html = array();
		$html[] = "<div class=\"panel-heading\">";

		if(!is_null($this->item) && $this->item instanceof SOYShop_Item){
			$title = $this->item->getName();
		}else if(!is_null($this->category) && $this->category instanceof SOYShop_Category){
			$title = $this->category->getName();
		}else if(!is_null($this->user) && $this->user instanceof SOYShop_User){
			$title = $this->user->getName();
		}

		$html[] = "	" . $title . "のメモ";
		$html[] = "	<small class=\"pull-right\">";
		$html[] = "		<a href=\"" . self::_buildLink() . "\" class=\"btn btn-default btn-xs\">新規作成</a>";
		$html[] = "	</small>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	private function _bodyblock(){
		$html = array();
		$html[] = "<div class=\"panel-body\">";

		$notepads = self::_getNotepads();
		$noteCnt = count($notepads);

		if($noteCnt === 0) $html[] = "<div class=\"alert alert-info\">メモはありません。<a href=\"" . self::_buildLink() . "\" class=\"btn btn-default\">メモを新規作成する</a></div>";

		//jqueryで一覧を組み立てる
		if($noteCnt){
			$html[] = self::_buildList($notepads);
			$html[] = "<style>\n" . file_get_contents(dirname(dirname(__FILE__)) . "/css/list.css") . "\n</style>";
		}


		$html[] = "</div>";
		return implode("\n", $html);
	}

	private function _getNotepads(){
		$notepads = array();
		if(!is_null($this->item) && $this->item instanceof SOYShop_Item){
			try{
				$notepads = self::dao()->getByItemId($this->item->getId());
			}catch(Exception $e){
				//
			}
		}else if(!is_null($this->category) && $this->category instanceof SOYShop_Category){
			try{
				$notepads = self::dao()->getByCategoryId($this->category->getId());
			}catch(Exception $e){
				//
			}
		}else if(!is_null($this->user) && $this->user instanceof SOYShop_User){
			try{
				$notepads = self::dao()->getByUserId($this->user->getId());
			}catch(Exception $e){
				//
			}
		}
		return $notepads;
	}

	private function _buildLink(){
		$link = SOY2PageController::createLink("Extension.Detail.common_notepad");
		$params = array();

		if(!is_null($this->item) && $this->item instanceof SOYShop_Item){
			$params[] = "item_id=" . $this->item->getId();
		}else if(!is_null($this->category) && $this->category instanceof SOYShop_Category){
			$params[] = "category_id=" . $this->category->getId();
		}else if(!is_null($this->user) && $this->user instanceof SOYShop_User){
			$params[] = "user_id=" . $this->user->getId();
		}

		if(!is_null($this->pluginId)) $params[] = "plugin_id=" . $this->pluginId;

		if(count($params)) $link .= "?" . implode("&", $params);
		return $link;
	}

	private function _buildList($notepads){
		$js = array();
		$js[] = "<div id=\"notepad_list\"></div>";
		$js[] = "<script>";
		$js[] = "var editor_url = \"" . SOY2PageController::createLink("Extension.Detail.common_notepad") . "\"";
		$js[] = "var notepads = [";
		foreach($notepads as $notepad){
			$js[] = "	{id:" . $notepad->getId() .", title:\"" . $notepad->getTitle() . "\", create_date:\"" . date("Y-m-d H:i:s", $notepad->getCreateDate()) . "\"},";
		}
		$js[] = "];";
		$js[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/list.js");
		$js[] = "</script>";

		return implode("\n", $js);
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.common_notepad.domain.SOYShop_NotepadDAO");
			$dao = SOY2DAOFactory::create("SOYShop_NotepadDAO");
		}
		return $dao;
	}

	function setItem(SOYShop_Item $item){
		$this->item = $item;
	}
	function setCategory(SOYShop_Category $category){
		$this->category = $category;
	}
	function setUser(SOYShop_User $user){
		$this->user = $user;
	}
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}
}
