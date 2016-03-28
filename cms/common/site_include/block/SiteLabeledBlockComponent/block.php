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
	function getFormPage(){

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
	function getViewPage($page){

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

			$this->displayCountFrom = max($this->displayCountFrom,1);//0件目は認めない→１件目に変更

			if(is_numeric($this->displayCountTo)){
				$logic->limit = $this->getDisplayCountTo()- (int)$this->getDisplayCountFrom()+1;//n件目～m件目はm-n+1個のエントリ
			}

			if(is_numeric($this->displayCountFrom)){
				$logic->offset = $this->displayCountFrom-1;//offsetは0スタートなので、n件目=offset:n-1
			}

			if($this->order == self::ORDER_ASC){
				$logic->setReverse(true);
			}

			if(defined("CMS_PREVIEW_ALL")){
				$array = $logic->getByLabelId($this->labelId);
			}else{
				$array = $logic->getOpenEntryByLabelId($this->labelId);
			}

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

				}catch(Exception $e){
					$this->isStickUrl = false;
				}
			}
		}catch(Exception $e){
			//do nothing
		}

		SOY2DAOConfig::Dsn($oldDsn);

		return SOY2HTMLFactory::createInstance("SiteLabeledBlockComponent_ViewPage",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"dsn" => $siteDsn
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){

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
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_ANOTHER_SOYCMSSITE_LABELED_ENTRY_BLOCK");
	}

	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_ANOTHER_SOYCMSSITE_LABELED_ENTRY_BLOCK_DESCRIPTION");
	}


	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}

	function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	function setDisplayCountFrom($displayCountFrom) {
		$this->displayCountFrom = $displayCountFrom;
	}

	function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	function setDisplayCountTo($displayCountTo) {
		$this->displayCountTo = $displayCountTo;
	}

	function getBlogPageId() {
		return $this->blogPageId;
	}
	function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	function getIsStickUrl() {
		return $this->isStickUrl;
	}
	function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}

	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
}

class SiteLabeledBlockComponent_FormPage extends HTMLPage{

	private $siteId = "";
	private $sites = array();
	private $entity;
	private $blogPages = array();

	function SiteLabeledBlockComponent_FormPage(){
		HTMLPage::HTMLPage();

	}

	function execute(){

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

		$this->createAdd("display_number_start","HTMLInput",array(
			"value"=>$this->entity->getDisplayCountFrom(),
			"name"=>"object[displayCountFrom]"
		));
		$this->createAdd("display_number_end","HTMLInput",array(
			"value"=>$this->entity->getDisplayCountTo(),
			"name"=>"object[displayCountTo]"
		));

		$this->createAdd("display_order_asc","HTMLCheckBox",array(
			"type"      => "radio",
			"name"      => "object[order]",
			"value"     => BlockComponent::ORDER_ASC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_ASC,
			"elementId" => "display_order_asc",
		));
		$this->createAdd("display_order_desc","HTMLCheckBox",array(
			"type"      => "radio",
			"name"      => "object[order]",
			"value"     => BlockComponent::ORDER_DESC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_DESC,
			"elementId" => "display_order_desc",
		));

		$this->createAdd("no_stick_url","HTMLHidden",array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));
		$this->createAdd("stick_url","HTMLCheckBox",array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_BLOCK_ADD_ENTRY_LINK_TO_THE_TITLE"),
			"value" => 1,
			"selected" => $this->entity->getIsStickUrl(),
			"visible" =>  (count($this->blogPages) > 0)
		));

		$style = SOY2HTMLFactory::createInstance("SOY2HTMLStyle");
		$style->display = ($this->entity->getIsStickUrl()) ? "" : "none";

		$this->createAdd("blog_page_list","HTMLSelect",array(
			"name" => "object[blogPageId]",
			"selected" => $this->entity->getBlogPageId(),
			"options" => $this->blogPages,
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));
		$this->createAdd("blog_page_list_label","HTMLLabel",array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SELECT_BLOG_TITLE"),
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		if(count($this->blogPages) === 0){
			DisplayPlugin::hide("blog_link");
		}

		$this->createAdd("site_hidden","HTMLInput",array(
			"name" => "object[siteId]",
			"value" => $this->siteId
		));

		$this->createAdd("main_form","HTMLForm",array());

		//サイト変更機能
		$this->createAdd("sites_form","HTMLForm");
		$this->createAdd("site","HTMLSelect",array(
			"options" => $this->sites,
			"property" => "siteName",
			"name" => "object[siteId]",
			"selected" => $this->siteId
		));

	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	function setEntity(SiteLabeledBlockComponent $block){
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

   /**
     *  ラベルオブジェクトのリストを返す
     *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
     */
    function getLabelList(){
    	$dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $dao->get();
    }

	function getTemplateFilePath(){

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "SiteLabeledBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "SiteLabeledBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "SiteLabeledBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";
		}
	}

	function setSites($sites){
		$this->sites = $sites;
	}

	function setSiteId($id){
		$this->siteId = $id;
	}

}


class SiteLabeledBlockComponent_ViewPage extends HTMLList{

	var $isStickUrl;
	var $articlePageUrl;
	var $blogPageId;
	private $dsn = false;

	function setIsStickUrl($flag){
		$this->isStickUrl = $flag;
	}

	function setArticlePageUrl($articlePageUrl){
		$this->articlePageUrl = $articlePageUrl;
	}

	function setBlogPageId($id){
		$this->blogPageId = $id;
	}

	function getStartTag(){

		return parent::getStartTag();
	}

	/**
	 * 実行前後にDSNの書き換えを実行
	 */
	function execute(){

		if($this->dsn)$old = SOY2DAOConfig::Dsn($this->dsn);

		parent::execute();

		if($this->dsn)SOY2DAOConfig::Dsn($old);
	}



	function populateItem($entity){

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = $this->articlePageUrl.rawurlencode($entity->getAlias());

		if($this->isStickUrl){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $entity->getId(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"html"=> $hTitle,
			"soy2prefix"=>"cms"
		));
		$this->createAdd("content","CMSLabel",array(
			"html"=>$entity->getContent(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entity->getMore(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("create_date","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("create_time","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		//entry_link追加
		$this->createAdd("entry_link","HTMLLink",array(
			"link" => $entryUrl,
			"soy2prefix"=>"cms"
		));

		//リンクの付かないタイトル 1.2.6～
		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entity->getTitle(),
			"soy2prefix"=>"cms"
		));

		//1.2.7～
		$this->createAdd("more_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));
		
		$this->createAdd("more_link_no_anchor", "HTMLLink", array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl,
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		//1.7.5~
		$this->createAdd("update_date","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("update_time","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->createAdd("entry_url","HTMLLabel",array(
			"text"=>$entryUrl,
			"soy2prefix"=>"cms",
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entity->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entity));
	}


	function getDsn() {
		return $this->dsn;
	}
	function setDsn($dsn) {
		$this->dsn = $dsn;
	}
}

class SiteLabelList extends HTMLList{
	private $currentLabel;

	function populateItem($entity){

		$elementID = "label_".$entity->getId();

		$this->createAdd("radio","HTMLCheckBox",array(
			"value"     => $entity->getId(),
			"selected"  => ((string)$this->currentLabel == (string)$entity->getId()),
			"elementId" => $elementID
		));
		$this->createAdd("label","HTMLModel",array(
			"for" => $elementID,
		));
		$this->createAdd("label_text","HTMLLabel",array(
			"text" => $entity->getCaption(),
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));

	}

	function getCurrentLabel() {
		return $this->currentLabel;
	}
	function setCurrentLabel($currentLabel) {
		$this->currentLabel = $currentLabel;
	}
}
?>
