<?php

SmartphoneBlogPlugin::register();

class SmartphoneBlogPlugin{

	const PLUGIN_ID = "smartphone_blog";

	function getId(){
		return self::PLUGIN_ID;
	}

	private $extensions = array(
	    'image/gif' => 'gif',
	    'image/jpeg' => 'jpg',
	    'image/png' => 'png'
	);

	private $maxWidth = 640;	//投稿した画像の幅のリサイズ

	const MODE_CONTENT = 0;
	const MODE_MORE = 1;

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "スマホでブログ投稿プラグイン",
			"description" => "スマホからブログを投稿できるようにする",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/3472",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.5"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
		}
	}

	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		//本文と追記にあるbase64形式の画像を指定のサイズの画像に変換する
		$isChange = false;
		$try = 0;
		for(;;){
			if($try > 1) break;

			$mode = ($try++ === 0) ? self::MODE_CONTENT : self::MODE_MORE;
			switch($mode){
				case self::MODE_MORE:
					$content = trim($entry->getMore());
					break;
				case self::MODE_CONTENT:
				default:
					$content = trim($entry->getContent());
			}

			if(!strlen($content)) continue;

			//一行ずつ調べる
			$lines = explode("\n", $content);
			if(!count($lines)) continue;

			$texts = array();
			for($i = 0; $i < count($lines); $i++){
				$line = trim($lines[$i]);
				$txt = null;	//画像タグの横にテキストがある場合は次の行にする
				// @ToDo base64形式の画像をjpgに変換する
				if(strpos($line, "<img") !== false && strpos($line, "data:") !== false){

					//大きな画像対策 大きい画像では正規表現が使えない
					$enc = str_replace(array("<p>", "</p>"), "", $line);
					$enc = str_replace("img src=", "", $enc);
					$enc = str_replace(array("<", ">"), "", $enc);
					$enc = rtrim($enc, "/");
					$enc = trim($enc);
					$enc = trim($enc, "\"");

					//もしスペーススラッシュがある場合はその後はテキストがある
					if(strpos($enc, " /")){
						$txt = substr($enc, strpos($enc, " /"));
						$txt = str_replace(" /", "", $txt);
						if(strpos($txt, "data:image")){	//同じ行に複数の画像がある場合
							$txt = null;

						}
						$enc = substr($enc, 0, strpos($enc, " /"));
						$enc = rtrim($enc, "/");
						$enc = trim($enc);
						$enc = trim($enc, "\"");
					}

					$mime_row = substr($enc, 0, strpos($enc, ";") + 1);
					$enc = substr($enc, strpos($enc, ";") + 1);

					$mime_type = str_replace("data:", "", $mime_row);
					$mime_type = str_replace(";", "", $mime_type);

					if(isset($this->extensions[$mime_type])){
						$enc = substr($enc, strpos($enc, ",") + 1);
						$data = base64_decode($enc);

						//MIMEタイプから拡張子を選択してファイル名を作成
						$filename = md5(time() . rand(1, 10)) . "." . $this->extensions[$mime_type];
						$uploadDir = self::_getUploadDirectory();

						$dist = UserInfoUtil::getSiteDirectory() . $uploadDir . "/" . $filename;

						// 画像ファイルの保存
						file_put_contents($dist, $data);

						// @ToDo maxWidth以上の画像をリサイズする
						$info = getimagesize($dist);
						$w = $info[0];
						$h = $info[1];

						//リサイズ
						if($w > $this->maxWidth){
							soy2_resizeimage($dist, $dist, $this->maxWidth);
						}

						//quetzli
						exec("guetzli --quality 84 " . $dist . " " . $dist);

						$line = "<p><img src=\"/" . UserInfoUtil::getSite()->getSiteId() . "/" . $uploadDir . "/" . $filename . "\"></p>";

						$isChange = true;
					}
				}

				$texts[] = $line;

				//画像タグの横にテキストがある場合は次の行にする
				if(isset($txt)) $texts[] = "<p>" . $txt . "</p>";
			}

			switch($mode){
				case self::MODE_CONTENT:
					$entry->setContent(implode("\n", $texts));
					break;
				case self::MODE_MORE:
					$entry->setMore(implode("\n", $texts));
					break;
			}
		}

		if($isChange){
			try{
				SOY2DAOFactory::create("cms.EntryDAO")->update($entry);
			}catch(Exception $e){
				//
			}
		}
	}

	private function _getUploadDirectory(){
		// 空文字列または/dir/**/path
		$dir = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getUploadDirectory();

		//先頭の/を削除
		if(strlen($dir) && $dir[0] == "/"){
			$dir = substr($dir,1);
		}

    	return $dir;
    }

	function config_page(){
		SOY2::import("site_include.plugin.smartphone_blog.config.SmaphoBlogFormPage");
		$form = SOY2HTMLFactory::createInstance("SmaphoBlogFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getMaxWidth(){
		return $this->maxWidth;
	}
	function setMaxWidth($maxWidth){
		$this->maxWidth = $maxWidth;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SmartphoneBlogPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
