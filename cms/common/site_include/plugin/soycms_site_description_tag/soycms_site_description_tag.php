<?php

SiteDescriptionTagPlugin::registerPlugin();

class SiteDescriptionTagPlugin{

	const PLUGIN_ID = "soycms_site_description_tag";

	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 初期化
	 */
	function init(){
		CMSPlugin::addPluginMenu($this->getId(), array(
			"name" => "サイトの説明表示プラグイン",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "info@saitodev.co",
			"version" => "1.0"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

        if(CMSPlugin::activeCheck($this->getId())){
			//公開画面側
			if(defined("_SITE_ROOT_")){
				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
			}
        }
	}

	function onPageOutput($obj){
		$desc = SOY2Logic::createInstance("logic.site.SiteConfig.SiteConfigLogic")->get()->getDescription();
		
		$obj->addLabel("site_description", array(
			"soy2prefix" => "cms",
			"text" => $desc
		));

		$obj->addLabel("site_description_raw", array(
			"soy2prefix" => "cms",
			"html" => $desc
		));
	}

	/**
	 * 設定画面の表示
	 */
	function config_page($message){
		return <<<HTML
<div class="alert alert-info">サイト設定のページにある「サイトの説明」用のタグが使用できます</div>
<table class="table table-striped">
	<thead>
		<tr>
			<th>cms:id</th>
			<th>タグの種類</th>
			<th>タグの説明</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>site_description</td>
			<td>すべて</td>
			<td>HTMLタグが無効の状態のサイトの説明を出力</td>
		</tr>
		<tr>
			<td>site_description_raw</td>
			<td>すべて</td>
			<td>HTMLタグが有効の状態のサイトの説明を出力</td>
		</tr>
	</tbody>
</table>
HTML;
	}


	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new SiteDescriptionTagPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
