<?php
class SOYShopTitleFormatBase implements SOY2PluginAction{

	/**
	 * 商品一覧のタイトルフォーマットを増やす
	 * @return array(array(label=>"", "format"=>"")...)
	 */
	function titleFormatOnListPage(){
		return array();
	}

	/**
	 * 商品検索のタイトルフォーマットを増やす
	 * @return array(array(label=>"", "format"=>"")...)
	 */
	function titleFormatOnSearchPage(){
		return array();
	}

	/**
	 * @param string
	 * @return string
	 */
	function convertOnListPage(string $title){
		return "";
	}

	/**
	 * @param string
	 * @return string
	 */
	function convertOnSearchPage(string $title){
		return array();
	}
}
class SOYShopTitleFormatDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";	//ページ種別
	private $_formats = array();
	private $format;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
    	if($action instanceof SOYShopTitleFormatBase){
			if(!defined("SOYSHOP_ADMIN_PAGE")) define("SOYSHOP_ADMIN_PAGE", false);
			if(!class_exists("SOYShop_Page")) SOY2::import("domain.site.SOYShop_Page");

			if(SOYSHOP_ADMIN_PAGE){	//管理画面側
				switch($this->mode){
					case SOYShop_Page::TYPE_LIST:
						$arr = $action->titleFormatOnListPage();
						break;
					case SOYShop_page::TYPE_SEARCH:
						$arr = $action->titleFormatOnSearchPage();
						break;
					default:
						$arr = array();
				}

				if(count($arr)){
					foreach($arr as $v){
						if(
							(isset($v["label"]) && is_string($v["label"]) && strlen($v["label"])) &&
							(isset($v["format"]) && is_string($v["format"]) && strlen($v["format"]))
						){
							$this->_formats[$moduleId][] = $v;
						}
					}
				}
			}else{	//公開側
				$fmt = (is_string($this->getFormat())) ? trim($this->getFormat()) : "";
				if(strlen($fmt)){
					switch($this->mode){
						case SOYShop_Page::TYPE_LIST:
							$res = $action->convertOnListPage($fmt);
							break;
						case SOYShop_page::TYPE_SEARCH:
							$res = $action->convertOnSearchPage($fmt);
							break;
						default:
							$res = "";
					}
					if(is_string($res) && strlen($res)) $this->setFormat($res);
				}
			}
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function getFormats(){
		return $this->_formats;
	}
	function getFormat(){
		return $this->format;
	}
	function setFormat($format){
		$this->format = $format;
	}
}
SOYShopPlugin::registerExtension("soyshop.title.format","SOYShopTitleFormatDeletageAction");
