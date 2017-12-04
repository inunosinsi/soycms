<?php

class IndexPage extends CMSWebPageBase {

	var $currentPageId;
	var $page;
	var $args;
	var $mode;	//For blog page
	var $siteConfig;
	var $showAllEntry;

    function __construct($arg) {

    	$this->showAllEntry = (!isset($_GET["show_all"]) or $_GET["show_all"] == 1);

    	$result = $this->run("Page.Preview.PreviewAction",array(
			"arg"     => $arg,
			"showAll" => $this->showAllEntry
		));

    	$this->currentPageId = $result->getAttribute("page_id");

    	if($result->success()){
    		$this->siteConfig = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();

    		$this->page = $result->getAttribute("pageObj");
    		$this->args = $result->getAttribute("args");

    		$this->mode = @$this->page->mode;	//Blogページだけだけど。
    		$html = $result->getAttribute("page");
    		$html = $this->insertScript($html);

    		//header送信
    		header("Content-Type: text/html; charset=" . $this->siteConfig->getCharsetText());

    		echo $html;
    	}else{
    		$this->addMessage("PAGE_PREVIEW_FAILED");
    		$this->jump("Page.Detail.".$arg[0]);
    	}

    	exit;
    }

    function insertScript($html){

    	//jquery関連の読み込みがあった場合、読み込まないようにする
    	preg_match_all('/<(script).*?src="(.*)\/jquery(.*)"><\/(script)>/i', $html, $match);
    	foreach($match[0] as $tag){
    		$html = str_replace($tag, "", $html);
    	}

		$head = "";

		if(preg_match('/<base[^>]+href\s*=\s*["\'](.*)["\'][^>]*>?/i',$html,$tmp)){
    		$html = preg_replace('/<base[^>]+>/','',$html);
    		$head = '<base href="'. $this->buildPageUrl($tmp[1]).'" />'."\n";
		}else{
			$head = '<base href="'. $this->getPageUrl().'" />'."\n";
		}

		//Insert prototype.js before jQuery
		$head .= '<script src="'.SOY2PageController::createRelativeLink("./js/jquery.js",true).'"></script>'."\n";
		$head .= '<script src="'.SOY2PageController::createRelativeLink("./js/jquery-ui.min.js",true).'"></script>'."\n";

		$script = '<link rel="stylesheet" href="'.SOY2PageController::createRelativeLink("./css/layer/layer.css",true).'"/>' ."\n".
    			  '<script src="'.SOY2PageController::createRelativeLink("./js/common.js",true).'"></script>' ."\n".
    			  '<script type="text/javascript"> ' .
    			  		'var EntryEditPage = "'.SOY2PageController::createLink("Page.Preview.Entry",true).'"; ' .
    			  		'var layerCSS = "'.SOY2PageController::createRelativeLink("./css/layer/layer.css",true).'";' .
    			  		'var cssEditAddress ="'.SOY2PageController::createLink("Page.Preview.CSSEditor",true).'";'.
    			  		'var templateEditAddress ="'.$this->getTemplateEditUrl().'";'.
    			  		'var siteId ="'.UserInfoUtil::getSite()->getSiteId().'";'.
				  '</script>' ."\n";
		if(UserInfoUtil::hasSiteAdminRole()){
    		$script .= '<script type="text/javascript">'.file_get_contents(dirname(__FILE__).'/PreviewPage.js').'</script>';
		}else{
			$script .= '<script type="text/javascript">'.file_get_contents(dirname(__FILE__).'/PreviewPageSimple.js').'</script>';
		}

    	//Setting of view
    	$style = array();
    	$style[] = '<style type="text/css">';
    	$style[] = '';
    	$style[] = '#soy_cms_operation div{ margin-left: 10px; margin-bottom:10px;}';
    	$style[] = '#soy_cms_operation input[type=submit]{ border:outset 1px #D4D0C8;margin-top:2px; text-align:center;}';
    	$style[] = '#soy_cms_operation select{ border:inset 2px #D4D0C8; width:200px;}';
    	$style[] = '</style>';

    	$script .= implode("\n",$style);

    	//Toggle edit button
    	$insertHTML = array();
    	$insertHTML[] = '<div id="soy_cms_operation" style="display:none;text-align:left;">';

    	$insertHTML[] = '<br>';
    	$insertHTML[] = '<div>';
    	$insertHTML[] = '<input id="soy_cms_operation_toggle_edit_entry_button" type="checkbox">';
    	$insertHTML[] = '<label for="soy_cms_operation_toggle_edit_entry_button">'.CMSMessageManager::get("SOYCMS_PREVIEW_SHOW_EDIT_BUTTON").'</label>';
    	$insertHTML[] = '</div>';


    	$insertHTML[] = '<div>';
    	$insertHTML[] = '<input id="soy_cms_operation_toggle_show_entry_button" type="checkbox" '.(($this->showAllEntry) ? "checked=\"checked\"" : "").'>';
    	$insertHTML[] = '<label for="soy_cms_operation_toggle_show_entry_button">'.CMSMessageManager::get("SOYCMS_PREVIEW_SHOW_ALL_ENTRIES").'</label>';
    	$insertHTML[] = '</div>';

    	//For stronger role than normal user
    	if(UserInfoUtil::hasSiteAdminRole()){
//	    	//CSS edit button
//	    	$insertHTML[] = '<div id="soy_cms_operation_edit_css_form_wrapper" style="display:none;">';
//    		$insertHTML[] = '<form id="soy_cms_operation_edit_css_form" method="POST">';
//	    	$insertHTML[] = '<select id="soy_cms_operation_edit_css_select" name="cssName" style="border:normal;"></select><br>';
//	    	$insertHTML[] = '<input type="submit" value="'.CMSMessageManager::get("SOYCMS_PREVIEW_EDIT_CSS").'">';
//	    	$insertHTML[] = '</form>';
//	    	$insertHTML[] = '</div>';

//	    	//Template edit button
//	    	$insertHTML[] = '<div id="soy_cms_operation_edit_template_form_wrapper">';
//			$insertHTML[] = '<form id="soy_cms_operation_edit_template_form" method="GET">';
//	    	$insertHTML[] = '<input type="submit" value="'.CMSMessageManager::get("SOYCMS_PREVIEW_EDIT_WEBPAGE_TEMPLATE").'">';
//	    	$insertHTML[] = '</form>';
//	    	$insertHTML[] = '</div>';
    	}

    	//Move to other webpage button
    	$insertHTML[] = '<div>';
    	$insertHTML[] = '<form action="'.SOY2PageController::createLink("Page.Preview",true).'" id="soy_cms_operation_move_page" method="GET">';
    	$insertHTML[] = '<select id="soy_cms_operation_move_page_select" name="id" style="width:200px;border:normal;">';
		$insertHTML[] = $this->getPageTreeSelectorHTML($this->currentPageId);
		$insertHTML[] = '</select><br>';
    	$insertHTML[] = '<input type="submit" value="'.CMSMessageManager::get("SOYCMS_PREVIEW_MOVE_TO_ANOTHER_WEBPAGE").'">';
    	$insertHTML[] = '</form>';
    	$insertHTML[] = '</div>';

    	//記事管理者用のログアウトボタン
    	if(!UserInfoUtil::hasSiteAdminRole()){
	    	$insertHTML[] = '<div style="margin-bottom:0;">';
	    	$insertHTML[] = '<form action="'.SOY2PageController::createLink("Login.Logout",true).'" method="GET">';
	    	$insertHTML[] = '<input type="submit" value="ログアウト">';
	    	$insertHTML[] = '</form>';
	    	$insertHTML[] = '</div>';
    	}

    	$insertHTML[] = '</div>';//soy_cms_operation

    	$insertHTML = implode("\n",$insertHTML);

    	//Change character set
    	$script = $this->siteConfig->convertToSiteCharset($script);
    	$insertHTML = $this->siteConfig->convertToSiteCharset($insertHTML);

    	//Insert HTML: <base> and prototype.js
    	if(stripos($html,'<head>')!==false){
    		$html = preg_replace('/<head>/i','<head>'.$head,$html);
    	}else{
	    	$head = '<head>'.$head.'</head>';
	    	if(stripos($html,'<body>')!==false){
	    		$html = preg_replace('/<body>/i',$head.'<body>',$html);
	    	}else if(stripos($html,'<html>')!==false){
	    		$html = preg_replace('/<html>/i','<html>'.$head,$html);
	    	}else{
	    		$html= '<html>'.$head .'<body>'.$html.'</body>'.'</html>';
	    	}
    	}

    	//Insert HTML: JavaScript
		if(stripos($html,'<script>')!==false){
			$html = preg_replace('/<script>/i',$script. '<script>',$html);
		}elseif(stripos($html,'</head>')!==false){
    		$html = preg_replace('/<\/head>/i',$script. '</head>',$html);
    	}else if(stripos($html,'</body>')!==false){
    		$html = preg_replace('/<\/body>/i',$script.'</body>',$html);
    	}else if(stripos($html,'</html>')!==false){
    		$html = preg_replace('/<\/html>/i',$script.'</html>',$html);
    	}else{
    		$html= $html.$script;
    	}

    	//Insert HTML: Preview iFrame
    	if(stripos($html,'</body>')!==false){
    		$html = preg_replace('/<\/body>/i',$insertHTML.'</body>',$html);
    	}else if(stripos($html,'</html>')!==false){
    		$html = preg_replace('/<\/html>/i',$insertHTML.'</html>',$html);
    	}else{
    		$html.= $insertHTML;
    	}

    	return $html;

    }

    function getPageTreeSelectorHTML($current){
		$result = $this->run("Page.PageListAction",array("buildTree"=>true));

		$pageWithBlock = array();

		$blocks = SOY2DAOFactory::create("cms.BlockDAO")->get();
		foreach($blocks as $block){
			$pageWithBlock[$block->getPageId()] = true;
		}

		$pages = $result->getAttribute("PageArray");

		$blogDao = SOY2DAOFactory::create("cms.BlogPageDAO");
		$options = "";
		foreach($result->getAttribute("PageTree") as $key => $value){

			//記事管理者はブロックのあるページのみを表示する
			if(!UserInfoUtil::hasSiteAdminRole() && !isset($pageWithBlock[$key])){
				continue;
			}

			//ブログページでトップページが非表示なら選択肢から除外する
			if($pages[$key]->isBlog()){
				$blog = $blogDao->cast($pages[$key]);
				if(!$blog->getRawGenerateTopFlag()){
					continue;
				}
			}

			$option = SOY2HTMLElement::createElement("option");
			$option->setAttribute("value",$key);
			$option->appendChild(SOY2HTMLElement::createTextElement($value));
			if($current == $key){
				$option->setAttribute("selected","selected");
			}

			$options .= $option->toHTML();
		}

		return $options;

	}

	function getTemplateEditUrl(){
		return SOY2PageController::createLink("Page.Preview.Template",true) . '/' . $this->currentPageId . (($this->mode) ? '/' . $this->mode : '');
	}

	/**
	 * URL for display webpage
	 */
	function getPageUrl(){

		$url = UserInfoUtil::getSitePublishURL();
		$pageUrl = $this->page->page->getUri();

		$url .= $pageUrl;

		//Add "/" to string end.
		if($this->page instanceof CMSBlogPage OR $this->page instanceof CMSMobilePage ){
			$arguments = implode("/",$this->page->arguments);
			if(strlen($pageUrl) >0 && strlen($arguments) >0) $url .= "/" . $arguments;
		}

		return $url;
	}

	/**
	 * Analize Base URL
	 */
	function buildPageUrl($path){
		$url = $this->getPageUrl();
		$urls = parse_url($url);

		if(isset($urls["port"]) && strlen($urls["port"])>0)$urls["host"] .= ":" . $urls["port"];

		$currentScript = explode("/",$urls["path"]);

		//If the head of the string...
		if($currentScript[0] == "")array_shift($currentScript);

		//Absolute path
		if(preg_match("/^https?:/",$path)){
			return $path;
		}

		//Absolute path 2
		if(preg_match("/^\//",$path)){
			return strtolower(trim(array_shift(split("/", $urls["scheme"])))) .
			 "://".$urls["host"] . $path;
		}

		//Recover omission of "./""
		if(preg_match("/^[^\.]/",$path)){
			$path = "./".$path;
		}

		$paths = explode("/",$path);
		$pathStack = array();

		foreach($paths as $path){

			if($path == ".."){
				array_pop($currentScript);
				array_pop($currentScript);
				continue;
			}

			if($path == "."){
				array_pop($currentScript);
				continue;
			}

			array_push($pathStack,$path);

		}

		$url = implode("/",array_merge($currentScript,$pathStack));

		$protocol = split("/", $urls["scheme"]);
		return strtolower(trim(array_shift($protocol))) .
			 "://".$urls["host"] ."/" .$url;
	}
}
