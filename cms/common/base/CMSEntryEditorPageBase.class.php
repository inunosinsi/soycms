<?php

class CMSEntryEditorPageBase extends CMSUpdatePageBase{

	function setupWYSIWYG($entryId = null, $labelIds = null){

    	if(isset($labelIds)){
    		if(!is_array($labelIds)){
    			$labelIds = array($labelIds);
    		}
    	}else{
    		$labelIds = array();
    	}

    	//Call Event
    	CMSPlugin::callEventFunc("onSetupWYSIWTG",array(
    		"id" => $entryId,
    		"labelIds" => $labelIds,
    	));

    	$editor = @$_COOKIE["entry_text_editor"];

		if(is_null($editor) || strlen($editor) == 0){
			$editor = "tinyMCE";
		}

		$script = "";

		$script .= "var entry_css_path = '" . SOY2PageController::createLink("Entry.CSS.".$this->id) . "';";
		$script .= 'var InsertLinkAddress = "'.SOY2PageController::createLink("Entry.Editor.InsertLink").'";' .
					'var InsertImagePage = "'.SOY2PageController::createLink("Entry.Editor.FileUpload").'";'.
					'var CreateLabelLink = "'.SOY2PageController::createLink("Entry.CreateNewLabel").'";';
		$script .= 'var templateAjaxURL = "'.SOY2PageController::createLink("EntryTemplate.GetTemplateAjaxPage").'";';

		if(SOYCMSEmojiUtil::isInstalled()){
			$script .= 'var mceSOYCMSEmojiURL = "'.SOYCMSEmojiUtil::getEmojiInputPageUrl().'#tinymce";';
		}

		HTMLHead::addScript("soycms_editor",array(
			"script" => $script
		));

		$scriptsArr =
			array(
				"plain"=>
					array(
						"./js/editor/PlainTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
					),
				"tinyMCE" =>
					array(
						"./js/tinymce/tinymce.min.js",
						"./js/editor/RichTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
					)
			);

		$scripts = @$scriptsArr[$editor];
		if(is_null($scripts)){
			$scripts = $scriptsArr["tinyMCE"];
		}

		foreach($scripts as $key => $link){
			HTMLHead::addScript("soycms_entry_editor_".$key,array(
				"type" => "text/JavaScript",
				"src" => SOY2PageController::createRelativeLink($link)."?".SOYCMS_BUILD_TIME
			));
		}

		HTMLHead::addLink("editor.css",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		if(UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish_info");
		}else{
			DisplayPlugin::hide("publish");
			if(!$this->id)DisplayPlugin::hide("publish_info");
		}


    }


    /**
     * テキストエリアのclassを設定
     */
    public static function getEditorClass(){

    	$editor = @$_COOKIE["entry_text_editor"];

		if(is_null($editor) || strlen($editor) == 0){
			$class = "mceEditor";
		}else{
			switch($editor){
				case "tinyMCE":
				default:
					$class = "mceEditor";
					break;

			}
		}

    	return $class;
    }

}
?>