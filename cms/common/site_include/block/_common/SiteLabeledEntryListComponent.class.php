<?php

class SiteLabeledEntryListComponent extends HTMLList{

	var $isStickUrl;
	var $articlePageUrl;
	var $categoryPageUrl;
	var $blogPageId;
	private $dsn = false;
	private $isCallEventFunc = true;	//カスタムフィールドの拡張ポイントを実行するか？

	public function setIsStickUrl($flag){
		$this->isStickUrl = $flag;
	}

	public function setArticlePageUrl($articlePageUrl){
		$this->articlePageUrl = $articlePageUrl;
	}

	public function setCategoryPageUrl($categoryPageUrl){
		$this->categoryPageUrl = $categoryPageUrl;
	}

	public function setBlogPageId($id){
		$this->blogPageId = $id;
	}
	public function setIsCallEventFunc($isCallEventFunc){
		$this->isCallEventFunc = $isCallEventFunc;
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

		if($this->dsn)SOY2DAOConfig::Dsn($old);
	}


	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = rtrim($this->articlePageUrl, "/") . "/" . rawurlencode($entity->getAlias());
		$entryShortUrl = rtrim($this->articlePageUrl, "/") . "/" . $entity->getId();

		if($this->isStickUrl){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $id,
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
		$contentLen = (is_string($entity->getContent())) ? strlen($entity->getContent()) : 0;
		$this->addModel("has_content", array(
			"visible" => ($contentLen > 0),
			"soy2prefix"=>"cms"
		));
		$this->addModel("no_content", array(
			"visible" => ($contentLen === 0),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entity->getMore(),
			"soy2prefix"=>"cms"
		));
		$moreLen = (is_string($entity->getMore())) ? strlen($entity->getMore()) : 0;
		$this->addModel("has_more", array(
			"visible" => ($moreLen > 0),
			"soy2prefix"=>"cms"
		));
		$this->addModel("no_more", array(
			"visible" => ($moreLen === 0),
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
		$this->addLink("entry_link", array(
			"link" => $entryUrl,
			"soy2prefix"=>"cms"
		));

		$this->addLink("entry_short_link", array(
			"link" => $entryShortUrl,
			"soy2prefix"=>"cms"
		));

		//リンクの付かないタイトル 1.2.6～
		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entity->getTitle(),
			"soy2prefix"=>"cms"
		));

		//1.2.7～
		$this->addLink("more_link", array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		$this->addLink("more_short_link", array(
			"soy2prefix"=>"cms",
			"link" => $entryShortUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		$this->addLink("more_link_no_anchor", array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl,
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		$this->addLink("more_short_link_no_anchor", array(
			"soy2prefix"=>"cms",
			"link" => $entryShortUrl,
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

		$this->addLabel("entry_url", array(
			"text"=>$entryUrl,
			"soy2prefix"=>"cms",
		));

		$labels = ($this->isStickUrl && $id > 0) ? self::_labelLogic()->getLabelsByBlogPageIdAndEntryId($this->blogPageId, $id) : array();
		$entity->setLabels($labels);
		unset($labels);

		//カテゴリ
		$this->createAdd("category_list","CategoryListComponent",array(
			"list" => $entity->getLabels(),
			"categoryUrl" => $this->categoryPageUrl,
			"entryCount" => array(),
			"soy2prefix" => "cms"
		));

		//ブロックの高速化　カスタムフィールド用の拡張ポイントを実行するか？
		if(!is_bool($this->isCallEventFunc)) $this->isCallEventFunc = true;
		if($this->isCallEventFunc) CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$id,"SOY2HTMLObject"=>$this,"entry"=>$entity));
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
