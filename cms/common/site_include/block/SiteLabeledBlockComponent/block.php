<?php
/**
 * エントリー表示用のブロックコンポーネント
 */
class SiteLabeledBlockComponent implements BlockComponent{

	private $siteId;
	private $labelId;
	private $displayCountFrom;
	private $displayCountTo;
	private $isStickUrl = false;
	private $blogPageId;
	private $order = self::ORDER_DESC;//記事の並び順

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	public function getFormPage(){

		//ASPでは使用不可
		if(defined("SOYCMS_ASP_MODE")) return false;

		//DSNを切り替える
		if(is_null($this->siteId)){ $this->siteId = UserInfoUtil::getSite()->getId(); }

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

		$sites = $siteDAO->getBySiteType(Site::TYPE_SOY_CMS);

		try{
			$site = $siteDAO->getById($this->siteId);
		}catch(Exception $e){
			$site = UserInfoUtil::getSite();
		}

		SOY2DAOConfig::Dsn($site->getDataSourceName());

		$logic = SOY2Logic::createInstance("logic.site.Page.BlogPageLogic");
		$blogPages = $logic->getBlogPageList();

		return SOY2HTMLFactory::createInstance("SiteLabeledBlockComponent_FormPage",array(
			"entity" => $this,
			"blogPages" => $blogPages,
			"sites" => $sites,
			"siteId" => $this->siteId
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	public function getViewPage($page){

		//ASPでは使用不可
		if(defined("SOYCMS_ASP_MODE")) return false;

		$oldDsn = SOY2DAOConfig::Dsn();
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

		$array = array();
		$articlePageUrl = "";
		$siteDsn = null;

		try{
			$site = $siteDAO->getById($this->siteId);
			SOY2DAOConfig::Dsn($site->getDataSourceName());

			$siteDsn = $site->getDataSourceName();

			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
			$logic->setBlockClass(get_class($this));

			$this->displayCountFrom = max($this->displayCountFrom,1);//0件目は認めない→１件目に変更

			if(is_numeric($this->displayCountTo)){
				$logic->setLimit($this->getDisplayCountTo() - (int)$this->getDisplayCountFrom() + 1);//n件目～m件目はm-n+1個のエントリ
			}

			if(is_numeric($this->displayCountFrom)){
				$logic->setOffset($this->displayCountFrom - 1);//offsetは0スタートなので、n件目=offset:n-1
			}

			if($this->order == self::ORDER_ASC){
				$logic->setReverse(true);
			}

			if(defined("CMS_PREVIEW_ALL")){
				$array = $logic->getByLabelId($this->labelId);
			}else{
				$array = $logic->getOpenEntryByLabelId($this->labelId);
			}

			$articlePageUrl = "";
			$categoryPageUrl = "";
			if($this->isStickUrl){
				try{
					$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
					$blogPage = $pageDao->getById($this->blogPageId);

					$siteUrl = $site->getUrl();

					if($site->getIsDomainRoot()){
						$siteUrl = "/";
					}

					//アクセスしているサイトと同じドメインなら / からの絶対パスにしておく（ケータイでURLに自動でセッションIDが付くように）
					if(strpos($siteUrl,"http://".$_SERVER["SERVER_NAME"]."/")===0){
						$siteUrl = substr($siteUrl,strlen("http://".$_SERVER["SERVER_NAME"]));
					}
					if(strpos($siteUrl,"https://".$_SERVER["SERVER_NAME"]."/")===0){
						$siteUrl = substr($siteUrl,strlen("https://".$_SERVER["SERVER_NAME"]));
					}

					$articlePageUrl = $siteUrl . $blogPage->getEntryPageURL();
					$categoryPageUrl = $siteUrl . $blogPage->getCategoryPageURL();

				}catch(Exception $e){
					$this->isStickUrl = false;
				}
			}
		}catch(Exception $e){
			//do nothing
		}

		SOY2DAOConfig::Dsn($oldDsn);

		SOY2::import("site_include.block._common.SiteLabeledEntryListComponent");
		SOY2::import("site_include.blog.component.CategoryListComponent");
		return SOY2HTMLFactory::createInstance("SiteLabeledEntryListComponent",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"categoryPageUrl" => $categoryPageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"dsn" => $siteDsn
		));
	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	public function getInfoPage(){

		//DSNを切り替える
		if(is_null($this->siteId)){ $this->siteId = UserInfoUtil::getSite()->getId(); }

		$oldDsn = SOY2DAOConfig::Dsn();
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

		try{
			$site = $siteDAO->getById($this->siteId);

			SOY2DAOConfig::Dsn($site->getDataSourceName());
			$dao = SOY2DAOFactory::create("cms.LabelDAO");
			$label = $dao->getById($this->labelId);
			$res = $site->getSiteName() . ": ".$label->getCaption();
			$res .= (strlen($this->displayCountFrom) OR strlen($this->displayCountTo)) ? " ". $this->displayCountFrom."-".$this->displayCountTo : "" ;

		}catch(Exception $e){
			$res = CMSMessageManager::get("SOYCMS_NO_SETTING");
		}

		SOY2DAOConfig::Dsn($oldDsn);

		return $res;

	}

	/**
	 * @return string コンポーネント名
	 */
	public function getComponentName(){
		return CMSMessageManager::get("SOYCMS_ANOTHER_SOYCMSSITE_LABELED_ENTRY_BLOCK");
	}

	public function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_ANOTHER_SOYCMSSITE_LABELED_ENTRY_BLOCK_DESCRIPTION");
	}


	public function getLabelId() {
		return $this->labelId;
	}
	public function setLabelId($labelId) {
		$this->labelId = $labelId;
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
		$this->displayCountTo = $displayCountTo;
	}

	public function getBlogPageId() {
		return $this->blogPageId;
	}
	public function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	public function getIsStickUrl() {
		return $this->isStickUrl;
	}
	public function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}

	public function getSiteId() {
		return $this->siteId;
	}
	public function setSiteId($siteId) {
		$this->siteId = $siteId;
	}

	public function getOrder(){
		return $this->order;
	}
	public function setOrder($order){
		$this->order = $order;
	}
}

class SiteLabeledBlockComponent_FormPage extends HTMLPage{

	private $siteId = "";
	private $sites = array();
	private $entity;
	private $blogPages = array();

	public function execute(){

		//ラベル一覧表示用
		HTMLHead::addLink("editor_css",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		$this->createAdd("label_loop","SiteLabelList",array(
			"list"=>$this->getLabelList(),
			"currentLabel"=>$this->entity->getLabelId()
		));

		$this->addInput("display_number_start", array(
			"value"=>$this->entity->getDisplayCountFrom(),
			"name"=>"object[displayCountFrom]"
		));
		$this->addInput("display_number_end", array(
			"value"=>$this->entity->getDisplayCountTo(),
			"name"=>"object[displayCountTo]"
		));

		$this->addCheckBox("display_order_asc", array(
			"type"	  => "radio",
			"name"	  => "object[order]",
			"value"	 => BlockComponent::ORDER_ASC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_ASC,
			"elementId" => "display_order_asc",
		));
		$this->addCheckBox("display_order_desc", array(
			"type"	  => "radio",
			"name"	  => "object[order]",
			"value"	 => BlockComponent::ORDER_DESC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_DESC,
			"elementId" => "display_order_desc",
		));

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

		if(count($this->blogPages) === 0){
			DisplayPlugin::hide("blog_link");
		}

		$this->addInput("site_hidden", array(
			"name" => "object[siteId]",
			"value" => $this->siteId
		));

		$this->addForm("main_form");

		//サイト変更機能
		$this->addForm("sites_form");
		$this->addSelect("site", array(
			"options" => $this->sites,
			"property" => "siteName",
			"name" => "object[siteId]",
			"selected" => $this->siteId
		));
	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	public function setEntity(SiteLabeledBlockComponent $block){
		$this->entity = $block;
	}

	/**
	 * ブログページを渡す
	 *
	 * array(ページID => )
	 */
	public function setBlogPages($pages){
		$this->blogPages = $pages;
	}

	/**
	 *  ラベルオブジェクトのリストを返す
	 *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
	 */
	private function getLabelList(){
		$dao = SOY2DAOFactory::create("cms.LabelDAO");
		return $dao->get();
	}

	public function getTemplateFilePath(){

		//ext-modeでbootstrap対応画面作成中
		if(defined("EXT_MODE_BOOTSTRAP") && file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html";
		}

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html";
		}
	}

	public function setSites($sites){
		$this->sites = $sites;
	}

	public function setSiteId($id){
		$this->siteId = $id;
	}

}

class SiteLabelList extends HTMLList{
	private $currentLabel;

	protected function populateItem($entity){

		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$elementID = "label_".$id;

		$this->addCheckBox("radio", array(
			"value"	 => $id,
			"selected"  => ((int)$this->currentLabel == $id),
			"elementId" => $elementID
		));
		$this->addModel("label", array(
			"for" => $elementID,
		));
		$this->addLabel("label_text", array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
			         ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";",
		));

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl()
		));
	}

	public function getCurrentLabel() {
		return $this->currentLabel;
	}
	public function setCurrentLabel($currentLabel) {
		$this->currentLabel = $currentLabel;
	}
}
