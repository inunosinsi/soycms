<?php
/**
 * カートにその他の処理を追加するための拡張ポイント
 */
class SOYShopOrderSearch implements SOY2PluginAction{

	/**
	 * @return html
	 */
	function button(){
		return null;
	}

	/**
	 * @return array("queries" => "", "binds" => array())
	 * queriesはサブクエリ形式でSQL構文の配列を返せば良い id IN (SELECT id FROM soyshop_order 〜以下省略〜)
	 */
	function setParameter($params){
		return array("queries" => "", "binds" => array());
	}

	/**
	 * @return array("label" => "", "form" => "")
	 * formの値に挿入するフォームのnameはsearch[customs][モジュールID][ユニークなパラメータ]にしなければ動作しない
	 **/
	function searchItems($params){
		return array("label" => "", "form" => "");
	}
}
class SOYShopOrderSearchDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $params;
	private $_buttons = array();
	private $_items = array();
	private $_queries = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopOrderSearch){
			$params = (isset($this->params[$moduleId])) ? $this->params[$moduleId] : array();
			switch($this->mode){
				case "button":
					$btn = $action->button();
					if(is_string($btn) && strlen($btn)) $this->_buttons[$moduleId] = $btn;
					break;
				case "search":
					$this->_queries[$moduleId] = $action->setParameter($params);
					break;
				case "form":
				default:
					$this->_items[$moduleId] = $action->searchItems($params);
					break;
			}
		}
	}
	function getButtons(){
		return $this->_buttons;
	}
	function getSearchItems(){
		return $this->_items;
	}
	function getQueries(){
		return $this->_queries;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setParams($params){
		$this->params = $params;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.search", "SOYShopOrderSearchDeletageAction");
