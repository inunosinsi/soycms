<?php

class CMSToolBox {
	
	private $links = array();
	private $htmls = array();
	private $filetree = false;
	
    function CMSToolBox() {}
    
    public static function &getInstance(){
    	static $_instance;
    	
    	if(!$_instance)$_instance = new CMSToolBox();
    	
    	return $_instance;
    }
    
    /**
     * @link 遷移先
     * @text アンカー内部
     */
    public static function addLink($text,$link,$clickToLayer = false,$onclick=""){
    	
    	if($clickToLayer){
    		$onclick = "javascript:return common_click_to_layer(this);";
    	}
    	
    	$instance = &self::getInstance();
    	$instance->links[] = array(
    		"link" => $link,
    		"text" => $text,
    		"onclick" => $onclick    	
    	);
    }
    
    public static function getLinks(){
    	$instance = &self::getInstance();
    	return $instance->links;
    }
    
    /**
     * @html 挿入するHTML
     */
    public static function addHTML($html){
    	$instance = &self::getInstance();
    	$instance->htmls[] = $html;
    }
    
    public static function getHTMLs(){
    	$instance = &self::getInstance();
    	return $instance->htmls;
    }
    
    /**
     * ツールボックスにファイルツリーを追加する
     */
    public static function enableFileTree(){
    	$instance = &self::getInstance();
    	$instance->filetree = true;
    }
    
    public static function isEnableFileTree(){
    	$instance = &self::getInstance();
    	return $instance->filetree;
    }
    
    public static function addPageJumpBox(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array("buildTree"=>true))->run();
		$options = $result->getAttribute("PageTree");
		
		$html = '<SELECT onchange="location.href=\''.SOY2PageController::createLink("Page.Detail.").'\'+this.value;"><option value="">'.CMSMessageManager::get("SOYCMS_JUMP_TO_WEBPAGE_DETAIL").'</option>';
		foreach($options as $key => $name){
			$html .= '<option value="'.htmlspecialchars($key,ENT_QUOTES,"UTF-8").'">'.htmlspecialchars($name,ENT_QUOTES,"UTF-8").'</option>';
		}
		$html.='</SELECT>';
		self::addHtml($html);
    }
    
}
?>