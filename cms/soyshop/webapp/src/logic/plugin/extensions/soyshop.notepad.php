<?php
class SOYShopNotepadBase implements SOY2PluginAction{

	private $pluginId;	//エディタの画面で出力するパンくずで拡張ポイントからの出力ページのURLを出力したいときに利用する

	/**
	 * @param item_id
	 * @return html
	 */
	function buildItemNotepad(int $itemId){
		return "";
	}

	/**
	 * @param category_id
	 * @return html
	 */
	function buildCategoryNotepad(int $categoryId){
		return "";
	}

	/**
	 * @param user_id
	 * @return html
	 */
	function buildUserNotepad(int $userId){
		return "";
	}

	function getPluginId(){
		return $this->pluginId;
	}

	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}
}

class SOYShopNotepadDelegateAction implements SOY2PluginDelegateAction{

	private $mode;
	private $id;
	private $pluginId;	//エディタの画面で出力するパンくずで拡張ポイントからの出力ページのURLを出力したいときに利用する
	private $_html;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->setPluginId($this->pluginId);
		switch($this->mode){
			case "item":
				$this->_html = $action->buildItemNotepad((int)$this->id);
				break;
			case "category":
				$this->_html = $action->buildCategoryNotepad((int)$this->id);
				break;
			case "user":
				$this->_html = $action->buildUserNotepad((int)$this->id);
				break;
		}
	}

	function getHtml(){
		return $this->_html;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setId($id){
		$this->id = $id;
	}
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}
}
SOYShopPlugin::registerExtension("soyshop.notepad", "SOYShopNotepadDelegateAction");
