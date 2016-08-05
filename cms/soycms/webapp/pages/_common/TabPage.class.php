<?php

class TabPage extends CMSHTMLPageBase {
	
	/**
	 * 有効なタブとURLのパターン
	 */
	private $activeTabRules = array(
		'Index' => 'dashboard',
		'^Page' =>	'page',
		'^Entry'=> 'entry',
		'^Label'=> 'label',
		'^Blog' => 'page',
		'^Plugin' => 'plugin',
		'^Template' => 'page',
		'^Module' => 'page',
		
		//以下、シンプルモードのタブ
		'^Simple$' => 'dashboard',
		'^Simple.Index' => 'dashboard',
		'^Simple.Entry'=> 'entry'
	);
	
	private $activeTab;
	
    function __construct() {
    	HTMLPage::HTMLPage();
    	    	
    	//リクエストされたパスからActiveなパスを取得
    	$requestPath = SOY2PageController::getRequestPath();
    	
    	foreach($this->activeTabRules as $rule => $tab){
    		if(preg_match("/".$rule."/",$requestPath)){
    			$this->activeTab = $tab;
    			break;
    		}
    	}
    }
    
    function execute(){
    	
    	parent::execute();
 		
 		/* タブの状態を設定 */
    	$this->createAdd("dashboard","HTMLTab",array(
    		"class" => $this->getMenuStatus("dashboard")
    	));
    	
    	$this->createAdd("page","HTMLTab",array(
    		"class" => $this->getMenuStatus("page")
    	));
    	
    	$this->createAdd("entry","HTMLTab",array(
    		"class" => $this->getMenuStatus("entry")
    	));
    	
    	$this->createAdd("label","HTMLTab",array(
    		"class" => $this->getMenuStatus("label")
    	));
    	
    	$this->createAdd("config","HTMLTab",array(
    		"class" => $this->getMenuStatus("config")
    	));   	
    	
    	$this->createAdd("plugin","HTMLTab",array(
    		"class" => $this->getMenuStatus("plugin")
    	));
    	
    	/* シンプルモードのタブの状態を設定 */
    	$this->createAdd("simple_dashboard","HTMLTab",array(
    		"class" => $this->getMenuStatus("dashboard")
    	));
    	
    	$this->createAdd("simple_entry","HTMLTab",array(
    		"class" => $this->getMenuStatus("entry")
    	));
    	
    	$this->createAdd("simple_preview","HTMLTab",array(
    		"class" => $this->getMenuStatus("preview")
    	));
    	
    	$this->createAdd("simple_blog","HTMLTab",array(
    		"class" => $this->getMenuStatus("blog")
    	));	
    	
    	
    	/* タブの切り替えを行う */
    	$notSimple = UserInfoUtil::hasSiteAdminRole();
    	
    	$this->createAdd("simple_mode","HTMLModel",array(
    		"visible" => !$notSimple
    	));	
    	
    	$this->createAdd("not_simple_mode","HTMLModel",array(
    		"visible" => $notSimple
    	));
    	
    }
    
    /**
     * メニューの状態を設定
     */
    function getMenuStatus($tabName){
    	
    	if($tabName == $this->activeTab){
    		return "menu_active span-3";
    	}else{
    		return "menu_inactive span-3";
    	}
    }
}

class HTMLTab extends SOY2HTML{
	
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	
	function execute(){
		//do nothing
	}
	
	function getObject(){
		return "";
	}
	
}
?>