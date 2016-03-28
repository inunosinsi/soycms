<?php
/**
 * 複数ラベル、複数ブログ割り当てコンポーネント
 */
class MultiLabelBlockComponent implements BlockComponent{

	private $siteId = null;
	private $oldSiteId = null;
	private $mapping = array();
	private $labelIds = array();

	private $displayCountFrom;
	private $displayCountTo;
	private $order = self::ORDER_DESC;//記事の並び順

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){

		//DSNを切り替える
		if(is_null($this->siteId)){
			$this->siteId = UserInfoUtil::getSite()->getId();
		}else if($this->oldSiteId != $this->siteId){
			$this->mapping = array();
			$this->labelIds = array();
		}

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

		return SOY2HTMLFactory::createInstance("MultiLabelBlockComponent_FormPage",array(
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

		//古いDSNのバックアップ
		$oldDsn = null;

		//siteのDsn
		$dsn = null;

		$array = array();
		$urlMapping = array();
		$blogTitleMapping = array();
		$blogUrlMapping = array();

		try{
			//DSNを切り替える、ついでにサイトのURLを取得
			//自サイトでもサイトのURL取得
			$oldDsn = SOY2DAOConfig::Dsn();
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

			$site = $siteDAO->getById($this->siteId);
			SOY2DAOConfig::Dsn($site->getDataSourceName());

			$dsn = $site->getDataSourceName();

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

			//エントリー取得

			if(defined("CMS_PREVIEW_ALL")){
				$array = $logic->getByLabelIds($this->getLabelIds());
			}else{
				$array = $logic->getOpenEntryByLabelIds($this->getLabelIds(),false);
			}

			//ブログページを作る
			$entryLabelDAO= SOY2DAOFactory::create("cms.EntryLabelDAO");

			$blogPageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
			foreach($array as $key => $entry){
				foreach($this->mapping as $labelId => $blogId){
					try{
						$entryLabelDAO->getByParam($labelId,$entry->getId());
						$blogPage = $blogPageDAO->getById($blogId);
						$url = $siteUrl . $blogPage->getEntryPageURL();
						$urlMapping[$entry->getId()] = $url;
						$blogTitle = $blogPage->getTitle();
						$blogTitleMapping[$entry->getId()] = $blogTitle;
						$blogUrl = $siteUrl . $blogPage->getTopPageURL();
						$blogUrlMapping[$entry->getId()] = $blogUrl;
						continue;
					}catch(Exception $e){

					}
				}
			}

			$entryLabelDAO = null;
			$blogPageDAO = null;
		}catch(Exception $e){
			//do nothing
		}

		$inst = SOY2HTMLFactory::createInstance("MultiLabelBlockComponent_ViewPage",array(
			"list" => $array,
			"url" => $urlMapping,
			"blogTitle" => $blogTitleMapping,
			"blogUrl" => $blogUrlMapping,
			"soy2prefix"=>"block",
			"dsn" => $dsn
		));

		//Dsn戻す
		if($oldDsn)SOY2DAOConfig::Dsn($oldDsn);

		return $inst;
	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){

		try{
			$res = count($this->mapping) . CMSMessageManager::get("SOYCMS_NUMBER_OF_SET_LABELS");
			$res .= (strlen($this->displayCountFrom) OR strlen($this->displayCountTo)) ? " ". $this->displayCountFrom."-".$this->displayCountTo : "" ;
		}catch(Exception $e){
			$res = CMSMessageManager::get("SOYCMS_NO_SETTING");
		}
		return $res;

	}

	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_BLOG_LINK_BLOCK");
	}

	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_BLOG_LINK_BLOCK_DESCRIPTION");
	}


	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	function getMapping() {
		if(!empty($this->mapping) && strlen(implode("",array_values($this->mapping))) == 0)$this->mapping = array();
		return $this->mapping;
	}
	function setMapping($mapping) {
		$this->mapping = $mapping;
	}
	function getLabelIds() {
		if(empty($this->labelIds) || !empty($this->mapping))$this->labelIds = array_keys($this->mapping);
		return $this->labelIds;
	}
	function setLabelIds($labelIds) {
		$this->labelIds = $labelIds;
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

	function getOldSiteId() {
		return $this->oldSiteId;
	}
	function setOldSiteId($oldSiteId) {
		$this->oldSiteId = $oldSiteId;
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
}

class MultiLabelBlockComponent_FormPage extends HTMLPage{

	private $siteId = "";
	private $sites = array();
	private $entity;
	private $blogPages = array();

	function MultiLabelBlockComponent_FormPage(){
		HTMLPage::HTMLPage();

	}

	function execute(){

		//サイト変更機能
		$this->createAdd("sites_form","HTMLForm");
		$this->createAdd("site","HTMLSelect",array(
			"options" => $this->sites,
			"property" => "siteName",
			"name" => "object[siteId]",
			"selected" => $this->siteId
		));

		/* 以下、通常フォーム */

		$this->createAdd("label_select","HTMLSelect",array(
			"options"=>$this->getLabelList(),
			"property" => "displayCaption"
		));

		$this->createAdd("blog_select","HTMLSelect",array(
			"options"=>$this->blogPages,
			"property" => "title"
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

		$this->createAdd("label_list","MultiLabelList_LabelList",array(
			"labels"=>$this->getLabelList(),
			"blogs" => $this->blogPages,
			"list" => $this->entity->getMapping()
		));

		$this->createAdd("old_site_id","HTMLInput",array(
			"name" => "object[oldSiteId]",
			"value" => $this->siteId
		));

		$this->createAdd("main_form","HTMLForm",array());

	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	function setEntity(MultiLabelBlockComponent $block){
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
		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "MultiLabelBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "MultiLabelBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "MultiLabelBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";
		}
	}

	function setSites($sites){
		$this->sites = $sites;
	}

	function setSiteId($id){
		$this->siteId = $id;
	}

}


class MultiLabelBlockComponent_ViewPage extends HTMLList{

	var $url = array();
	var $blogTitle = array();
	var $blogUrl = array();

	private $dsn;

	function setUrl($url){
		$this->url = $url;
	}

	function setBlogTitle($blogTitle){
		$this->blogTitle = $blogTitle;
	}

	function setBlogUrl($blogUrl){
		$this->blogUrl = $blogUrl;
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
		//entry title
		$url = (isset($this->url[$entity->getId()])) ? $this->url[$entity->getId()] : "" ;

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = ( strlen($url) >0 ) ? $url.rawurlencode($entity->getAlias()) : "" ;

		if(strlen($entryUrl) >0){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		//blog title
		$blogUrl = (isset($this->blogUrl[$entity->getId()])) ? $this->blogUrl[$entity->getId()] : "";
		$blogTitle = (isset($this->blogTitle[$entity->getId()])) ? $this->blogTitle[$entity->getId()] : "";
		$hBlogTitle = htmlspecialchars($blogTitle, ENT_QUOTES, "UTF-8");

		if(strlen($blogUrl) >0){
			$hBlogTitle = "<a href=\"".htmlspecialchars($blogUrl, ENT_QUOTES, "UTF-8")."\">".$hBlogTitle."</a>";
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

		//Blog Title link
		$this->createAdd("blog_title", "CMSLabel", array(
			"html" => $hBlogTitle,
			"soy2prefix"=>"cms"

		));

		//Blog Title plain
		$this->createAdd("blog_title_plain", "CMSLabel" , array(
			"text" => $blogTitle,
			"soy2prefix"=>"cms"
		));

		//Blog link
		$this->createAdd("blog_link", "HTMLLink" , array(
			"link" => $blogUrl,
			"soy2prefix"=>"cms"
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


class MultiLabelList_LabelList extends HTMLList{
	private $labels = array();
	private $blogs = array();

	function populateItem($entity,$key){

		$labelId = $key;
		$blogId = $entity;

		$this->createAdd("label","HTMLLabel",array(
			"text"=> (isset($this->labels[$labelId])) ? $this->labels[$labelId]->getCaption() : ""
		));

		$this->createAdd("title","HTMLLabel",array(
			"text"=> (isset($this->blogs[$blogId])) ? $this->blogs[$blogId] : ""
		));

		$this->createAdd("delete_button","HTMLInput",array(
			"name" => "delete",
			"type" => "submit",
			"value" => CMSMessageManager::get("SOYCMS_DELETE"),
			"onclick" => 'add_reload_input(this);delete_mapping($(\'#mapping_'.$labelId.'\'));'
		));

		$this->createAdd("mapping","HTMLInput",array(
			"id" => "mapping_".$labelId,
			"class" => "mapping_input",
			"name" => "object[mapping][".$labelId."]",
			"value" => $blogId,
			"type" => "hidden"
		));
	}


	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
	function getBlogs() {
		return $this->blogs;
	}
	function setBlogs($blogs) {
		$this->blogs = $blogs;
	}
}
?>