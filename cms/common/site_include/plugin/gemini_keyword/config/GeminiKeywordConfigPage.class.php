<?php
class GeminiKeywordConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("domain.cms.DataSets");
		SOY2::import("site_include.plugin.soycms_search_block.util.SearchBlockUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			SearchBlockUtil::saveSecurityConfig($_POST["Security"]);
		
			$logic = SOY2Logic::createInstance("logic.ai.GeminiApiLogic");
			$logic->saveApiKey($_POST["Config"]["gemini_api_key"]);

			$enabledBlogPages = (isset($_POST["Config"]["enabled_blog_page"]) && is_array($_POST["Config"]["enabled_blog_page"])) ? $_POST["Config"]["enabled_blog_page"] : array();
			GeminiKeywordUtil::saveEnabledBlogPages($enabledBlogPages);

			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		parent::__construct();
		self::_buildGeminiConfigArea();
		self::_buildSecurityConfigArea();
	}

	private function _buildGeminiConfigArea(){
		$logic = SOY2Logic::createInstance("logic.ai.GeminiApiLogic");

		$this->addForm("form");

		$this->addInput("gemini_api_key", array(
			"name" => "Config[gemini_api_key]",
			"value" => $logic->getApiKey(),
			"style" => "width:60%"
		));

		$this->addLabel("enabled_blog_pages_checkboxes", array(
			"html" => self::_buildCheckBoxes()
		));

		$siteId = UserInfoUtil::getSite()->getSiteId();

		$this->addLabel("job_path", array(
			"text" => dirname(dirname(__FILE__)) . "/job/extract.php " . $siteId
		));

		$this->addLabel("soy_id", array(
			"text" => GeminiKeywordUtil::FIELD_ID
		));

		$this->addLabel("plugin_id", array(
			"text" => GeminiKeywordPlugin::PLUGIN_ID	
		));

		$this->addLabel("site_id", array(
			"text" => $siteId
		));
	}

	private function _buildSecurityConfigArea(){
		$cfg = SearchBlockUtil::getSecurityConfig();

		$this->addCheckBox("is_strip_tags", array(
			"type" => "checkbox",
			"name" => "Security[is_strip_tags]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリからHTMLタグを削除する",
			"selected" => (isset($cfg["is_strip_tags"]) && (int)$cfg["is_strip_tags"] === SearchBlockUtil::ON)
		));

		$this->addCheckBox("deny_url", array(
			"type" => "checkbox",
			"name" => "Security[deny_url]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリでURLを禁止",
			"selected" => (isset($cfg["deny_url"]) && (int)$cfg["deny_url"] === SearchBlockUtil::ON)
		));

		$this->addCheckBox("deny_path", array(
			"type" => "checkbox",
			"name" => "Security[deny_path]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリでファイルパスを禁止",
			"selected" => (isset($cfg["deny_path"]) && (int)$cfg["deny_path"] === SearchBlockUtil::ON)
		));

		$this->addInput("limit_char", array(
			"type" => "number",
			"name" => "Security[limit_char]",
			"value" => (isset($cfg["limit_char"])) ? (int)$cfg["limit_char"] : 0,
			"style" => "width:60px;"
		));
	}

	/**
	 * @return string
	 */
	private function _buildCheckBoxes(){
		$blogs = self::_blogPagesList();
		if(!count($blogs)) return "";

		$chks = GeminiKeywordUtil::getEnabledBlogPages();
		$html = array();
		
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>記事のキーワードの自動抽出を有効にするブログページ</label>";
		$html[] = "<div class=\"form-inline\">";

		foreach($blogs as $labelId => $blogName){
			$h = array();

			if(is_numeric(array_search($labelId, $chks))){
				$h[] = "<label><input type=\"checkbox\" name=\"Config[enabled_blog_page][]\" value=\"".$labelId."\" checked=\"checked\">".$blogName."</label><br>";
			}else{
				$h[] = "<label><input type=\"checkbox\" name=\"Config[enabled_blog_page][]\" value=\"".$labelId."\">".$blogName."</label><br>";
			}			

			$html[] = implode("", $h);
		}
		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	/**
	 * array(label_id => ブログ名(ラベル名))
	 * @return array
	 */
	private function _blogPagesList(){
		try{
			$blogs = soycms_get_hash_table_dao("blog_page")->get();
		}catch(Exception $e){
			return array();
		}
		if(!count($blogs)) return array();

		$_arr = array();
		foreach($blogs as $blog){
			if(!is_numeric($blog->getBlogLabelId())) continue;
			if(!isset($_arr[(int)$blog->getBlogLabelId()])) $_arr[(int)$blog->getBlogLabelId()] = $blog->getTitle();
		}
		if(!count($_arr)) return array();
		
		$labels = self::_getLabelCaptionsByIds(array_keys($_arr));
		if(!count($labels)) return array();

		foreach($_arr as $labelId => $blogName){
			if(!isset($labels[$labelId])) continue;
			$_arr[$labelId] = $blogName."(".$labels[$labelId].")";
		}
		
		return $_arr;
	}

	/**
	 * @param array
	 * @return array
	 */
	private function _getLabelCaptionsByIds(array $labelIds){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery(
				"SELECT id, caption FROM Label WHERE id IN (".implode(",", $labelIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["id"]] = $v["caption"];
		}
		return $_arr;
	}
	
	function setPluginObj(GeminiKeywordPlugin $pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
