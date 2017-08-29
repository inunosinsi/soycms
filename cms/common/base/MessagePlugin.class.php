<?php

class MessagePlugin extends PluginBase{
	function executePlugin($soyValue){

		$helpMessage = CMSMessageManager::get($soyValue);

		switch($this->tag){
			case "img":
				$this->_attribute["src"] = SOY2PageController::createRelativeLink("./image/icon/help.gif");
				$this->_attribute["class"] = "help_icon";
				$this->_attribute["onMouseOver"] = "this.style.cursor='pointer'";
				$this->_attribute["onMouseOut"] = "this.style.cursor='auto'";
				if($soyValue){
					$this->_attribute["onclick"] = "common_show_message_popup(this,'".$helpMessage."')";
				}
				break;
			case "span":
				$helpMessage = SOY2HTML::ToText($helpMessage);
				$this->setInnerHTML('<i class="fa fa-question-circle fa-fw" data-toggle="tooltip" data-placement="right" title="'.htmlspecialchars($helpMessage, ENT_QUOTES, SOY2HTML::ENCODING).'"></i>');
			default:
				;
		}
	}

	function getStartTag(){
		switch($this->tag){
			case "span":
				if(isset($this->_attribute["class"]) && strlen($this->_attribute["class"])){
					$this->_attribute["class"] = "help ".trim($this->_attribute["class"]);
				}else{
					$this->_attribute["class"] = "help";
				}
				return "<span class=\"".htmlspecialchars($this->_attribute["class"],ENT_QUOTES,SOY2HTML::ENCODING)."\">";
				break;
			case "img":
			default:
				return parent::getStartTag();
		}
	}

	function getEndTag(){
		switch($this->tag){
			case "span":
				return "</span>";
				break;
			case "img":
			default:
				return parent::getEndTag();
		}
	}
}


?>