<?php

class CategoryPage extends CMSWebPageBase{

	private $pageId;

	function doPost(){


		if(soy2_check_token() && isset($_POST["caption"]) && strlen($_POST["caption"])){
			$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
			$label = new Label();
			$label->setCaption($_POST["caption"]);
			
			$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
			if(!$logic->checkDuplicateCaption($label->getCaption())){
				$this->addErrorMessage("BLOG_CATEGORY_ADD_FAILED");
				$this->jump("Blog.Category.".$this->pageId);
			}
			
			//並び順補正
			$label->setDisplayOrder(Label::ORDER_MAX);

			$labelDao->begin();
			try{
				$id = $logic->create($label);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_ADD_FAILED");
				$this->jump("Blog.Category.".$this->pageId);
			}
			
			$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
			try{
				$blogPage = $pageDao->getById($this->pageId);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_ADD_FAILED");
				$this->jump("Blog.Category.".$this->pageId);
			}
				
			$categoryLabelList = $blogPage->getCategoryLabelList();
			array_push($categoryLabelList, $id);
			$blogPage->setCategoryLabelList($categoryLabelList);
			
			try{
				$pageDao->updatePageConfig($blogPage);
			}catch(Exception $e){
				$this->addErrorMessage("BLOG_CATEGORY_ADD_FAILED");
				$this->jump("Blog.Category.".$this->pageId);
			}
			
			$labelDao->commit();
			
			$this->addMessage("BLOG_CATEGORY_ADD_SUCCESS");
		}
	}

	function __construct($arg) {
		if(is_null($arg[0])){
			$this->jump('Blog');//どっかに飛ばす
		}
		$this->pageId = (int)$arg[0];

		WebPage::__construct();
		
		$labels = $this->getLabelLists();
		$this->createAdd("label_lists","LabelLists",array(
			"list" => $labels,
			"pageId" => $this->pageId
		));

		$this->createAdd("update_display_order","HTMLInput",array(
			"type" => "submit",
			"name" => "update_display_order",
			"value" => CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
			"tabindex" => LabelList::$tabIndex++
		));

		$this->createAdd("no_label_message","Label._LabelBlankPage",array(
			"visible" => (count($labels)<1)
		));

		if(count($labels)<1){
			DisplayPlugin::hide("must_exist_label");
		}

		$this->createAdd("create_label","HTMLForm");
		$this->addModel("create_label_caption",array(
			"placeholder" => $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER"),//ラベル名 または 分類名/ラベル名
		));


		$this->createAdd("reNameForm","HTMLForm",array(
			"action"=>SOY2PageController::createLink("Label.Rename")
		));
		
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->pageId)
		));
		
		
		HTMLHead::addScript("root",array(
			"script"=>'var reNameLink = "'.SOY2PageController::createLink("Blog.Rename.".$this->pageId).'";' .
					'var reDesciptionLink = "'.SOY2PageController::createLink("Blog.ReDescription.".$this->pageId).'";' .
					'var ChangeLabelIconLink = "'.SOY2PageController::createLink("Blog.ChangeLabelIcon.".$this->pageId).'";'
		));

		//アイコンリスト
		$this->createAdd("image_list","LabelIconList",array(
			"list" => $this->getLabelIconList()
		));

		//表示順更新フォーム
		$this->createAdd("update_display_order_form","HTMLForm");

		//CSS
		HTMLHead::addLink("labelcss",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/label/label.css")
		));
	}
	
	/**
	 *  ラベルオブジェクトのリストのリストを返す
	 * @param Boolean $classified ラベルを分けるかどうか
	 */
	function getLabelLists($classified = true){
		/**
		 * actionクラスを介さず直接ブログに設定されているカテゴリを取得
		 */
		
		//ブログページを取得
		try{
			$blogPage = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($this->pageId);
		}catch(Exception $e){
			return array();
		}
		
		$categoryLabelList = $blogPage->getCategoryLabelList();
		if(!count($categoryLabelList)) return array();
		
		$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
		$labels = array();
		foreach($categoryLabelList as $labelId){
			try{
				$labels[] = $labelDao->getById($labelId);
			}catch(Exception $e){
				continue;
			}
		}
		
		return array("" => $labels);
	}
	
	/**
	 * ラベルに使えるアイコンの一覧を返す
	 */
	function getLabelIconList(){

		$dir = CMS_LABEL_ICON_DIRECTORY;

		$files = scandir($dir);

		$return = array();

		foreach($files as $file){
			if($file[0] == ".")continue;
			if(!preg_match('/jpe?g|gif|png$/i',$file))continue;
			if($file == "default.gif")continue;

			$return[] = (object)array(
				"filename" => $file,
				"url" => CMS_LABEL_ICON_DIRECTORY_URL . $file,
			);
		}


		return $return;
	}
}

class LabelLists extends HTMLList{
	
	private $pageId;

	function populateItem($entity, $key){
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));
		$this->createAdd("list","LabelList",array(
			"list" => $entity,
			"pageId" => $this->pageId
		));

		return ( count($entity) > 0 );
	}
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}

class LabelList extends HTMLList{
	public static $tabIndex = 0;
	private $pageId;

	function populateItem($entity){

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));

		$this->createAdd("label_name","HTMLLabel",array(
			"text"=> $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";margin:5px"
		));
		
		$this->createAdd("remove_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink("Blog.Remove." .$this->pageId . "." .$entity->getId()),
			"visible" => UserInfoUtil::hasEntryPublisherRole(),
		));

		$this->createAdd("description","HTMLLabel",array(
			"text"=> (trim($entity->getDescription())) ? $entity->getDescription() : CMSMessageManager::get("SOYCMS_CLICK_AND_EDIT"),
			"onclick"=>'postDescription('.$entity->getId().',"'.addslashes($entity->getCaption()).'","'.addslashes($entity->getDescription()).'")'
		));

		//記事数
//		$this->createAdd("entry_count","HTMLLabel",array(
//			"text"=> $entity->getEntryCount(),
//		));
	}
	
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}

class LabelIconList extends HTMLList{

	function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:postChangeLabelIcon('".$entity->filename."');"
		));
	}
}
?>