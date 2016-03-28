<?php

class CSSEditorPage extends CMSWebPageBase {

	private $filename;
	private $text;
	private $writable;
	private $url;
	
	function doPost(){
		
		if(isset($_POST['op_code'])){
    		if(soy2_check_token() or true){
				//CSSの更新
				$result = $this->run("Page.Preview.CSSUpdateAction");
				$out = '<html><head><script lang="text/javascript">';
				$out .= 'window.parent.document.update_form.soy2_token.value = "'.soy2_get_token().'";';
				if(!$result->success()){
					$out.='alert("'.$this->getMessage("SOYCMS_FILEMANAGER_SAVE_FAILED").'");';
				}
				if(isset($_POST["reload"])){
					$out .= 'window.parent.parent.location.reload();</script></body></html>';
				}
				$out .= '</script></head><body></body></html>';
				echo $out;
	    	}
			exit;
		}else{
			
			$result = $this->run("Page.Preview.CSSEditAction");
			
			$this->url = $result->getAttribute("url");
			
			if($result->success()){
				$this->filename = $result->getAttribute("filename");
				$this->text = $result->getAttribute("contents");
				$this->writable = $result->getAttribute("writable");
			}else{
				echo '<html><body><p>'.$this->getMessage("SOYCMS_FILEMANAGER_CANNOT_EDIT").'</p>' .
						'<p>'.htmlspecialchars($this->url).'</p>'.
						'<input type="button" value="'.$this->getMessage("SOYCMS_FILEMANAGER_CLOSE").'" onclick="window.parent.common_close_layer(window.parent);" />' .
						'</body></html>';
				exit;
			}	
		}
	}

    function CSSEditorPage($arg) {
    	
    	WebPage::WebPage();
    	$this->createAdd("update_form","HTMLForm",array("name" => "update_form"));
    	$this->createAdd("filepath","HTMLInput",array("value"=>$this->filename));
    	$this->createAdd("filePath","HTMLLabel",array("text"=>$this->filename));
		$this->createAdd("contents","HTMLTextArea",array("text"=>$this->text));
		HTMLHead::addScript("soycms_entry_editor_2",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js")
		));
		
		if(!$this->writable){
			DisplayPlugin::hide("writable");
		}
    	
    }
}
?>