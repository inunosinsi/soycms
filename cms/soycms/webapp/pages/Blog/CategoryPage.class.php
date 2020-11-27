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
		//記事管理者以外がこのページを開いた時
		if(is_null($arg[0]) || UserInfoUtil::hasSiteAdminRole()){
			$this->jump('Blog');//どっかに飛ばす
		}
		$this->pageId = (int)$arg[0];

		parent::__construct();

		$labels = $this->getLabelLists();
		$this->createAdd("label_lists", "_component.Blog.LabelListsComponent", array(
			"list" => $labels,
			"pageId" => $this->pageId
		));

		$this->addInput("update_display_order", array(
			"type" => "submit",
			"name" => "update_display_order",
			"value" => CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
			"tabindex" => CategoryListComponent::$tabIndex++
		));

		// $this->createAdd("no_label_message","Label._LabelBlankPage",array(
		// 	"visible" => (count($labels)<1)
		// ));

		if(count($labels) < 1) DisplayPlugin::hide("must_exist_label");

		$this->addForm("create_label");
		$this->addModel("create_label_caption", array(
			"placeholder" => $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER"),//ラベル名 または 分類名/ラベル名
		));


		$this->addForm("reNameForm", array(
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
		$this->createAdd("image_list", "_component.Blog.LabelIconListComponent",array(
			"list" => self::_getLabelIconList()
		));

		//表示順更新フォーム
		$this->addForm("update_display_order_form");

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
	private function _getLabelIconList(){

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
