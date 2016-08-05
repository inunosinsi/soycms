<?php

class PreviewPage extends WebPage{

	var $id;
	var $dao;
	var $form;

	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$this->form = new SOYInquiry_Form();

    	parent::prepare();
	}

    function __construct($args) {

    	if(count($args)<1)CMSApplication::jump("Form");
    	$this->id = $args[0];

    	//レイヤーモードで
    	CMSApplication::setMode("layer");

    	WebPage::WebPage();

    	try{
    		$this->form = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		CMSApplication::jump("Form");
    	}

    	$config = $this->form->getConfigObject();

		//template directory setting
		$designConfig = $config->getDesign();
		if(isset($designConfig["theme"]) && strlen($designConfig["theme"])  > 0 && is_dir(SOY2::RootDir() . "template/". $designConfig["theme"])){
			$templateDir = SOY2::RootDir() . "template/". $designConfig["theme"] . "/";
		}else{
			$templateDir = SOY2::RootDir() . "template/default/";
		}

    	//ランダムな値を作成
		$random_hash = md5(mt_rand());

    	$columnDAO = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    	$columns = $columnDAO->getOrderedColumnsByFormId($this->id);

    	ob_start();
    	include_once($templateDir . "form.php");
    	$html = ob_get_contents();
    	ob_end_clean();

    	$this->createAdd("preview","HTMLLabel",array(
    		"html" => $html
    	));

    }
}
?>