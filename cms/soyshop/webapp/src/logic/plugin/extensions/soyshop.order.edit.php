<?php
class SOYShopOrderEditBase implements SOY2PluginAction{

	//HTMLを自由に記述出来るスペース
	function html(){
		return "";
	}
}

class SOYShopOrderEditBaseDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "html";
	private $_html;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "html":
			default:
				$html = $action->html();
				if(strlen($html)){
					if(strlen($this->_html)){
						$this->_html .= "\n";
					}
					$this->_html .= $html;
				}
				break;
		}
	}

	function setMode($mode) {
		$this->mode = $mode;
	}

	function getHTML(){
		return $this->_html;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.edit",      "SOYShopOrderEditBaseDeletageAction");
