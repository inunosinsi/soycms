<?php
class GeminiAbstractConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}
	
	function doPost(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("logic.ai.GeminiApiLogic");
			$logic->saveApiKey($_POST["Config"]["gemini_api_key"]);
			GeminiAbstractUtil::saveCount((int)$_POST["Config"]["gemini_count_chars"]);

			$enabledBlogPages = (isset($_POST["Config"]["enabled_blog_page"]) && is_array($_POST["Config"]["enabled_blog_page"])) ? $_POST["Config"]["enabled_blog_page"] : array();
			GeminiAbstractUtil::saveEnabledBlogPages($enabledBlogPages);

			GeminiAbstractUtil::savePrefix($_POST["Config"]["gemini_prefix"]);
			GeminiAbstractUtil::savePostfix($_POST["Config"]["gemini_postfix"]);

			$on = (isset($_POST["Config"]["gemini_abstract_update"]) && $_POST["Config"]["gemini_abstract_update"]);
			GeminiAbstractUtil::saveIsAbstractUpdate($on);

			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		parent::__construct();
		self::_buildGeminiConfigArea();
	}

	private function _buildGeminiConfigArea(){
		$logic = SOY2Logic::createInstance("logic.ai.GeminiApiLogic");

		$this->addForm("form");

		$this->addInput("gemini_api_key", array(
			"name" => "Config[gemini_api_key]",
			"value" => $logic->getApiKey(),
			"style" => "width:60%"
		));

		$this->addInput("gemini_count_chars", array(
			"name" => "Config[gemini_count_chars]",
			"value" => GeminiAbstractUtil::getCount(),
			"style" => "width:100px;"
		));

		$this->addInput("gemini_prefix", array(
			"name" => "Config[gemini_prefix]",
			"value" => GeminiAbstractUtil::getPrefix()
		));

		$this->addInput("gemini_postfix", array(
			"name" => "Config[gemini_postfix]",
			"value" => GeminiAbstractUtil::getPostfix()
		));

		$this->addCheckBox("gemini_abstract_update", array(
			"name" => "Config[gemini_abstract_update]",
			"value" => 1,
			"selected" => GeminiAbstractUtil::isAbstractUpdate()
		));

		$this->addLabel("enabled_blog_pages_checkboxes", array(
			"html" => self::_buildCheckBoxes()
		));

		$this->addLabel("job_path", array(
			"text" => dirname(dirname(__FILE__)) . "/job/generate.php " . UserInfoUtil::getSite()->getSiteId()
		));

		$this->addLabel("soy_id", array(
			"text" => GeminiAbstractUtil::FIELD_ID
		));
	}

	/**
	 * @return string
	 */
	private function _buildCheckBoxes(){
		$blogs = self::_blogPagesList();
		if(!count($blogs)) return "";

		$chks = GeminiAbstractUtil::getEnabledBlogPages();
		
		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>自動生成を有効にするブログページ</label>";
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
	
	function setPluginObj(GeminiAbstractPlugin $pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
