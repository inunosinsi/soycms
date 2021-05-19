<?php
/**
 * スクリプト読み込み用
 */
class PluginBlockComponent implements BlockComponent{

    private $pluginId;
	private $isStickUrl = false;
	private $blogPageId;

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){

        $pluginIds = array();
        $onLoad = CMSPlugin::getEvent('onPluginBlockAdminReturnPluginId');
		foreach($onLoad as $plugin){
			$func = $plugin[0];
            $res = call_user_func($func);
            if(isset($res) && strlen($res)) $pluginIds[] = htmlspecialchars($res, ENT_QUOTES, "UTF-8");
		}

		return SOY2HTMLFactory::createInstance("PluginBlockComponent_FormPage",array(
			"entity" => $this,
			"blogPages" => SOY2Logic::createInstance("logic.site.Page.BlogPageLogic")->getBlogPageList(),//ブログ一覧を取得する
            "pluginIds" => $pluginIds
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	function getViewPage($page){

        $array = array();
        $articlePageUrl = "";


        $onLoad = CMSPlugin::getEvent('onPluginBlockLoad');
		foreach($onLoad as $pluginId => $plugin){
            if($this->getPluginId() !== $pluginId) continue;
			$func = $plugin[0];
            $array = call_user_func($func, array());
		}

        $articlePageUrl = "";
		$categoryPageUrl = "";
		if($this->isStickUrl){
			try{
				$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
				$blogPage = $pageDao->getById($this->blogPageId);

				if(defined("CMS_PREVIEW_MODE")){
					$articlePageUrl = SOY2PageController::createLink("Page.Preview") ."/". $blogPage->getId() . "?uri=". $blogPage->getEntryPageURL();
				}else{
					$articlePageUrl = $page->getSiteRootUrl() . $blogPage->getEntryPageURL();
					$categoryPageUrl = $page->getSiteRootUrl() . $blogPage->getCategoryPageURL();
				}
			}catch(Exception $e){
				$this->isStickUrl = false;
			}
		}

		SOY2::import("site_include.block._common.BlockEntryListComponent");
		SOY2::import("site_include.blog.component.CategoryListComponent");
		return SOY2HTMLFactory::createInstance("BlockEntryListComponent",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"categoryPageUrl" => $categoryPageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){

        if(strlen($this->getPluginId())){
            return $this->getPluginId();
        }else{
            return "設定なし";
        }
	}

	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_PLUGIN_BLOCK");
	}

	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_PLUGIN_BLOCK_DESCRIPTION");
	}

    function getPluginId() {
        return $this->pluginId;
    }
    function setPluginId($pluginId) {
        $this->pluginId = $pluginId;
    }
	function getIsStickUrl() {
		return $this->isStickUrl;
	}
	function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}
	function getBlogPageId() {
		return $this->blogPageId;
	}
	function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	public function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	public function setDisplayCountFrom($displayCountFrom) {
		$cnt = (strlen($displayCountFrom) && is_numeric($displayCountFrom)) ? (int)$displayCountFrom : null;
		$this->displayCountFrom = $cnt;
	}

	public function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	public function setDisplayCountTo($displayCountTo) {
		$cnt = (strlen($displayCountTo) && is_numeric($displayCountTo)) ? (int)$displayCountTo : null;
		$this->displayCountTo = $cnt;
	}
}


class PluginBlockComponent_FormPage extends HTMLPage{

	private $entity;
	private $blogPages = array();
    private $pluginIds = array();

	function __construct(){
		parent::__construct();

	}

	function execute(){

		$this->createAdd("no_stick_url","HTMLHidden",array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));

		$this->addCheckBox("stick_url", array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_BLOCK_ADD_ENTRY_LINK_TO_THE_TITLE"),
			"value" => 1,
			"selected" => $this->entity->getIsStickUrl(),
			"visible" =>  (count($this->blogPages) > 0)
		));

		$style = SOY2HTMLFactory::createInstance("SOY2HTMLStyle");
		$style->display = ($this->entity->getIsStickUrl()) ? "" : "none";

		$this->addSelect("blog_page_list", array(
			"name" => "object[blogPageId]",
			"selected" => $this->entity->getBlogPageId(),
			"options" => $this->blogPages,
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->addLabel("blog_page_list_label", array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SELECT_BLOG_TITLE"),
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

        $this->addSelect("plugin_id_list", array(
            "name" => "object[pluginId]",
            "selected" => $this->entity->getPluginId(),
            "options" => $this->pluginIds,
            "visible" => (count($this->pluginIds) > 0)
        ));

		$this->addForm("main_form", array());

		if(count($this->blogPages) === 0){
			DisplayPlugin::hide("blog_link");
		}
	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	function setEntity(PluginBlockComponent $block){
		$this->entity = $block;
	}

	/**
	 * ブログページを渡す
	 *
	 * array(ページID => )
	 */
	function setBlogPages($pages){
		$this->blogPages = $pages;
	}

    function setPluginIds($pluginIds){
        $this->pluginIds = $pluginIds;
    }

    /**
     *  ラベルオブジェクトのリストを返す
     *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
     */
    function getLabelList(){
    	$dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $dao->get();
    }

	function getTemplateFilePath(){
		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "PluginBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
            return CMS_BLOCK_DIRECTORY . "PluginBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "PluginBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";
		}
	}
}
