<?php
SOY2::import('site_include.CMSPage');
class CMSMobilePage extends CMSPage{

	var $virtualPageId;
	var $virtualTree = null;
	var $pageUrl = "";
	var $pager = 0;
	var $pagerMax = 0;
	var $imageUrl = "";

	function __construct($args){
		$id = $args[0];
		$this->arguments = $args[1];
		$this->siteConfig = $args[2];
		$this->virtualPageId = (isset($this->arguments[0])) ? $this->arguments[0] : 0;
		$this->pager = (count($this->arguments)>1 && is_numeric($this->arguments[count($this->arguments)-1])) ? $this->arguments[count($this->arguments)-1] : null;
		$pageDao = SOY2DAOFactory::create("cms.MobilePageDAO");
		$this->page = $pageDao->getById($id);
		$this->id = $id;

		if(is_numeric($this->virtualPageId)){
			$this->virtualTree = $this->page->getVirtualTreeById($this->virtualPageId);

		}else{
			if(count($this->arguments)>1){
				if(is_numeric($this->arguments[count($this->arguments)-1])){
					$this->virtualPageId = implode("/",array_slice($this->arguments,0,count($this->arguments)-1));
				}else{
					$this->virtualPageId = implode("/",$this->arguments);
				}
			}

			$this->virtualTree = $this->page->getVirtualTreeByAlias($this->virtualPageId);
			if(!$this->virtualTree)throw new Exception("Invalid Virtual Page Id");
			$this->virtualPageId = $this->virtualTree->getId();
		}

		if(!$this->virtualTree)throw new Exception("Invalid Virtual Page Id");

		$siteRootUrl = CMSPageController::createRelativeLink(".");
		if(strlen($siteRootUrl) ==0 OR $siteRootUrl[strlen($siteRootUrl)-1] != "/") $siteRootUrl .= "/";

		$this->pageUrl = $siteRootUrl . $this->page->getUri();
		if( strlen($this->pageUrl) == 0 OR $this->pageUrl[strlen($this->pageUrl)-1] != "/" )$this->pageUrl .= "/";

		if(defined("_SITE_ROOT_")){
			if(realpath(dirname($_SERVER["SCRIPT_FILENAME"])) == realpath(_SITE_ROOT_)){
				$this->imageUrl = $siteRootUrl. "im.php";
			}else{
				$this->imageUrl = $siteRootUrl. basename(_SITE_ROOT_)  ."/im.php";
			}
		}else{
			$this->imageUrl = $siteRootUrl. "im.php";
		}

		//ページフォーマットの取得
		$pageFormat = $this->page->getPageTitleFormat();


		if(strlen($pageFormat) == 0){
			//空っぽだったらデフォルト追加
			$pageFormat = '%PAGE%';
		}
		$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
		$pageFormat = preg_replace('/%PAGE%/',$this->page->getTitle(),$pageFormat);
		$pageFormat = preg_replace('/%MINIPAGE_TITLE%/',$this->virtualTree->getTitle(),$pageFormat);
		$this->title = $pageFormat;
		parent::__construct();
	}

	function main(){

		//メインコンポーネント出力
		$this->createAdd("main","CMSMobilePage_EntryComponent",array(
			"list" => $this->getEntries()
		));

		//現在のページのタイトル
		$this->createAdd("current_page_title","HTMLLabel",array(
			"text" => $this->virtualTree->getTitle(),
			"soy2prefix" => "m_block"
		));
		//現在のページのリンク
		$this->createAdd("current_page_link","HTMLLink",array(
			"link" => $this->pageUrl . ($this->virtualTree->getAlias() ? $this->virtualTree->getAlias() : $this->virtualTree->getId()),
			"soy2prefix" => "m_block"
		));

		//ページャー出力
		//m_block:id="next_page_link"
		$this->createAdd("next_page_link","HTMLLink",array(
			"link" => $this->pageUrl . $this->virtualTree->getAlias() ."/". ($this->pager+1),
			"visible" => ($this->pager+1 < $this->pagerMax),
			"soy2prefix" => "m_block"
		));
		//m_block:id="prev_page_link"
		$this->createAdd("prev_page_link","HTMLLink",array(
			"link" => $this->pageUrl . $this->virtualTree->getAlias() . "/" . ($this->pager-1),
			"visible" => (($this->pager-1) >= 0),
			"soy2prefix" => "m_block"
		));
		//m_block:id="current_pager"
		$this->createAdd("current_pager","HTMLLabel",array(
			"text" => $this->pager+1,
			"soy2prefix" => "m_block"
		));
		//m_block:id="max_pager"
		$this->createAdd("max_pager","HTMLLabel",array(
			"text" => $this->pagerMax,
			"soy2prefix" => "m_block"
		));

		//親ページ
		//m_block:id="parent_page_link"
		$parentNode = $this->page->getVirtualTreeById($this->virtualTree->getParent());
		if($parentNode && $parentNode->getAlias()){
			$parentUrl = $this->pageUrl . $parentNode->getAlias();
		}else{
			if($this->virtualTree->getParent() >0){
				$parentUrl = $this->pageUrl . $this->virtualTree->getParent();
			}else{
				$parentUrl = $this->pageUrl;
			}
		}

		$this->createAdd("parent_page_link","HTMLLink",array(
			"link" => $parentUrl,
			"visible" => ($this->virtualTree->getParent() != $this->virtualPageId),
			"soy2prefix" => "m_block"
		));
		//m_block:id="parent_page_title"
		$parent = $this->page->getVirtualTreeById($this->virtualTree->getParent());
		$this->createAdd("parent_page_title","HTMLLabel",array(
			"text" => $parent->getTitle(),
			"soy2prefix" => "m_block"
		));

		//兄弟ページ
		$siblings = ($this->virtualTree->getParent() != $this->virtualPageId) ? $parent->getChild() : array();
		$prev_siblings = array();
		$next_siblings = array();
		$flag = false;
		foreach($siblings as $sibling){
			if($sibling == $this->virtualTree->getId()){
				$flag = true;
				continue;
			}
			if($flag){
				$next_siblings[] = $sibling;
			}else{
				$prev_siblings[] = $sibling;
			}
		}
		//m_block:id="prev_sibling_page_list"	//前のページ
		$this->createAdd("prev_sibling_page_list","CMSMobilePage_Pager",array(
			"list" => $prev_siblings,
			"page" => $this->page,
			"pageUrl" => $this->pageUrl
		));
		//m_block:id="next_sibling_page_list" //次のページ
		$this->createAdd("next_sibling_page_list","CMSMobilePage_Pager",array(
			"list" => $next_siblings,
			"page" => $this->page,
			"pageUrl" => $this->pageUrl
		));
		//m_block:id="has_siblings"	//兄弟ページがあった時のみ表示
		$this->createAdd("has_siblings","HTMLModel",array(
			"visible" => (count($siblings)>0),
			"soy2prefix" => "m_block"
		));
		//m_block:id="no_siblings"	//兄弟ページがなかった時のみ表示
		$this->createAdd("no_siblings","HTMLModel",array(
			"visible" => (count($siblings)<1),
			"soy2prefix" => "m_block"
		));



		//子ページ
		$children = $this->virtualTree->getChild();
		foreach($children as $key => $treeid){
			$treePage = $this->page->getVirtualTreeById($treeid);
			if(!$treePage)unset($children[$key]);
		}

		$this->createAdd("child_page_list","CMSMobilePage_Pager",array(
			"list" => $children,
			"page" => $this->page,
			"pageUrl" => $this->pageUrl
		));

		//m_block:id="has_child"	//子ページがあった時のみ表示
		$this->createAdd("has_child","HTMLModel",array(
			"visible" => (count($children)>0),
			"soy2prefix" => "m_block"
		));
		//m_block:id="no_child"	//子ページがなかった時
		$this->createAdd("no_child","HTMLModel",array(
			"visible" => (count($children)<1),
			"soy2prefix" => "m_block"
		));


		//トップページ
		//m_block:id="root_page_link"
		$this->createAdd("root_page_link","HTMLLink",array(
			"link" => $this->pageUrl,
			"soy2prefix" => "m_block"
		));
		//m_block:id="root_page_title"
		$this->createAdd("root_page_title","HTMLLabel",array(
			"text" => $this->page->getVirtualTreeById(0)->getTitle(),
			"soy2prefix" => "m_block"
		));


		//メッセージ
		$this->addMessageProperty("parent_page_title",'<?php echo $'.$this->_soy2_pageParam.'["parent_page_title"]; ?>');
		$this->addMessageProperty("parent_page_link",'<?php echo $'.$this->_soy2_pageParam.'["parent_page_link_attribute"]["href"]; ?>');
		$this->addMessageProperty("current_page_title",'<?php echo $'.$this->_soy2_pageParam.'["current_page_title"]; ?>');
		$this->addMessageProperty("current_page_link",'<?php echo $'.$this->_soy2_pageParam.'["current_page_link_attribute"]["href"]; ?>');

		parent::main();

		$this->setTitle($this->title);
	}

	/**
	 * エントリーのリストを返す
	 * @return array(Entry)
	 */
	function getEntries(){

		$limit = (!is_null($this->virtualTree->getSize())) ? $this->virtualTree->getSize() : null;
		$offset = (!is_null($this->virtualTree->getSize())) ? $this->virtualTree->getSize() * $this->pager : null;

		$result = array();

		//ラベル出力の場合
		if($this->virtualTree->getType() == VirtualTreePage::TYPE_LABEL){

			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

			if(!is_null($this->virtualTree->getSize())){
				$logic->setLimit($limit);
				$logic->setOffset($offset);
			}

			if(defined("CMS_PREVIEW_ALL")){
				$result = $logic->getByLabelId($this->virtualTree->getLabel());
			}else{
				$result = $logic->getOpenEntryByLabelId($this->virtualTree->getLabel());
			}

			if((!is_null($this->virtualTree->getSize()))){
				$this->pagerMax = max(1,ceil($logic->totalCount / $this->virtualTree->getSize()));
			}else{
				$this->pagerMax = 1;
			}

		//エントリー出力の場合
		}else{
			$dao = SOY2DAOFactory::create("cms.EntryDAO");

			$entryList = $this->virtualTree->getEntries();
			if(!$entryList)$entryList = array();

			$counter = 0;
			foreach($entryList as $entryId){
				try{
					$entry = $dao->getById($entryId);
					if($entry->isActive() == Entry::ENTRY_ACTIVE || defined("CMS_PREVIEW_ALL")){

						if($counter >= $offset && $counter < ($offset+$limit)){
							$result[] = $entry;
						}
						$counter++;
					}
				}catch(Exception $e){
					//
				}
			}

			if((!is_null($this->virtualTree->getSize()))){
				$this->pagerMax = max(1,ceil($counter / $this->virtualTree->getSize()));
			}else{
				$this->pagerMax = 1;
			}
		}

		return $result;
	}

	/**
	 * 最終的に表示するHTMLがここに設定される
	 */
	function beforeConvert($html){

		if(defined("SOYCMS_SKIP_MOBILE_RESIZE") && SOYCMS_SKIP_MOBILE_RESIZE){
			return $html;
		}


		$regex = '/(<img[^>]*\s)src\s*=\s*(["\'])([^"\']*)(["\'])([^>]*\/?>)/i';
		$html = preg_replace_callback($regex,array($this,'imageUrlReplace'),$html);
		return $html;
	}

	/**
	 * imgタグをim.phpを呼び出す形に変換する
	 */
	function imageUrlReplace($array){

		$im_addr = $this->imageUrl;

		//TODO 携帯からのアクセス時にwidthとheightを書き換える？

		if(preg_match('/width\s*=\s*["\']([0-9]+)["\']/i',$array[0],$tmp)){
			$array[3] .= "&width=".$tmp[1];
		}

		if(preg_match('/height\s*=\s*["\']([0-9]+)["\']/i',$array[0],$tmp)){
			$array[3] .= "&height=".$tmp[1];
		}

		//TODO $array[3]の相対パスを$this->pageUrlで絶対パスにする

		$isExternal = false;

		if(strpos($array[3],"http") === 0 && strpos($array[3],$_SERVER["HTTP_HOST"]) === false){
			$return = $array[0];	//そのまま
		}else{
			$return = $array[1]."src=".$array[2].$im_addr.'?src='.htmlspecialchars($array[3],ENT_QUOTES).$array[4].$array[5];
		}

		return $return;

	}

}

class CMSMobilePage_EntryComponent extends HTMLList{

	protected $_soy2_prefix = "m_block";

	function getStartTag(){
		if(defined("CMS_PREVIEW_MODE")){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->getId().'["entry_id"]; ?>','<?php echo strip_tags($'.$this->getId().'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}

	function populateItem($entity){
		$title = $entity->getTitle();

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $entity->getId(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"text"=> $title,
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
			"soy2prefix"=>"cms"
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entity->getId(),"SOY2HTMLObject"=>$this));
	}
}

class CMSMobilePage_Pager extends HTMLList{

	protected $_soy2_prefix = "m_block";
	protected $page;
	protected $pageUrl;
	protected $count;
	protected $counter=0;

	function setPage($page){
		$this->page = $page;
	}

	function setPageUrl($url){
		$this->pageUrl = $url;
	}

	function execute(){

		if($this->getAttribute("cms:count")){
			$this->count = $this->getAttribute("cms:count");
		}

		parent::execute();
	}

	function populateItem($entity){

		$res = true;
		$treePage = $this->page->getVirtualTreeById($entity);
		if(!$treePage){
			$res = false;
			$treePage = new VirtualTreePage();
		}

		$flag = true;
		if(!is_null($this->count)){
			$flag = ($this->counter < $this->count) ? true : false ;
		}

		$node = $this->page->getVirtualTreeById($entity);
		if($node && $node->getAlias()){
			$url = $this->pageUrl . $node->getAlias();
		}else{
			$url = $this->pageUrl . $entity;
		}

		$this->createAdd("page_link","HTMLLink",array(
			"link" => $url,
			"soy2prefix" => "cms",
			"visible" => $flag
		));

		$this->createAdd("page_title","HTMLLabel",array(
			"text" => $treePage->getTitle(),
			"soy2prefix" => "cms"
		));

		if(!$res)return false;

		$this->counter++;
	}
}
