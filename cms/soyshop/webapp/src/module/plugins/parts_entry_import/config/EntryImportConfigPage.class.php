<?php

class EntryImportConfigPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.parts_entry_import.util.EntryImportUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["confirm"]) || isset($_POST["confirm_x"])){
				SOYShop_DataSets::put("parts.entry.import", $_POST["site"]);
				$this->config->redirect("updated");
			}


			if(isset($_POST["complete"]) || isset($_POST["complete_x"])){
				//var_dump($_POST);exit;
				if(is_numeric($_POST["site"]["count"])){
					SOYShop_DataSets::put("parts.entry.import", $_POST["site"]);
					$this->config->redirect("updated");
				}else{
					$this->config->redirect("error");
				}
			}
		}

	}

	function execute(){

		$config = EntryImportUtil::getConfig();

		parent::__construct();

		DisplayPlugin::toggle("example", isset($config["blogId"]));
		
		DisplayPlugin::toggle("error", isset($_GET["error"]));

		$old = SOYAppUtil::switchAdminDsn();

		//SOY CMS サイト一覧
		$this->addForm("form");
		$this->addLabel("site", array(
			"html" => self::getSiteForm(self::getSiteList(), $config["siteId"])
		));

		/** ここから、記事インポートの詳細設定を記述する **/


		//サイトIDを設定した後に表示する
		$this->addModel("config", array(
			"visible" => isset($config["siteId"])
		));

		//SOY CMSサイトDBに切り替える
		if(isset($config["siteId"])){
			$site = EntryImportUtil::getSite($config["siteId"]);
			$dsn = $site->getDataSourceName();
			SOY2DAOConfig::Dsn($dsn);
			$blogs = self::getBlogList();
		}else{
			$blogs = array();
		}

		/* 詳細設定画面 */
		$this->addForm("config_form");

		$this->addInput("site_id", array(
			"name" => "site[siteId]",
			"value" => (isset($config["siteId"])) ? $config["siteId"] : ""
		));

		$blogId = (isset($config["blogId"])) ? $config["blogId"] : null;
		$this->addLabel("page", array(
			"html" => self::getPageForm($blogs, $blogId)
		));

		$this->addInput("entry_count", array(
			"name" => "site[count]",
			"value" => (isset($config["count"])) ? $config["count"] : 0,
			"size" => 3,
			"style" => "text-align:right;ime-mode:disabled;"
		));

		//元に戻す
		SOYAppUtil::resetAdminDsn($old);


		//タグリスト
		if(isset($config["blogId"])){
			SOY2::import("module.plugins.parts_entry_import.util.TagList");
			$list = TagList::getTagList();
//			$customfields = self::getCustomfieldConfig($config["siteId"]);
		}else{
			$list = array();
		}

		SOY2::import("module.plugins.parts_entry_import.component.TagListComponent");
		$this->createAdd("tag_list", "TagListComponent", array(
			"list" => $list
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}

	private function getSiteList(){
		try{
			return SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_CMS);
		}catch(Exception $e){
			return array();
		}
	}

	private function getBlogList(){
		SOY2::import("util.CMSUtil");
		try{
			return SOY2DAOFactory::create("cms.BlogPageDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * サイト一覧 ラジオ HTML
	 * @param array $sites Site
	 * @param array $siteConfig このプラグインの設定 array(["siteId"],["blogId"],["count"])
	 * @return string $html
	 */
	function getSiteForm($sites, $siteId){

		$html = array();
		foreach($sites as $site){

			//サイト選択のラジオ
			if(isset($siteId) && $site->getSiteId() == $siteId){
				$html[] = "<input type=\"radio\" name=\"site[siteId]\" value=\"" . $site->getSiteId() . "\" id=\"" . $site->getId() . "\" checked=\"checked\" /><label for=\"" . $site->getId() . "\">" . $site->getSiteName() . "(ID:" . $site->getSiteId() . ")</label>";
			}else{
				$html[] = "<input type=\"radio\" name=\"site[siteId]\" value=\"" . $site->getSiteId() . "\" id=\"" . $site->getId() . "\" /><label for=\"" . $site->getId() . "\">" . $site->getSiteName() . "(ID:" . $site->getSiteId() . ")</label>";
			}

		}

		return implode("<br />\n", $html);
	}

	/**
	 * ブログ一覧 ラジオ HTML
	 * @param array $blogs array(BlogPage)
	 * @param array $siteConfig このプラグインの設定 array(["siteId"],["blogId"],["count"])
	 * @return string $html
	 */
	private function getPageForm($blogs, $blogId){

		$html = array();
		foreach($blogs as $blog){

			//ブログの選択ラジオ
			if(isset($blogId) && $blog->getId() == $blogId){
				$html[] = "<input type=\"radio\" name=\"site[blogId]\" value=\"" . $blog->getId() . "\" id=\"" . $blog->getUri() . "\" checked=\"checked\" /><label for=\"" . $blog->getUri() . "\">" . $blog->getTitle() . "(ID:" . $blog->getUri() . ")</label>";
			}else{
				$html[] = "<input type=\"radio\" name=\"site[blogId]\" value=\"" . $blog->getId() . "\" id=\"" . $blog->getUri() . "\" /><label for=\"" . $blog->getUri() . "\">" . $blog->getTitle() . "(ID:" . $blog->getUri() . ")</label>";
			}
		}
		return implode("<br />\n", $html);
	}

/**
	private function getCustomfieldConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/CustomFieldAdvanced.config';
		include_once(dirname(dirname(__FILE__)) . "/class/CustomFieldPluginAdvanced.class.php");
		include_once(dirname(dirname(__FILE__)) . "/class/CustomField.class.php");
		if(file_exists($fname)){
			$obj = unserialize(file_get_contents($fname));
			return $obj->customFields;
		}else{
			return array();
		}
	}
**/
}
?>
