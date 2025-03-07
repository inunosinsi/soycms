<?php
/**
 * エントリー表示用のブロックコンポーネント
 */
class LabeledBlockComponent implements BlockComponent{

	const ON = 1;
	const OFF = 0;

	private $labelId;
	private $displayCountFrom;
	private $displayCountTo;
	private $enablePaging = false;
	private $pagingParameter = "";
	private $isStickUrl = false;
	private $blogPageId;
	private $sort = self::SORT_CDATE;	//記事の並び順
	private $order = self::ORDER_DESC;//記事の並び順
	private $isCallEventFunc = self::ON;	//公開側でHTMLの表示の際にカスタムフィールドの拡張ポイントを読み込むか？

	// Deprecated: Creation of dynamic property LabeledBlockComponent::$blockId is deprecated in 〜対策
	public $blockId;

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	public function getFormPage(){
		return SOY2HTMLFactory::createInstance("LabeledBlockComponent_FormPage",array(
			"entity" => $this,
			"blogPages" => SOY2Logic::createInstance("logic.site.Page.BlogPageLogic")->getBlogPageList()
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	public function getViewPage($page){
		$onLoads = CMSPlugin::getEvent("onPageOutputLabelRead");
		if(is_array($onLoads) && count($onLoads)) {
			foreach($onLoads as $plugin){
				$res = call_user_func($plugin[0], array('labelId' => (int)$this->labelId));
				if(is_numeric($res) && $res > 0 && $res !== (int)$this->labelId){
					$this->labelId = (int)$res;
				}
			}
		}

		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		$logic->setBlockClass(get_class($this));

		$this->displayCountFrom = max($this->displayCountFrom,1);//0件目は認めない→１件目に変更

		if(is_numeric($this->displayCountTo)) $limit = $this->getDisplayCountTo()- (int)$this->getDisplayCountFrom()+1;//n件目～m件目はm-n+1個のエントリ
		if(is_numeric($this->displayCountFrom)) $offset = $this->displayCountFrom-1;//offsetは0スタートなので、n件目=offset:n-1

		$pageNumber = 1;
		//2ページ目以降の場合
		if(  $this->enablePaging && strlen($this->pagingParameter)
		  && isset($_GET[$this->pagingParameter]) && is_numeric($_GET[$this->pagingParameter]) && $_GET[$this->pagingParameter] >= 2
		  && $limit > 0 && isset($offset)
		){
			$pageNumber = (int)$_GET[$this->pagingParameter];
			$offset += ($pageNumber -1) * $limit;
		}


		$logic->setSort((int)$this->sort);
		if($this->order == self::ORDER_ASC) $logic->setReverse(true);

		//制限なしで数を数える：ページ分けする場合のみ必要
		$total = 0;
		$logic->setLimit(null);
		$logic->setOffset(null);
		if($this->enablePaging){
			try{
				if(defined("CMS_PREVIEW_ALL")){
					$array = $logic->getByLabelId((int)$this->labelId);
				}else{
					$array = $logic->getOpenEntryByLabelId((int)$this->labelId);
				}
				$total = count($array);
			}catch(Exception $e){
				//
			}
		}

		//制限をかけてデータ取得
		$array = array();
		if(isset($limit)) $logic->setLimit($limit);
		if(isset($offset)) $logic->setOffset($offset);
		try{
			if(defined("CMS_PREVIEW_ALL")){
				$array = $logic->getByLabelId((int)$this->labelId);
			}else{
				$array = $logic->getOpenEntryByLabelId((int)$this->labelId);
			}
		}catch(Exception $e){
			//do nothing
		}

		//エントリー管理者が変更できるかどうか
		SOY2::import("util.UserInfoUtil");
		$editable = (defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE && !UserInfoUtil::hasSiteAdminRole()) ? soycms_get_label_object((int)$this->labelId)->isEditableByNormalUser() : true;

		$articlePageUrl = "";
		$categoryPageUrl = "";
		if($this->isStickUrl){
			$blogPage = soycms_get_blog_page_object((int)$this->blogPageId);	
			if(is_numeric($blogPage->getId())){
				if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE){
					$articlePageUrl = SOY2PageController::createLink("Page.Preview") ."/". $blogPage->getId() . "?uri=". $blogPage->getEntryPageURL();
				}else{
					$articlePageUrl = $page->getSiteRootUrl() . $blogPage->getEntryPageURL();
					$categoryPageUrl = $page->getSiteRootUrl() . $blogPage->getCategoryPageURL();
				}
			}else{
				$this->isStickUrl = false;
			}
		}

		//ページャー
		if($this->enablePaging){
			try{
				LabeledBlockPagerComponent::buildPager($page, $page->getPageUrl(), count($array), $total, $logic->getOffset(), $pageNumber, $logic->getLimit(), $this->pagingParameter);
			}catch(Exception $e){
				error_log("Pager");
				error_log($e);
			}
		}

		//
		if(!strlen($this->pagingParameter) && ($this->displayCountFrom > 1 && is_null($this->displayCountTo))){
			for($i = 0; $i < ($this->displayCountFrom - 1); $i++){
				array_shift($array);
			}
		}

		if($this->isCallEventFunc == self::ON) CMSPlugin::callEventFunc('onEntryListBeforeOutput', array("entries" => &$array));

		SOY2::import("site_include.block._common.BlockEntryListComponent");
		SOY2::import("site_include.blog.component.CategoryListComponent");
		return SOY2HTMLFactory::createInstance("BlockEntryListComponent",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"categoryPageUrl" => $categoryPageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"editable" => $editable,
			"labelId" => $this->labelId,
			"isCallEventFunc" => ($this->isCallEventFunc == self::ON)
		));
	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	public function getInfoPage(){
		try{
			return soycms_get_label_object((int)$this->labelId)->getCaption() ." ".( (strlen((string)$this->displayCountFrom) OR strlen((string)$this->displayCountTo)) ? $this->displayCountFrom."-".$this->displayCountTo : "" );
		}catch(Exception $e){
			return CMSMessageManager::get("SOYCMS_NO_SETTING");
		}
	}

	/**
	 * @return string コンポーネント名
	 */
	public function getComponentName(){
		return CMSMessageManager::get("SOYCMS_LABELED_ENTRY_BLOCK");
	}

	public function getComponentDescription(){
		return  CMSMessageManager::get("SOYCMS_LABELED_ENTRY_BLOCK_DESCRIPTION");
	}


	public function getLabelId() {
		// @ToDo 多言語化プラグインの設定
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
		$cnt = (strlen($displayCountTo) && is_numeric($displayCountTo)) ? (int)$displayCountTo : null;
		$this->displayCountTo = $cnt;
	}

	public function getEnablePaging(){
		return $this->enablePaging;
	}
	public function setEnablePaging($enablePaging){
		$this->enablePaging = $enablePaging;
	}

	public function getPagingParameter(){
		return $this->pagingParameter;
	}
	public function setPagingParameter($pagingParameter){
		$this->pagingParameter = $pagingParameter;
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

	public function getSort(){
		return $this->sort;
	}
	public function setSort($sort){
		$this->sort = $sort;
	}

	public function getOrder(){
		return $this->order;
	}
	public function setOrder($order){
		$this->order = $order;
	}

	public function getIsCallEventFunc(){
		return $this->isCallEventFunc;
	}
	public function setIsCallEventFunc($isCallEventFunc){
		$this->isCallEventFunc = $isCallEventFunc;
	}
}


class LabeledBlockComponent_FormPage extends HTMLPage{

	private $entity;
	private $blogPages = array();


	public function execute(){

		HTMLHead::addLink("editor_css",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		$this->createAdd("label_loop","LabeledBlock_LabelList",array(
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

		//ページ分割設定
		$this->addCheckBox("enable_paging",array(
			"name" => "object[enablePaging]",
			"value" => 1,
			"isBoolean" => true,
			"elementId" => "enable_paging",
			"selected" => $this->entity->getEnablePaging(),
		));

		//ページ番号に使うパラメーター（GET変数名）
		$this->addInput("paging_parameter",array(
			"name" => "object[pagingParameter]",
			"value" => $this->entity->getPagingParameter(),
		));

		$this->addSelect("display_sort", array(
			"name"	  => "object[sort]",
			"options"	 => array(
				BlockComponent::SORT_CDATE => "作成日",
				BlockComponent::SORT_UDATE => "更新日"
			),
			"selected"  => $this->entity->getSort(),
			"indexOrder" => true
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

		$this->addHidden("no_stick_url", array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));
		$this->addCheckBox("stick_url", array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_ADD_HYPERLINK_TO_BLOG_ENTRYPAGE"),
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

		$this->addCheckBox("is_call_event_func", array(
			"name" => "object[isCallEventFunc]",
			"value" => 1,
			"label" => "カスタムフィールドの拡張ポイントを実行します",
			"selected" => $this->entity->getIsCallEventFunc()
		));

		$this->addForm("main_form", array());

		if(count($this->blogPages) === 0) DisplayPlugin::hide("blog_link");
	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	public function setEntity(LabeledBlockComponent $block){
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
		return soycms_get_hash_table_dao("label")->get();
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
}

class LabeledBlock_LabelList extends HTMLList{
	private $currentLabel;

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$elementID = "label_".$id;

		$this->addCheckBox("radio", array(
			"value"	 => $id,
			"selected"  => ((int)$this->currentLabel === $id),
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


class LabeledBlockPagerComponent{

	/**
	 * @param CMSPage etc, string, int, int, int, int, int, string
	 */
	static public function buildPager($appComponent, string $pageUrl, int $count, int $total, int $offset, int $page, int $limit, string $pagerParamKey){
		$start = $offset;
		$end = $start + $count;
		if($end > 0 && $start == 0) $start = 1;

		$pager = new LabeledBlockPagerLogic();
		$pager->setPageUrl($pageUrl);
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		$pager->setPagerParamKey($pagerParamKey);

		$query = $_GET;
		if(isset($query[$pagerParamKey]))unset($query[$pagerParamKey]);
		$pager->setQuery($query);

		//件数情報表示
		$appComponent->addLabel("count_start", array(
			"soy2prefix" => "cms",
			"text" => $pager->getStart()
		));
		$appComponent->addLabel("count_end", array(
			"soy2prefix" => "cms",
			"text" => $pager->getEnd()
		));
		$appComponent->addLabel("count_max", array(
			"soy2prefix" => "cms",
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$appComponent->addModel("has_next_prev_pager", $pager->getHasNextOrPrevParam());
		$appComponent->addModel("has_next_pager", $pager->getHasNextParam());
		$appComponent->addModel("has_prev_pager", $pager->getHasPrevParam());
		$appComponent->addLink("next_pager", $pager->getNextParam());
		$appComponent->addLink("prev_pager", $pager->getPrevParam());
		$appComponent->createAdd("pager_list", "LabeledBlockSimplePager", $pager->getPagerParam());

		//ページへジャンプ
		$appComponent->addForm("pager_jump", array(
			"soy2prefix" => "cms",
			"method" => "get",
			"action" => $pager->getPageURL()."/"
		));
		$appComponent->addSelect("pager_select", array(
			"soy2prefix" => "cms",
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
	}
}

class LabeledBlockPagerLogic {
	private $pageURL;
	private $query;
	private $queryString;

	private $page = 1;
	private $start;
	private $end;
	private $total;
	private $limit = 15;
	private $pagerParamKey = "page";

	public function setPageURL($value){
		if(strpos($value,"/")!==false){
			$this->pageURL = SOY2PageController::createRelativeLink($value);
		}else{
			$this->pageURL = SOY2PageController::createLink($value);
		}
	}
	public function getQuery() {
		return $this->query;
	}
	public function setQuery($query) {
		$this->query = $query;
		$this->queryString = (count($query) > 0) ? "?".http_build_query($query) : "" ;
	}
	public function getQueryString() {
		return $this->queryString;
	}
	public function setPage($value){
		$this->page = $value;
	}
	public function setStart($value){
		$this->start = $value;
	}
	public function setEnd($value){
		$this->end = $value;
	}
	public function setTotal($value){
		$this->total = $value;
	}
	public function setLimit($value){
		$this->limit = $value;
	}
	public function setPagerParamKey($pagerParamKey){
		$this->pagerParamKey = $pagerParamKey;
	}

	public function getCurrentPageURL(){
		return $this->pageURL."/".$this->page;
	}
	public function getPageURL(){
		return $this->pageURL;
	}
	public function getPage(){
		return $this->page;
	}
	public function getStart(){
		return $this->start;
	}
	public function getEnd(){
		return $this->end;
	}
	public function getTotal(){
		return $this->total;
	}
	public function getLimit(){
		return $this->limit;
	}
	public function getOffset(){
		return ($this->page - 1) * $this->limit;;
	}

	public function getHasNextOrPrevParam(){
		return array(
			"soy2prefix" => "cms",
			"visible" => ($this->total > $this->end || $this->page > 1),
		);
	}
	public function getHasNextParam(){
		return array(
			"soy2prefix" => "cms",
			"visible" => ($this->total > $this->end),
		);
	}
	public function getHasPrevParam(){
		return array(
			"soy2prefix" => "cms",
			"visible" => ($this->page > 1),
		);
	}
	public function getNextParam(){
		$query = $this->queryString;
		if($this->total > $this->end){
			if(strlen($query)){
				$query .= "&".$this->pagerParamKey."=".($this->page + 1);
			}else{
				$query  = "?".$this->pagerParamKey."=".($this->page + 1);
			}
		}
		return array(
			"soy2prefix" => "cms",
			"link"	=> $this->pageURL . $query,
			"class"   => ($this->total <= $this->end) ? "pager_disable" : "",
			"visible" => ($this->total >  $this->end),
		);
	}
	public function getPrevParam(){
		$query = $this->queryString;
		if($this->page > 2){
			if(strlen($query)){
				$query .= "&".$this->pagerParamKey."=".($this->page - 1);
			}else{
				$query  = "?".$this->pagerParamKey."=".($this->page - 1);
			}
		}
		return array(
			"soy2prefix" => "cms",
			"link"	=> $this->pageURL . $query,
			"class"   => ($this->page <= 1) ? "pager_disable" : "",
			"visible" => ($this->page > 1),
		);
	}
	public function getPagerParam(){
		$pagers = $this->limit ? range(
			max(1, $this->page - 4),
			max(1, min(ceil($this->total / $this->limit), max(1, $this->page - 4) +9))
		) : array() ;

		return array(
			"soy2prefix" => "cms",
			"url" => $this->pageURL,
			"queryString" => $this->queryString,
			"current" => $this->page,
			"list" => $pagers,
			"pagerParamKey" => $this->pagerParamKey,
		);
	}
	public function getSelectArray(){
		$pagers = $this->limit ? range(
			1,
			(int)($this->total / $this->limit) + 1
		) : array() ;

		$array = array();
		foreach($pagers as $page){
//			$array[ $this->pageURL."/".$page . $this->queryString ] = $page;
			$array[ $page ] = $page;
		}

		return $array;
	}
}

class LabeledBlockSimplePager extends HTMLList{

	private $url;
	private $queryString;
	private $current;
	private $pagerParamKey;

	protected function populateItem($bean){

		$this->addLink("target_link", array(
			"soy2prefix" => "cms",
			"html" => "$bean",
			"link" => $this->url . ( $bean > 1 ? "/" . $bean : "" ) . $this->queryString,
			"class" => ($this->current == $bean) ? "pager_current" : ""
		));
		$this->addModel("target_wrapper",array(
			"soy2prefix" => "cms",
			"class" => ($this->current == $bean) ? "act" : ""
		));

		$query = $this->queryString;
		if($bean > 1){
			if(strlen($query)){
				$query .= "&".$this->pagerParamKey."=".$bean;
			}else{
				$query  = "?".$this->pagerParamKey."=".$bean;
			}
		}
		$this->addLink("target_get_link", array(
			"soy2prefix" => "cms",
			"html" => "$bean",
			"link" => $this->url.$query,
			"class" => ($this->current == $bean) ? "pager_current" : ""
		));

//		$areaId = $_GET["area"][0];
//		$this->createAdd("target_mobile_link","HTMLLink",array(
//			"html" => "$bean",
//			"link" => $this->url. "/r?area[]=".$areaId."&page=" . $bean,
//			"class" => ($this->current == $bean) ? "pager_current" : ""
//		));

	}

	public function getUrl() {
		return $this->url;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	public function getCurrent() {
		return $this->current;
	}
	public function setCurrent($cuttent) {
		$this->current = $cuttent;
	}
	public function setQueryString($queryString) {
		$this->queryString = $queryString;
	}
	public function setPagerParamKey($pagerParamKey){
		$this->pagerParamKey = $pagerParamKey;
	}
}
