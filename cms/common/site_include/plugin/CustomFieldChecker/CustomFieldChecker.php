<?php
class CustomFieldCheckerPlugin{

	const PLUGIN_ID = "CustomFieldChecker";
	const DIRECTORY_NAME = "customfield_checker";

	private $tags = array(
		//"b_block:id",
		//"m_block;id",
		//"p_block:id",
		//"block:id",
		"cms:id",
		//"app:id",
		//"csf:id",
		//"cms:module",
	);

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"カスタムフィールドチェッカー",
			"type" => Plugin::TYPE_DB,
			"description"=>"カスタムフィールドの使用状況を調べます",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/",
			"mail"=>"saito@saitodev.co",
			"version"=>"1.4.2"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			if(!defined("_SITE_ROOT_")){
				if(!function_exists("checker_fn_get_template")) SOY2::import("site_include.plugin.cms_tag_checker.func.fn", ".php");

				CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onBlogPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
	
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Page.Notice", array($this, "onPageNotice"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Notice", array($this, "onBlogNotice"));
			}
		}
	}

	function onPageUpdate($args){
		$page = &$args["new_page"];
		$template = checker_fn_get_template($page);
		
		// キャッシュの削除
		$cacheFilePath = checker_fn_build_cache_file_path((int)$page->getId(), self::DIRECTORY_NAME);
		if(file_exists($cacheFilePath)) unlink($cacheFilePath);

		if(is_null($template)) return;

		// @ToDo いずれはカスタムサーチフィールド等の分も用意する
		if(!function_exists("checker_fn_update_normal_tag_list")) SOY2::import("site_include.plugin.CustomFieldChecker.func.fn", ".php");
		$tags = checker_fn_get_tag_list();
		
		$errs = array();

		foreach($this->tags as $tagType){
			preg_match_all('/'.$tagType.'=\"(.*?)\"/', $template, $tmps);
			
			if(isset($tmps[1]) && count($tmps[1])){
				$tmps[1] = array_unique($tmps[1]);

				foreach($tmps[1] as $tagName){
					if(!strlen($tagName)) continue;
					if(is_numeric(array_search($tagName, $tags))) continue;

					$errs[] = $tagType."=\"".$tagName."\"";
				}
			}
		}
		
		if(count($errs)) file_put_contents($cacheFilePath, implode("\n", $errs));
	}

	function onPageNotice(){
		$args = SOY2PageController::getArguments();
		if(!isset($args[0]) || !is_numeric($args[0])) return "";
		
		$cacheFilePath = checker_fn_build_cache_file_path((int)$args[0], self::DIRECTORY_NAME);
		if(!file_exists($cacheFilePath)) return "";

		return self::_buildNotice($cacheFilePath);
	}

	function onBlogNotice(){
		$args = SOY2PageController::getArguments();
		if(!isset($args[0]) || !is_numeric($args[0])) return "";
		if(!isset($args[1]) || !is_string($args[1])) return "";
		
		$cacheFilePath = checker_fn_build_cache_file_path((int)$args[0], self::DIRECTORY_NAME);
		if(!file_exists($cacheFilePath)) return "";

		return self::_buildNotice($cacheFilePath);
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _buildNotice(string $path){
		$h = array();
		$h[] = "<div class=\"alert alert-danger\">";
		$h[] = "<p>未定義のcms:idが使用されています。</p>";

		$errs = explode("\n", file_get_contents($path));
		$h[] = "<ul>";
		foreach($errs as $err){
			$h[] = "<li>".$err."</li>";
		}
		$h[] = "</ul>";

		$h[] = "</div>";
		return implode("\n", $h);
	}

	/**
	 * debug
	 */
	private function d(array $a){
		checker_fn_debug_dump_array($a);
	}

	/**
	 * debug
	 */
	private function h(string $str){
		checker_fn_debug_dump_string($str);
	}

	function config_page($message){
		SOY2::import("site_include.plugin.CustomFieldChecker.config.CustomFieldCheckerConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomFieldCheckerConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldCheckerPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

CustomFieldCheckerPlugin::register();
