<?php
SOY2::import("domain.cms.Page");

/**
 * @table BlogPage
 */
class BlogPage extends Page{

	const MODE_TOP = 0;
	const MODE_CATEGORY = 1;
	const MODE_ARCHIVE = 2;
	const MODE_ENTRY = 3;

	const TEMPLATE_ARCHIVE = "archive";
	const TEMPLATE_TOP = "top";
	const TEMPLATE_ENTRY = "entry";
	const TEMPLATE_POPUP = "popup";

	const ENTRY_SORT_DESC = "desc";
	const ENTRY_SORT_ASC = "asc";

	//トップページのURL
	private $topPageUri = "";

	//単体ページのURL
	private $entryPageUri = "article";

	//月別ページのURL
	private $monthPageUri = "month";

	//カテゴリ別ページのURL
	private $categoryPageUri = "category";

	//RSSページのURL
	private $rssPageUri = "feed";

	//トップページの表示件数
	private $topDisplayCount = 10;

	//月別ページの表示件数
	private $monthDisplayCount = 10;

	//カテゴリ別ページの表示件数
	private $categoryDisplayCount = 10;

	//トップページの表示順
	private $topEntrySort = "desc";

	//月別ページの表示順
	private $monthEntrySort = "desc";

	//カテゴリ別ページの表示順
	private $categoryEntrySort = "desc";

	//RSSの表示件数
	private $rssDisplayCount = 10;

	//単体ページの生成フラグ
	private $generateEntryFlag = true;

	//トップページの生成フラグ
	private $generateTopFlag = true;

	//月別ページの生成フラグ
	private $generateMonthFlag = true;

	//カテゴリ別ページの生成フラグ
	private $generateCategoryFlag = true;

	//RSSの生成フラグ
	private $generateRssFlag = true;

	//トップページのタイトルフォーマット
	private $topTitleFormat = "%BLOG%";

	//月別ページのタイトルフォーマット
	private $monthTitleFormat = "%BLOG%";

	//カテゴリー別ページのタイトルフォーマット
	private $categoryTitleFormat = "%BLOG%";

	//単体ページのタイトルフォーマット
	private $entryTitleFormat = "%BLOG%";

	//フィードのタイトルフォーマット
	private $feedTitleFormat = "%BLOG%";


	//使用するラベル一覧
	private $blogLabelId;

	//カテゴリ分けに使うラベル一覧
	private $categoryLabelList = array();

	private $description;

	private $author;

	//コメントのデフォルト承認
	private $defaultAcceptComment;

	private $defaultAcceptTrackback;

	//b_blockの設定
	private $bBlockConfig;

	/**
	* @param startWithSlash /で始まるかどうか
	*/
	function getEntryPageUri($startWithSlash = false) {
		if($startWithSlash && strlen($this->entryPageUri)>0){
			return "/" . $this->entryPageUri;
		}else{
			return $this->entryPageUri;
		}
	}
	function setEntryPageUri($entryPageUri) {
		$this->entryPageUri = $entryPageUri;
	}
	function setCategoryLabelList($list){
		if(!is_array($list))return;
		$this->categoryLabelList = $list;
	}
	function getCategoryLabelList(){
		return $this->categoryLabelList;
	}

	/**
	* トップページのURL
	*/
	function getTopPageURL($withPageUri = true){
		if($withPageUri && strlen($this->getUri()) >0){
			if(strlen($this->getTopPageUri()) >0){
				return $this->getUri() . "/" . $this->getTopPageUri();
			}else{
				return $this->getUri();
			}
		}else{
			return $this->getTopPageUri();
		}
	}
	/**
	* エントリーページのURLを取得（末尾はスラッシュ付き）
	*
	* @param withPageUri ページのUriを追加するかどうか
	*/
	function getEntryPageURL($withPageUri = true){
		$url = "";
		if($withPageUri && strlen($this->getUri()) >0){
			$url .= $this->getUri() . "/";
		}
		if(strlen($this->getEntryPageUri()) >0){
			$url .= $this->getEntryPageUri() . "/";
		}
		return $url;
	}
	/**
	* カテゴリーアーカイブのURL（末尾はスラッシュ付き）
	*/
	function getCategoryPageURL($withPageUri = true){
		$url = "";
		if($withPageUri && strlen($this->getUri()) >0){
			$url .= $this->getUri() . "/";
		}
		if(strlen($this->getCategoryPageUri()) >0){
			$url .= $this->getCategoryPageUri() . "/";
		}
		return $url;
	}
	/**
	* 月別アーカイブのURL（末尾はスラッシュ付き）
	*/
	function getMonthPageURL($withPageUri = true){
		$url = "";
		if($withPageUri && strlen($this->getUri()) >0){
			$url .= $this->getUri() . "/";
		}
		if(strlen($this->getMonthPageUri()) >0){
			$url .= $this->getMonthPageUri() . "/";
		}
		return $url;
	}
	/**
	* RSSページのURL
	*/
	function getRssPageURL($withPageUri = true){
		if($withPageUri && strlen($this->getUri()) >0){
			return $this->getUri() . "/" . $this->getRssPageUri();
		}else{
			return $this->getRssPageUri();
		}
	}

	/**
	* 保存用のstdObjectを返します。
	*/
	function getConfigObj(){

		$obj = new stdClass();

		$obj->topPageUri = $this->topPageUri;
		$obj->entryPageUri = $this->entryPageUri;
		$obj->monthPageUri = $this->monthPageUri;
		$obj->categoryPageUri = $this->categoryPageUri;
		$obj->rssPageUri = $this->rssPageUri;

		$obj->blogLabelId = $this->blogLabelId;
		$obj->categoryLabelList = $this->categoryLabelList;

		$obj->topDisplayCount = $this->topDisplayCount;
		$obj->monthDisplayCount = $this->monthDisplayCount;
		$obj->categoryDisplayCount = $this->categoryDisplayCount;
		$obj->rssDisplayCount = $this->rssDisplayCount;

		$obj->topEntrySort = $this->topEntrySort;
		$obj->monthEntrySort = $this->monthEntrySort;
		$obj->categoryEntrySort = $this->categoryEntrySort;

		$obj->generateTopFlag = $this->generateTopFlag;
		$obj->generateMonthFlag = $this->generateMonthFlag;
		$obj->generateCategoryFlag = $this->generateCategoryFlag;
		$obj->generateRssFlag = $this->generateRssFlag;
		$obj->generateEntryFlag = $this->generateEntryFlag;

		$obj->topTitleFormat = @$this->topTitleFormat;
		$obj->monthTitleFormat = @$this->monthTitleFormat;
		$obj->categoryTitleFormat = @$this->categoryTitleFormat;
		$obj->entryTitleFormat = @$this->entryTitleFormat;
		$obj->feedTitleFormat = @$this->feedTitleFormat;

		$obj->description = @$this->description;
		$obj->author = @$this->author;

		$obj->defaultAcceptComment = @$this->defaultAcceptComment;
		$obj->defaultAcceptTrackback = @$this->defaultAcceptTrackback;

		$obj->bBlockConfig = $this->bBlockConfig;

		return $obj;
	}

	function _getTemplate(){

		$array = @unserialize($this->getTemplate());

		if(!is_array($array)){
			$array = array(
				BlogPage::TEMPLATE_ARCHIVE => "",
				BlogPage::TEMPLATE_TOP => "",
				BlogPage::TEMPLATE_ENTRY => "",
				BlogPage::TEMPLATE_POPUP => "",
			);
		}

		return $array;
	}

	/**
	* アーカイブテンプレート
	*/
	function getArchiveTemplate(){
		$template = $this->_getTemplate();
		return $template[BlogPage::TEMPLATE_ARCHIVE];
	}

	/**
	* ブログトップページ
	*/
	function getTopTemplate(){
		$template = $this->_getTemplate();
		return $template[BlogPage::TEMPLATE_TOP];
	}

	/**
	* エントリーテンプレート
	*/
	function getEntryTemplate(){
		$template = $this->_getTemplate();
		return $template[BlogPage::TEMPLATE_ENTRY];
	}

	/**
	* ポップアップコメントテンプレート
	*/
	function getPopUpTemplate(){
		$template = $this->_getTemplate();
		return $template[BlogPage::TEMPLATE_POPUP];
	}

	function getMonthPageUri() {
		return $this->monthPageUri;
	}
	function setMonthPageUri($monthPageUri) {
		$this->monthPageUri = $monthPageUri;
	}
	function getCategoryPageUri() {
		return $this->categoryPageUri;
	}
	function setCategoryPageUri($categoryPageUri) {
		$this->categoryPageUri = $categoryPageUri;
	}
	function getRssPageUri() {
		return $this->rssPageUri;
	}
	function setRssPageUri($rssPageUri) {
		$this->rssPageUri = $rssPageUri;
	}
	function getTopDisplayCount() {
		return $this->topDisplayCount;
	}
	function setTopDisplayCount($topDisplayCount) {
		$this->topDisplayCount = (int)$topDisplayCount;
	}
	function getMonthDisplayCount() {
		return $this->monthDisplayCount;
	}
	function setMonthDisplayCount($monthDisplayCount) {
		$this->monthDisplayCount = (int)$monthDisplayCount;
	}
	function getCategoryDisplayCount() {
		return $this->categoryDisplayCount;
	}
	function setCategoryDisplayCount($categoryDisplayCount) {
		$this->categoryDisplayCount = (int)$categoryDisplayCount;
	}
	function getRssDisplayCount() {
		return $this->rssDisplayCount;
	}
	function setRssDisplayCount($rssDisplayCount) {
		$this->rssDisplayCount = (int)$rssDisplayCount;
	}

	function getTopEntrySort(){
		return $this->topEntrySort;
	}
	function setTopEntrySort($topEntrySort){
		$this->topEntrySort = $topEntrySort;
	}
	function getMonthEntrySort(){
		return $this->monthEntrySort;
	}
	function setMonthEntrySort($monthEntrySort){
		$this->monthEntrySort = $monthEntrySort;
	}
	function getCategoryEntrySort(){
		return $this->categoryEntrySort;
	}
	function setCategoryEntrySort($categoryEntrySort){
		$this->categoryEntrySort = $categoryEntrySort;
	}

	function getGenerateTopFlag() {
		if(defined('CMS_PREVIEW_MODE') && CMS_PREVIEW_MODE){
			return true;
		}else{
			return $this->generateTopFlag;
		}
	}
	function getRawGenerateTopFlag() {
		return $this->generateTopFlag;
	}
	function setGenerateTopFlag($generateTopFlag) {
		$this->generateTopFlag = $generateTopFlag;
	}

	function getGenerateMonthFlag() {
		if(defined('CMS_PREVIEW_MODE') && CMS_PREVIEW_MODE){
			return true;
		}else{
			return $this->generateMonthFlag;
		}
	}
	function setGenerateMonthFlag($generateMonthFlag) {
		$this->generateMonthFlag = $generateMonthFlag;
	}

	function getGenerateCategoryFlag() {
		if(defined('CMS_PREVIEW_MODE') && CMS_PREVIEW_MODE){
			return true;
		}else{
			return $this->generateCategoryFlag;
		}
	}
	function setGenerateCategoryFlag($generateCategoryFlag) {
		$this->generateCategoryFlag = $generateCategoryFlag;
	}

	function getGenerateRssFlag() {
		if(defined('CMS_PREVIEW_MODE') && CMS_PREVIEW_MODE){
			return true;
		}else{
			return $this->generateRssFlag;
		}
	}
	function setGenerateRssFlag($generateRssFlag) {
		$this->generateRssFlag = $generateRssFlag;
	}

	function getGenerateEntryFlag() {
		if(defined('CMS_PREVIEW_MODE') && CMS_PREVIEW_MODE){
			return true;
		}else{
			return $this->generateEntryFlag;
		}
	}
	function setGenerateEntryFlag($generateEntryFlag) {
		$this->generateEntryFlag = $generateEntryFlag;
	}

	function getTopTitleFormat() {
		return $this->topTitleFormat;
	}
	function setTopTitleFormat($topTitleFormat) {
		$this->topTitleFormat = $topTitleFormat;
	}

	function getMonthTitleFormat() {
		return $this->monthTitleFormat;
	}
	function setMonthTitleFormat($MonthTitleFormat) {
		$this->monthTitleFormat = $MonthTitleFormat;
	}

	function getCategoryTitleFormat() {
		return $this->categoryTitleFormat;
	}
	function setCategoryTitleFormat($CategoryTitleFormat) {
		$this->categoryTitleFormat = $CategoryTitleFormat;
	}

	function getEntryTitleFormat() {
		return $this->entryTitleFormat;
	}
	function setEntryTitleFormat($EntryTitleFormat) {
		$this->entryTitleFormat = $EntryTitleFormat;
	}

	function getBlogLabelId() {
		return $this->blogLabelId;
	}
	function setBlogLabelId($blogLabelId) {
		$this->blogLabelId = $blogLabelId;
	}

	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function getAuthor() {
		return $this->author;
	}
	function setAuthor($author) {
		$this->author = $author;
	}

	function getDefaultAcceptComment() {
		return $this->defaultAcceptComment;
	}
	function setDefaultAcceptComment($defaultAcceptComment) {
		$this->defaultAcceptComment = $defaultAcceptComment;
	}

	function getDefaultAcceptTrackback() {
		return $this->defaultAcceptTrackback;
	}
	function setDefaultAcceptTrackback($defaultAcceptTrackback) {
		$this->defaultAcceptTrackback = $defaultAcceptTrackback;
	}

	function getBBlockConfig(){
		if(is_null($this->bBlockConfig) || (is_array($this->bBlockConfig) && !count($this->bBlockConfig))){
			foreach($this->getBBlockList() as $tag){
				$this->bBlockConfig[$tag] = 1;
			}
		}
		return $this->bBlockConfig;
	}
	function setBBlockConfig($bBlockConfig){
		$this->bBlockConfig = $bBlockConfig;
	}

	function getFeedTitleFormat() {
		return $this->feedTitleFormat;
	}
	function setFeedTitleFormat($feedTitleFormat) {
		$this->feedTitleFormat = $feedTitleFormat;
	}

	function getTopPageUri() {
		return $this->topPageUri;
	}
	function setTopPageUri($topPageUri) {
		$this->topPageUri = $topPageUri;
	}

	/** 便利なメソッド **/

	const B_BLOCK_CATEGORY = "category";
	const B_BLOCK_ARCHIVE = "archive";
	const B_BLOCK_ARCHIVE_BY_YEAR = "archive_by_year";
	const B_BLOCK_ARCHIVE_EVERY_YEAR = "archive_every_year";
	const B_BLOCK_RECENT_ENTRY_LIST = "recent_entry_list";
	const B_BLOCK_RECENT_COMMENT_LIST = "recent_comment_list";
	const B_BLOCK_RECENT_TRACKBACK_LIST = "recent_trackback_list";
	const B_BLOCK_PAGER = "pager";
	const B_BLOCK_CURRENT_CATEGORY = "current_category";
	const B_BLOCK_CURRENT_ARCHIVE = "current_archive";
	const B_BLOCK_CURRENT_CATEGORY_OR_ARCHIVE = "current_category_or_archive";
	const B_BLOCK_COMMENT_FORM = "comment_form";
	const B_BLOCK_COMMENT_LIST = "comment_list";
	const B_BLOCK_TRACKBACK_LINK = "trackback_link";
	const B_BLOCK_TRACKBACK_LIST = "trackback_list";
	const B_BLOCK_TOP_LINK = "top_link";
	const B_BLOCK_META_FEED_LINK = "meta_feed_link";
	const B_BLOCK_RSS_LINK = "rss_link";

	function getBBlockList(){
		return array(
			self::B_BLOCK_CATEGORY,
			self::B_BLOCK_ARCHIVE,
			self::B_BLOCK_ARCHIVE_BY_YEAR,
			self::B_BLOCK_ARCHIVE_EVERY_YEAR,
			self::B_BLOCK_RECENT_ENTRY_LIST,
			self::B_BLOCK_RECENT_COMMENT_LIST,
			self::B_BLOCK_RECENT_TRACKBACK_LIST,
			self::B_BLOCK_PAGER,
			self::B_BLOCK_CURRENT_CATEGORY,
			self::B_BLOCK_CURRENT_ARCHIVE,
			self::B_BLOCK_CURRENT_CATEGORY_OR_ARCHIVE,
			self::B_BLOCK_COMMENT_FORM,
			self::B_BLOCK_COMMENT_LIST,
			self::B_BLOCK_TRACKBACK_LINK,
			self::B_BLOCK_TRACKBACK_LIST,
			self::B_BLOCK_TOP_LINK,
			self::B_BLOCK_META_FEED_LINK,
			self::B_BLOCK_RSS_LINK
		);
	}
}
