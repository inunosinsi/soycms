<?php
RestrictIpAddressPlugin::register();

class RestrictIpAddressPlugin{

	const PLUGIN_ID = "restrict_ip_address";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ページ毎IPアドレスアクセス制限プラグイン",
			"description"=>"ページ毎でIPアドレスによるアクセス制限を設定する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//公開側
		if(defined("_SITE_ROOT_")){
			CMSPlugin::setEvent('onPageLoad', self::PLUGIN_ID, array($this,"onPageLoad"), array("filter" => "all"));
		}else{
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
			CMSPlugin::setEvent('onBlogPageConfigUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
			CMSPlugin::setEvent('onPageRemove', self::PLUGIN_ID, array($this, "onPageRemove"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Page.Detail", array($this, "onCallCustomField"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Config", array($this, "onCallCustomField"));
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.restrict_ip_address.config.RestrictConfigPage");
		$form = SOY2HTMLFactory::createInstance("RestrictConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onPageLoad($args){
		$pageId = $_SERVER["SOYCMS_PAGE_ID"];
		$ips = self::_ip_addresses($pageId);
		if(!count($ips)) return;	//何もしない

		if(!isset($_SERVER["REMOTE_ADDR"])) $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		
		$hit = false;
		foreach($ips as $ip){
			if($ip == $_SERVER["REMOTE_ADDR"]){
				$hit = true;
				break;
			}
		}
		
		if(!$hit) soycms_jump_notfound_page();
	}

	/**
	 * ラベル更新時
	 */
	function onPageUpdate($arg){
		if(!isset($arg["new_page"])) return;
		$pageId = (int)$arg["new_page"]->getId();
		if($pageId === 0) return;

		$v = trim((string)$_POST[self::PLUGIN_ID]);
		if(strlen($v)){
			$ips = explode("\n", $v);
			$list = array();
			foreach($ips as $ip){
				$ip = trim($ip);
				if(preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip)) {
					$list[] = $ip;
				}
			}
			$v = trim(implode("\n", $list));
		}

		$attr = soycms_get_page_attribute_object($pageId, self::PLUGIN_ID);
		if(!strlen($v)) $v = null;
		$attr->setValue($v);
		soycms_save_page_attribute_object($attr);

		return true;
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onPageRemove(array $args){
		foreach($args as $pageId){
			$attr = soycms_get_page_attribute_object($pageId, self::PLUGIN_ID);
			$attr->setValue(null);
			soycms_save_page_attribute_object($attr);
		}

		return true;
	}

	/**
	 * ラベル編集画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0])) ? (int)$arg[0] : 0;
		
		$h = array();
		$h[] = "<div class=\"form-group\">";
		$h[] = "<label>IPアドレスによるアクセス制限</label>";
		$h[] = "<textarea name=\"".self::PLUGIN_ID."\" class=\"form-control\" style=\"width:300px;height:100px;\" placeholder=\"IPアドレスを改行区切りで指定します。\">".implode("\n", self::_ip_addresses($pageId))."</textarea>";
		$h[] = "</div>";

		return implode("\n", $h);
	}

	/**
	 * @paran int
	 * @return array
	 */
	private function _ip_addresses(int $pageId){
		$v = trim((string)soycms_get_page_attribute_object($pageId, self::PLUGIN_ID)->getValue());
		return (strlen($v)) ? explode("\n", $v) : array();
	}

	private function _log_dir(){
		$dir = __DIR__."/.log/";
		if(file_exists($dir)) return $dir;
		mkdir($dir);
		return $dir;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new RestrictIpAddressPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
