<?php

class PluginCustomfieldPlugin{

	const PLUGIN_ID = "plugin_customfield";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "プラグインのカスタムフィールドサンプル",
			"type" => Plugin::TYPE_NONE,
			"description" => "",
			"author" => "",
			"url" => "",
			"mail" => "",
			"version" => "1.0"
		));
		
		// 当プラグインが有効であるかを調べる
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				// 公開側ページの方で動作する拡張ポイントで使用したいものを追加する
			
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する
				
				/**
				 * 記事投稿画面にフォームを表示する
				 * 注) 記事投稿画面とブログページ毎の記事投稿画面で分かれる
				 * 　→記事IDの取得方法が異なる為
				 */
				CMSPlugin::addCustomFiledFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFiledFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_onBlog"));
			}
		}
	}

	// 記事投稿画面用
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildCommonForm($entryId);
	}

	// 各ブログページからの記事投稿画面用
	function onCallCustomField_onBlog(){
		$arg = SOY2PageController::getArguments();
		// 記事IDの取得時の配列のインデックスの指定に注意
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildCommonForm($entryId);
	}

	/** 
	 * カスタムフィールドの共通箇所
	 * @param int
	 * @return html
	 */
	private function _buildCommonForm(int $entryId){
		// PHPで直接出力
return <<<HTML
<div class="form-group">
	<label>hoge</label>
	<input type="text" class="form-control">
</div>
HTML;

		// SOY2HTML経由
		// SOY2::import("site_include.plugin.plugin_customfield.form.PluginCustomfieldSamplePage");
		// $form = SOY2HTMLFactory::createInstance("PluginCustomfieldSamplePage");
		// $form->setEntryId($entryId);
		// $form->execute();
		// return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginCustomfieldPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginCustomfieldPlugin::register();
