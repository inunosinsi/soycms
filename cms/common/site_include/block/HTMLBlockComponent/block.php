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
	public function getFormPage(){
		return SOY2HTMLFactory::createInstance("HTMLBlockComponent_FormPage",array(
			"html" => $this->html
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	public function getViewPage($page){

		return SOY2HTMLFactory::createInstance("HTMLBlockComponent_ViewPage",array(
			"html" => $this->html
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	public function getInfoPage(){
		return "";
	}

	/**
	 * @return string コンポーネント名
	 */
	public function getComponentName(){
		return CMSMessageManager::get("SOYCMS_HTML_BLOCK");
	}

	public function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_HTML_BLOCK_DESCRIPTION");
	}

	public function getHtml() {
		return $this->html;
	}
	public function setHtml($html) {
		$this->html = $html;
	}

	public function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	public function setDisplayCountFrom($displayCountFrom) {
		$cnt = (strlen($displayCountFrom) && is_numeric($displayCountFrom)) ? (int)$displayCountFrom : null;
		$this->displayCountFrom = $cnt;
	}

	public function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	public function setDisplayCountTo($displayCountTo) {
		$cnt = (strlen($displayCountTo) && is_numeric($displayCountTo)) ? (int)$displayCountTo : null;
		$this->displayCountTo = $cnt;
	}
}


class HTMLBlockComponent_FormPage extends HTMLPage{

	private $html;


	public function execute(){

		$this->addTextArea("html", array(
			"name" => "object[html]",
			"text" => $this->html
		));

		$this->addForm("main_form");
	}

	public function setHtml($html){
		$this->html = $html;
	}

	public function getHtml(){
		return $this->html;
	}

	public function getTemplateFilePath(){
		//ext-modeでbootstrap対応画面作成中
		if(defined("EXT_MODE_BOOTSTRAP") && file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html";
		}


		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)) . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html";
		}

	}
}


class HTMLBlockComponent_ViewPage extends SOYBodyComponentBase{

	private $html;
	protected $_soy2_prefix = "block";

	public function setHtml($html){
		$this->html = $html;
	}

	public function getHtml(){
		return $this->html;
	}

	public function execute(){

		$this->setSoy2Prefix("block");

		$this->setInnerHTML('<?php echo $'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]; ?>');

	}

	public function getObject(){
		return $this->getHtml();
	}

}
