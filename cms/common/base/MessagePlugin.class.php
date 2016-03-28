<?php

class MessagePlugin extends PluginBase{
	function executePlugin($soyValue){
		switch($this->tag){
			case "img":
				$this->_attribute["src"] = SOY2PageController::createRelativeLink("./image/icon/help.gif");
				$this->_attribute["class"] = "help_icon";
				$this->_attribute["onMouseOver"] = "this.style.cursor='pointer'";
				$this->_attribute["onMouseOut"] = "this.style.cursor='auto'";
				break;
			default:
				;			
		}
		if($soyValue){
			$this->_attribute["onclick"] = "common_show_message_popup(this,'".CMSMessageManager::get($soyValue)."')";
		}	
	}	
}


?>