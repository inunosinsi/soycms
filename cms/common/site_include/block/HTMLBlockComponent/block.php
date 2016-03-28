<?php
/**
 * HTML自由記述用のブロックコンポーネント
 */
class HTMLBlockComponent implements BlockComponent{
	
	private $html;
	
	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){
		return SOY2HTMLFactory::createInstance("HTMLBlockComponent_FormPage",array(
			"html" => $this->html
		));		
	}
	
	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	function getViewPage($page){
		
		return SOY2HTMLFactory::createInstance("HTMLBlockComponent_ViewPage",array(
			"html" => $this->html
		));
		
	}
	
	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){
		return "";	
	}
	
	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_HTML_BLOCK");
	}
	
	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_HTML_BLOCK_DESCRIPTION");
	}

	function getHtml() {
		return $this->html;
	}
	function setHtml($html) {
		$this->html = $html;
	}
}


class HTMLBlockComponent_FormPage extends HTMLPage{
	
	private $html;
	
	function HTMLBlockComponent_FormPage(){
		HTMLPage::HTMLPage();
		
	}
	
	function execute(){
				
		$this->createAdd("html","HTMLTextArea",array(
			"name" => "object[html]",
			"text" => $this->html
		));
		
		$this->createAdd("main_form","HTMLForm");
	}
	
	function setHtml($html){
		$this->html = $html;
	}
	
	function getHtml(){
		return $this->html;
	}
	
	function getTemplateFilePath(){
		
		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "HTMLBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "HTMLBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "HTMLBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";			
		}
		
	}	
}


class HTMLBlockComponent_ViewPage extends SOYBodyComponentBase{
	
	private $html;
	protected $_soy2_prefix = "block";
	
	function setHtml($html){
		$this->html = $html;
	}
	
	function getHtml(){
		return $this->html;
	}
	
	function execute(){
		
		$this->setSoy2Prefix("block");
		
		$this->setInnerHTML('<?php echo $'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]; ?>');
		
	}	
	
	function getObject(){
		return $this->getHtml();
	}
		
}

?>
