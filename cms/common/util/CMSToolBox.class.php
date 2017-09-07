<?php

class CMSToolBox {

	private $links = array();
	private $htmls = array();

	/**
	 * 廃止予定
	 */
	private $filetree = false;

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
	 * ツールボックスにファイルツリーを追加する（実際はファイルマネージャーへのリンク）
	 * _common.ToolBoxPageで無効になっている
	 * 廃止予定
	 */
	public static function enableFileTree(){
		$instance = &self::getInstance();
		$instance->filetree = true;
	}

	/**
	 * 廃止予定
	 */
	public static function isEnableFileTree(){
		$instance = &self::getInstance();
		return $instance->filetree;
	}

	public static function addPageJumpBox(){
		$result = SOY2ActionFactory::createInstance("Page.PageListAction",array("buildTree"=>true))->run();
		$options = $result->getAttribute("PageTree");

		//ページ詳細では現在表示中のページIDを取得する
		$currentPageId = null;
		$page = SOY2PageController::getRequestPath();
		if($page == "Page.Detail" || $page == "Page.Mobile.Detail" || $page == "Page.Application.Detail" || strpos($page, "Blog") === 0){
			$args = SOY2PageController::getArguments();
			$currentPageId = (is_array($args) && count($args)) ? array_shift($args) : null;
			$_tmp = (isset($args[1])) ? $args[1] : null;
		}

		$html = '<SELECT class="form-control" onchange="location.href=\''.SOY2PageController::createLink("Page.Detail.").'\'+this.value;"><option value="">'.CMSMessageManager::get("SOYCMS_JUMP_TO_WEBPAGE_DETAIL").'</option>';
		foreach($options as $key => $name){
			$selected = (isset($currentPageId) && $key == $currentPageId) ? ' selected class="current-page"' : '' ;
			$html .= '<option value="'.htmlspecialchars($key,ENT_QUOTES,"UTF-8").'"'.$selected.'>'.htmlspecialchars($name,ENT_QUOTES,"UTF-8").'</option>';
		}
		$html.='</SELECT>';
		self::addHtml($html);
	}

}
