<?php

class MultiEntryListComponent extends HTMLList{

	private $url = array();
	private $blogId = array();
	private $blogTitle = array();
	private $blogUrl = array();
	private $blogCategoryUrl = array();

	private $dsn;

	public function setUrl($url){
		$this->url = $url;
	}

	public function setBlogId($blogId){
		$this->blogId = $blogId;
	}

	public function setBlogTitle($blogTitle){
		$this->blogTitle = $blogTitle;
	}

	public function setBlogUrl($blogUrl){
		$this->blogUrl = $blogUrl;
	}

	public function setBlogCategoryUrl($blogCategoryUrl){
		$this->blogCategoryUrl = $blogCategoryUrl;
	}

	public function getStartTag(){

		return parent::getStartTag();
	}

	/**
	 * 実行前後にDSNの書き換えを実行
	 */
	public function execute(){

		if($this->dsn)$old = SOY2DAOConfig::Dsn($this->dsn);

		parent::execute();

		if($this->dsn) SOY2DAOConfig::Dsn($old);
	}

	protected function populateItem($entity){
		//entry title
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$url = (isset($this->url[$id])) ? $this->url[$id] : "" ;

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = ( strlen($url) > 0 ) ? $url.rawurlencode($entity->getAlias()) : "" ;

		if(strlen($entryUrl) > 0){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		//blog title
		$blogId = (isset($this->blogId[$id])) ? (int)$this->blogId[$id] : 0;
		$blogUrl = (isset($this->blogUrl[$id])) ? $this->blogUrl[$id] : "";
		$blogCategoryUrl = (isset($this->blogCategoryUrl[$id])) ? $this->blogCategoryUrl[$id] : "";
		$blogTitle = (isset($this->blogTitle[$id])) ? $this->blogTitle[$id] : "";
		$hBlogTitle = htmlspecialchars($blogTitle, ENT_QUOTES, "UTF-8");

		if(strlen($blogUrl) > 0){
			$hBlogTitle = "<a href=\"".htmlspecialchars($blogUrl, ENT_QUOTES, "UTF-8")."\">".$hBlogTitle."</a>";
		}

		$this->createAdd("entry_id","CMSLabel",array(
			"text" => $id,
			"soy2prefix" => "cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"html" => $hTitle,
			"soy2prefix" => "cms"
		));
		$this->createAdd("content","CMSLabel",array(
			"html" => $entity->getContent(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html" => $entity->getMore(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("create_date","DateLabel",array(
			"text" => $entity->getCdate(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("create_time","DateLabel",array(
			"text" => $entity->getCdate(),
			"soy2prefix" => "cms",
			"defaultFormat"=>"H:i"
		));

		//entry_link追加
		$this->addLink("entry_link", array(
			"link" => $entryUrl,
			"soy2prefix" => "cms"
		));

		//リンクの付かないタイトル 1.2.6～
		$this->createAdd("title_plain","CMSLabel",array(
			"text" =>  $entity->getTitle(),
			"soy2prefix" => "cms"
		));

		//1.2.7～
		$this->addLink("more_link", array(
			"soy2prefix" => "cms",
			"link" => $entryUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		$this->addLink("more_link_no_anchor", array(
			"soy2prefix" => "cms",
			"link" => $entryUrl,
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		//Blog Title link
		$this->createAdd("blog_title", "CMSLabel", array(
			"html" => $hBlogTitle,
			"soy2prefix" => "cms"

		));

		//Blog Title plain
		$this->createAdd("blog_title_plain", "CMSLabel" , array(
			"text" => $blogTitle,
			"soy2prefix" => "cms"
		));

		//Blog link
		$this->addLink("blog_link", array(
			"link" => $blogUrl,
			"soy2prefix" => "cms"
		));

		//1.7.5~
		$this->createAdd("update_date","DateLabel",array(
			"text" => $entity->getUdate(),
			"soy2prefix" => "cms",
		));

		$this->createAdd("update_time","DateLabel",array(
			"text" => $entity->getUdate(),
			"soy2prefix" => "cms",
			"defaultFormat"=>"H:i"
		));

		$this->addLabel("entry_url", array(
			"text" => $entryUrl,
			"soy2prefix" => "cms",
		));

		//カテゴリ
		$this->createAdd("category_list","CategoryListComponent",array(
			"list" => (is_numeric($blogId) && $blogId > 0 && strlen($blogCategoryUrl)) ? self::_labelLogic()->getLabelsByBlogPageIdAndEntryId($blogId, $id) : array(),
			"categoryUrl" => $blogCategoryUrl,
			"entryCount" => array(),
			"soy2prefix" => "cms"
		));

		CMSPlugin::callEventFunc('onEntryOutput', array("entryId" => $id, "SOY2HTMLObject" => $this, "entry" => $entity));
	}

	private function _labelLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		return $logic;
	}


	public function getDsn() {
		return $this->dsn;
	}
	public function setDsn($dsn) {
		$this->dsn = $dsn;
	}
}
