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
		$labelId = @$args[0];
		$this->labelId = $labelId;

		parent::__construct();

		$res = $this->run("Label.LabelDetailAction",array(
			"id" => $labelId
		));

		//無かった場合
		if(!$res->success()){
			$this->jump("Label");
		}

		self::setupWYSIWYG();

		$label = $res->getAttribute("label");
		$this->buildForm($label);

		//アイコンリスト
		$this->createAdd("image_list","LabelIconList",array(
			"list" => $this->getLabelIconList()
		));

		// colorpickerプラグイン
		HTMLHead::addLink("colorpicker",array(
				"rel" => "stylesheet",
				"href" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.css"),
		));
		$this->addLabel("colorpicker", array(
			"src" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.js"),
		));

		$this->createAdd("update_form","HTMLForm");
	}

	function buildForm($entity){

		$this->createAdd("caption","HTMLInput",array(
			"value" => $entity->getCaption(),
			"name" => "caption"
		));

		$this->createAdd("alias","HTMLInput",array(
	    		"value" => $entity->getAlias(),
	    		"name" => "alias"
	    	));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$entity->getId().");"
		));
		$this->createAdd("icon","HTMLInput",array(
			"value" => $entity->getIcon(),
			"name" => "icon",
			"id" => "labelicon"
		));

		$this->createAdd("description","HTMLTextArea",array(
			"value" => $entity->getDescription(),
			"name" => "description"
		));

		$this->createAdd("color","HTMLInput",array(
			"value" => sprintf("%06X",$entity->getColor()),
			"name" => "color"
		));

		$this->createAdd("background_color","HTMLInput",array(
			"value" => sprintf("%06X",$entity->getBackgroundColor()),
			"name" => "backgroundColor"
		));

		$this->createAdd("preview","HTMLLabel",array(
			"text"=> $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";padding:5px;line-height:1.7;"
		));
	}

	/**
	 * ラベルに使えるアイコンの一覧を返す
	 */
	function getLabelIconList(){

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
		$editor = isset($_COOKIE["label_text_editor"]) ? $_COOKIE["label_text_editor"] : "tinyMCE" ;

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


class LabelIconList extends HTMLList{

	function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:postChangeLabelIcon(this,'".$entity->filename."');"
		));
	}

}
