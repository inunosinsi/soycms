<?php

class PluginConvertFilepathPlugin{

	const PLUGIN_ID = "plugin_convert_filepath";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "プラグインのファイルパスの変換",
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

				CMSPlugin::setEvent("onOutput", self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}else{
				// 管理画面側の方で動作する拡張ポイントで使用したいものを追加する			
			}
		}
	}

	function onOutput($args){
		if(!function_exists("imagewebp")) return $html;

		$html = &$args["html"];

		$lines = explode("\n", $html);
		$n = count($lines);
		if($n === 0) return $html;

		for($i = 0; $i < $n; $i++){
			$line = $lines[$i];
			// 画像ファイルがない行は飛ばす
			if(is_bool(stripos($line, "<img"))) continue;
			
			// 一行に複数のimgタグ対応 一行に<img>タグが複数行あるかもしれないので、preg_match_allを利用する
			preg_match_all('/<img.*?>/', $line, $tmps);
			if(!isset($tmps[0]) || !is_array($tmps[0]) || !count($tmps[0])) continue;

			foreach($tmps[0] as $tag){
				// src属性値がなければ飛ばす
				if(is_bool(stripos($tag, "src"))) continue;

				// src属性値を取得 src属性値でスラッシュから始まっていない場合は除く
				preg_match('/src=\"(.*?)\"/i', $tag, $tmp);
				if(!isset($tmp[1]) || $tmp[1][0] != "/") continue;
				
				// ファイル名を取得 最後のスラッシュの後の文字列がファイル名
				$filename = substr($tmp[1], strrpos($tmp[1], "/") + 1);
				
				// 多言語用の画像があるか？を調べる
				$src = str_replace($filename, "en/".$filename, $tmp[1]);
				if(!file_exists($_SERVER["DOCUMENT_ROOT"].$src)) continue;

				// 多言語用の画像のsrc属性と入れ替える
				$line = str_replace($tmp[1], $src, $line);
			}

			// 行の上書き
			$lines[$i] = $line;
		}

		return implode("\n", $lines);
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new PluginConvertFilepathPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

PluginConvertFilepathPlugin::register();

