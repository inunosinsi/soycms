<?php

class CMSEntryEditorPageBase extends CMSUpdatePageBase{

	/**
	 *
	 * @param int $entryId
	 * @param array(int) $labelIds
	 * @param boolean $placeJsInHead JavaScriptをヘッダーに置くか（true, デフォルト）, createAddで追加するか（false）
	 */
	public function setupWYSIWYG($entryId = null, $labelIds = null, $placeJsInHead = true){

		if(isset($labelIds)){
			if(!is_array($labelIds)){
				$labelIds = array($labelIds);
			}
		}else{
			$labelIds = array();
		}

		//Call Event
		CMSPlugin::callEventFunc("onSetupWYSIWYG",array(
			"id" => $entryId,
			"labelIds" => $labelIds,
		));


		$jsVarsAndPaths = array(
				"entry_css_path" => "Entry.CSS.".$entryId,
				"InsertLinkAddress" => "Entry.Editor.InsertLink",
				"InsertImagePage" => "Entry.Editor.FileUpload",
				"CreateLabelLink" => "Entry.CreateNewLabel",
				"templateAjaxURL" => "EntryTemplate.GetTemplateAjaxPage",
		);

		//Cookieからエディタのタイプを取得
		$editor = isset($_COOKIE["entry_text_editor"]) ? $_COOKIE["entry_text_editor"] : "tinyMCE" ;

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

		//JavaScriptの位置によってメソッドを変える
		if($placeJsInHead){
			//従来の管理画面用（JavaScriptは<head>で読み込む）
			$this->addScriptToHead($jsVarsAndPaths, $jsFiles);
		}else{
			//bootstrapを使った管理画面用（JavaScriptはファイル末尾で読み込む）
			$this->createAddJavaScript($jsVarsAndPaths, $jsFiles);
		}


		if(UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish_info");
		}else{
			DisplayPlugin::hide("publish");
			if(!$this->id)DisplayPlugin::hide("publish_info");
		}
	}

	/**
	 * 記事編集に必要なJavaScriptを<head>に追加する
	 */
	private function addScriptToHead($jsVarsAndPaths, $scriptFiles){
		$script = "";
		foreach($jsVarsAndPaths as $var => $path){
			$script .= 'var '.$var.' = "' . SOY2PageController::createLink($path) . '";'."\n";
		}
		if(SOYCMSEmojiUtil::isInstalled()){
			$script .= 'var mceSOYCMSEmojiURL = "'.SOYCMSEmojiUtil::getEmojiInputPageUrl().'#tinymce";';
		}

		HTMLHead::addScript("soycms_editor",array(
				"script" => $script
		));

		foreach($scriptFiles as $key => $path){
			HTMLHead::addScript("soycms_entry_editor_".$key,array(
				"type" => "text/JavaScript",
				"src" => SOY2PageController::createRelativeLink($path)."?".SOYCMS_BUILD_TIME
			));
		}

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

	/**
	 * テキストエリアのclassを設定
	 */
	public static function getEditorClass(){

		$editor = isset($_COOKIE["entry_text_editor"]) ? $_COOKIE["entry_text_editor"] : "" ;

		switch($editor){
			case "plain":
				$class = "form-control";//bootstrap
				break;
			case "tinyMCE":
			default:
				$class = "mceEditor";
				break;

		}

		return $class;
	}

}
