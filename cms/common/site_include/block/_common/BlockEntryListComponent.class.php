<?php

class BlockEntryListComponent extends HTMLList{

	private $isStickUrl;
	private $articlePageUrl;
	private $categoryPageUrl;
	private $blogPageId;
	private $blockId;
	private $editable = true;
	private $labelId;
	private $dsn = false;

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

	public function setBlockId($blockId) {
		$this->blockId = $blockId;
	}

	public function setEditable($flag){
		$this->editable = $flag;
	}

	public function setLabelId($labelId){
		$this->labelId = $labelId;
	}

	public function getDsn() {
		return $this->dsn;
	}
	public function setDsn($dsn) {
		$this->dsn = $dsn;
	}

	public function getStartTag(){

		if(defined("CMS_PREVIEW_MODE") && $this->editable){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->getId().'["entry_id"]; ?>','<?php echo strip_tags($'.$this->getId().'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}

	public function getEndTag(){

		if(defined("CMS_PREVIEW_MODE") && $this->editable){
			return parent::getEndTag().'<?php echo "<button type=\"button\" class=\"cms_hidden_entry_id\" blocklabelid=\"'.$this->labelId.'\" style=\"display:none;\">ここに記事を追加する</button>"; ?>';
		}else{
			return parent::getEndTag();
		}
	}

	/**
	 * 実行前後にDSNの書き換えを実行
	 */
	public function execute(){

		if($this->dsn) $old = SOY2DAOConfig::Dsn($this->dsn);

		parent::execute();

		if($this->dsn) SOY2DAOConfig::Dsn($old);
	}

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = $this->articlePageUrl.($entity->getAlias());

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
		//cms:idで呼び出せるように　2009.04.14
		$this->addLink("entry_link", array(
			"link" => $entryUrl,
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

		$this->addLink("more_link_no_anchor", array(
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

		$this->addLabel("entry_url", array(
			"text" => $entryUrl,
			"soy2prefix" => "cms",
		));

		//カテゴリ
		$this->createAdd("category_list","CategoryListComponent",array(
			"list" => ($this->isStickUrl && $id > 0) ? self::_labelLogic()->getLabelsByBlogPageIdAndEntryId($this->blogPageId, $id) : array(),
			"categoryUrl" => $this->categoryPageUrl,
			"entryCount" => array(),
			"soy2prefix" => "cms"
		));


		CMSPlugin::callEventFunc('onEntryOutput',array(
			"entryId" => $id,
			"SOY2HTMLObject" => $this,
			"entry" => $entity,
			"blockId" => $this->blockId
		));
	}

	private function _labelLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		return $logic;
	}
}
