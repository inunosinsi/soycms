<?php

class DetailPage extends CMSWebPageBase{

	var $labelId;

	function doPost(){

		if(soy2_check_token()){
			$res = $this->run("Label.LabelUpdateAction",array(
				"id" => $this->labelId
			));

			if($res->success()){
				$this->addMessage("LABEL_UPDATE_SUCCESS");
			}else{
				$this->addMessage("LABEL_UPDATE_FAILED");
			}

			$this->jump("Label.Detail.".$this->labelId);
		}
	}

	function __construct($args) {
		$this->labelId = (isset($args[0])) ? (int)$args[0] : null;

		parent::__construct();

		$res = $this->run("Label.LabelDetailAction",array(
			"id" => $this->labelId
		));

		//無かった場合
		if(!$res->success()){
			$this->jump("Label");
		}

		self::setupWYSIWYG();

		$label = $res->getAttribute("label");
		self::_buildForm($label);

		//アイコンリスト
		$this->createAdd("image_list","_component.Label.LabelIconListComponent",array(
			"list" => self::getLabelIconList()
		));

		// colorpickerプラグイン
		HTMLHead::addLink("colorpicker",array(
				"rel" => "stylesheet",
				"href" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.css"),
		));
		$this->addLabel("colorpicker", array(
			"src" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.js"),
		));

		$this->addForm("update_form");

		self::_buildUsedLabelInfo($label);
	}

	private function _buildForm(Label $entity){

		$this->addInput("caption", array(
			"value" => $entity->getCaption(),
			"name" => "caption"
		));

		$this->addInput("alias", array(
	    		"value" => $entity->getAlias(),
	    		"name" => "alias"
	    	));

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));
		$this->addInput("icon", array(
			"value" => $entity->getIcon(),
			"name" => "icon",
			"id" => "labelicon"
		));

		$this->addTextArea("description", array(
			"value" => $entity->getDescription(),
			"name" => "description"
		));

		$this->addInput("color", array(
			"value" => sprintf("%06X",$entity->getColor()),
			"name" => "color"
		));

		$this->addInput("background_color", array(
			"value" => sprintf("%06X",$entity->getBackgroundColor()),
			"name" => "backgroundColor"
		));

		$this->addLabel("preview", array(
			"text"=> $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";padding:5px;line-height:1.7;"
		));
	}

	private function _buildUsedLabelInfo(Label $label){
		//ブロックとして使用中
		$blocks = self::_getBlockComponentsByLabelId($label->getId());

		//使用するラベルとして設定されているもの
		$blogs = self::_getBlogDefaultLabelByLabelId($label->getId());

		//カテゴリとして使用されているもの
		$blogCategories = self::_getBlogCategoryLabelByLabelId($label->getId());

		DisplayPlugin::toggle("label_info", (count($blocks) || count($blogs) || count($blogCategories)));

		$this->addLabel("caption_text", array(
			"text" => $label->getCaption()
		));


		DisplayPlugin::toggle("used_block", count($blocks));
		$this->createAdd("used_block_list", "_component.Label.UsedBlockListComponent", array(
			"list" => $blocks
		));

		DisplayPlugin::toggle("used_blog", (count($blogs) || count($blogCategories)));

		DisplayPlugin::toggle("used_blog_label", count($blogs));
		$this->createAdd("used_blog_label_list", "_component.Label.UsedBlogLabelListComponent", array(
			"list" => $blogs
		));

		DisplayPlugin::toggle("used_blog_category", count($blogCategories));
		$this->createAdd("used_blog_category_list", "_component.Label.UsedBlogLabelListComponent", array(
			"list" => $blogCategories
		));
	}

	/**
	 * ラベルに使えるアイコンの一覧を返す
	 */
	private function getLabelIconList(){

		$files = scandir(CMS_LABEL_ICON_DIRECTORY);

		$return = array();
		foreach($files as $file){
			if($file[0] == ".")continue;

			$return[] = (object)array(
				"filename" => $file,
				"url" => CMS_LABEL_ICON_DIRECTORY_URL . $file,
			);
		}

		return $return;
	}

	/**
	 * @param labelId
	 * @return array(pageId => array("soy" => soy:id, "type" => component)
	 */
	private function _getBlockComponentsByLabelId($labelId){
		//一旦すべて取得する
		try{
			$blocks = SOY2DAOFactory::create("cms.BlockDAO")->get();
		}catch(Exception $e){
			$blocks = array();
		}
		if(!count($blocks)) return array();

		$list = array();
		foreach($blocks as $block){
			$cmp = $block->getBlockComponent();
			if(!method_exists($cmp, "getLabelId") && !method_exists($cmp, "getMapping")) continue;
			$cmpName = get_class($cmp);
			switch($cmpName){
				case "MultiLabelBlockComponent":
					if(array_key_exists($labelId, $cmp->getMapping())){
						$list[$block->getPageId()] = array("soy" => $block->getSoyId(), "type" => $cmpName);
					}
					break;
				default:
					if($cmp->getLabelId() == $labelId){
						$list[$block->getPageId()] = array("soy" => $block->getSoyId(), "type" => $cmpName);
					}
			}
		}
		return $list;
	}

	/**
	 * @param labelId
	 * @return array(pageId)
	 */
	private function _getBlogDefaultLabelByLabelId($labelId){
		//一旦全て取得する
		$blogs = self::_getAllBlogPages();
		if(!count($blogs)) return array();

		$list = array();
		foreach($blogs as $pageId => $blog){
			if($blog["blogLabelId"] == $labelId){
				$list[] = $pageId;
			}
		}
		return $list;
	}


	/**
	 * @param labelId
	 * @return array(pageId)
	 */
	private function _getBlogCategoryLabelByLabelId($labelId){
		//一旦全て取得する
		$blogs = self::_getAllBlogPages();
		if(!count($blogs)) return array();

		$list = array();
		foreach($blogs as $pageId => $blog){
			if(!isset($blog["categoryLabelList"]) || !is_array($blog["categoryLabelList"]) || !count($blog["categoryLabelList"])) continue;
			if(is_numeric(array_search($labelId, $blog["categoryLabelList"]))){
				$list[] = $pageId;
			}
		}
		return $list;
	}

	private function _getAllBlogPages(){
		static $blogs;
		if(is_null($blogs)){
			//必要な情報だけ整理
			$blogs = array();
			try{
				$blogPages = SOY2DAOFactory::create("cms.BlogPageDAO")->get();
			}catch(Exception $e){
				$blogPages = array();
			}

			if(count($blogPages)){
				foreach($blogPages as $page){
					$blogs[$page->getId()] = array("blogLabelId" => $page->getBlogLabelId(), "categoryLabelList" => $page->getCategoryLabelList());
				}
			}
		}
		return $blogs;
	}

	private function setupWYSIWYG(){
		//Call Event
		CMSPlugin::callEventFunc("onLabelSetupWYSIWYG");

		$jsVarsAndPaths = array(
				"InsertLinkAddress" => "Entry.Editor.InsertLink",
				"InsertImagePage" => "Entry.Editor.FileUpload",
				"CreateLabelLink" => "Entry.CreateNewLabel",
				"templateAjaxURL" => "EntryTemplate.GetTemplateAjaxPage",
		);

		//Cookieからエディタのタイプを取得
		$editor = isset($_COOKIE["label_text_editor"]) ? $_COOKIE["label_text_editor"] : "plain" ;

		$scriptsArr = array(
				"plain"=> array(
						"./js/editor/PlainTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
				),
				"tinyMCE" => array(
						"./js/tinymce/tinymce.min.js",
						"./js/editor/RichTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
				)
		);
		$jsFiles = isset($scriptsArr[$editor]) ? $scriptsArr[$editor] : $scriptsArr["tinyMCE"];

		//bootstrapを使った管理画面用（JavaScriptはファイル末尾で読み込む）
		self::createAddJavaScript($jsVarsAndPaths, $jsFiles);
	}

	/**
	 * 記事編集に必要なJavaScriptをcreateAddで追加する。soy:id=entry_editor_javascripts
	 */
	private function createAddJavaScript($jsVarsAndPaths, $scriptFiles){
		$script = array();
		$script[] = '<script type="text/javascript">'."\n";
		foreach($jsVarsAndPaths as $var => $path){
			$script[] = 'var '.$var.' = "' . htmlspecialchars(SOY2PageController::createLink($path),ENT_QUOTES,"UTF-8") . '";';
		}
		if(SOYCMSEmojiUtil::isInstalled()){
			$script[] = 'var mceSOYCMSEmojiURL = "'.htmlspecialchars(SOYCMSEmojiUtil::getEmojiInputPageUrl().'#tinymce',ENT_QUOTES,"UTF-8").'";';
		}
		$script[] = '</script>'."\n";

		foreach($scriptFiles as $path){
			$script[] = '<script type="text/javascript" src="'.htmlspecialchars(SOY2PageController::createRelativeLink($path)."?".SOYCMS_BUILD_TIME,ENT_QUOTES,"UTF-8").'"></script>';
		}
		//ラベルのチェック状況を調べる
		$script[] = '<script type="text/javascript">';
		$script[] = "$('input[name=\"label[]\"]').each(function(ele){";
		$script[] = "	toggle_labelmemo(\$(this).val(), \$(this).is(\":checked\"));";
		$script[] = "});";
		$script[] = '</script>'."\n";

		$this->addLabel("entry_editor_javascripts", array(
			"html" => implode("\n", $script),
		));
	}
}
