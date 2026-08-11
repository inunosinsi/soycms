<?php

class IndexPage extends WebPage{

	private $pageId;
	private $page;

	function doPost(){

		if(soy2_check_token()){
			$start = '/* write custom script here */';
			$end = '/* --------------------- end */';
			$content = $start . "\n" . $_POST["content"] . "\n\t\t" . $end;
			$this->setPageClassFileScript($content);

			SOY2PageController::jump("Site.Pages.Script." . $this->pageId . "?updated");
		}

	}

	function __construct($args){
		$this->pageId = (isset($args[0])) ? (int)$args[0] : null;
		$this->page = $this->getPageObj();

		parent::__construct();

		$this->addForm("form");

		$this->addLabel("page_name", array(
			"text" => $this->page->getName() . "ページのカスタムスクリプト"
		));

		$this->addLabel("page_name_raw", array(
			"text" => $this->page->getName()
		));

		$this->addLink("page_link_no_text", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->pageId),
		));

		$this->addLink("page_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->pageId),
			"text" =>$this->page->getName() . "ページのページ設定"
		));

		$this->addLabel("file_path", array(
			"text" => $this->getPageClassFile()
		));

		$this->addTextArea("script", array(
			"name" => "content",
			"value" => $this->getPageClassFileScript()
		));

	}

	function getPageObj(){
		$pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		try{
			$page = $pageDao->getById($this->pageId);
		}catch(Exception $e){
			$page = new SOYShop_Page();
		}

		return $page;
	}

	/**
	 * ページクラスファイルを取得する
	 */
	function getPageClassFile(){
		return SOYSHOP_SITE_DIRECTORY . ".page/" . $this->page->getCustomClassFileName();
	}

	function getPageClassFileScript(){

		$file = file_get_contents($this->getPageClassFile());
		preg_match('/\/\* write custom script here \*\/(.*)\/\* --------------------- end \*\//s', $file, $match);

		return (isset($match[1])) ? trim($match[1]) : "";
	}

	function setPageClassFileScript($content){

		$filePath = $this->getPageClassFile();
		$file = file_get_contents($filePath);
		$content = preg_replace('/\/\* write custom script here \*\/(.*)\/\* --------------------- end \*\//s', $content, $file);

		file_put_contents($filePath, $content);
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カスタムスクリプト", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->pageId => "ページ設定"));
	}
}
