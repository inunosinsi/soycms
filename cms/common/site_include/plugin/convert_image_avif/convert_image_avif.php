<?php
ConvertImageAvifPlugin::registerPlugin();

class ConvertImageAvifPlugin {

	const PLUGIN_ID = "convert_image_avif";

	//挿入するページ
	var $config_per_page = array();
	var $config_per_blog = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=> "AVIF変換プラグイン",
			"type" => Plugin::TYPE_IMAGE,
			"description"=> "JPG等の画像ファイルをAVIF(AV1 Image File Format)形式の画像ファイルに変換する",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co/article/4919",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.3"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		if(!function_exists("imageavif")) return $html;

		$html = &$arg["html"];
		$page = &$arg["page"];

		//404ページの場合は調べない
		if($page->getPageType() == Page::PAGE_TYPE_ERROR) return $html;

		//当プラグインの対象ページであるか？
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				switch(SOYCMS_BLOG_PAGE_MODE){
					case CMSBlogPage::MODE_TOP:
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(!isset($this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE]) || $this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE] != 1) return $html;
						break;
					case CMSBlogPage::MODE_RSS:
					case CMSBlogPage::MODE_POPUP:
						return $html;
				}
				break;
			case Page::PAGE_TYPE_NORMAL:
			case Page::PAGE_TYPE_APPLICATION:
			default:
				//何もしない
		}

		$lines = explode("\n", $html);
		if(!count($lines)) return $html;

		if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");

		$htmls = array();
		foreach($lines as $line){
			//画像ファイルのある行を探す
			if(is_numeric(stripos($line, "<img"))){
				//一行に複数のimgタグ対応
				preg_match_all('/<img.*?>/', $line, $tmp);
				if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
					foreach($tmp[0] as $imgTag){
						$props = x_get_properties_by_img_tag($imgTag);
						if(count($props) && isset($props["src"])){
							//ファイルが存在しているか？
							$filepath = x_build_filepath($props["src"]);
							if(!file_exists($filepath)) continue;

							// WebPに変換する
							$ext = x_get_extension_by_filepath($props["src"]);
							if(!strlen($ext) || $ext == "avif") continue;

							$new = x_convert_file_extension($filepath, "avif");
							if(!file_exists($new)){
								switch($ext){
									case "jpg":
										$img = imagecreatefromjpeg($filepath);
										break;
									case "png":
										$src = imagecreatefrompng($filepath);
										$img = imagecreatetruecolor(imagesx($src), imagesy($src));
										$bgc = imagecolorallocate($img, 255, 255, 255);
										imagefilledrectangle($img, 0, 0, imagesx($src), imagesx($src), $bgc);
										imagecopy($img, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
										break;
									case "git":
										$img = imagecreatefromgif($filepath);
										break;
									case "webp":
										$img = @imagecreatefromwebp($filepath);
										break;
									default:
										//何もしない
										$img = null;
								}

								if(!$img instanceof GdImage) continue;

								// 縦横比
								$exif = exif_read_data($filepath);
								if(isset($exif["Orientation"])){
									switch($exif["Orientation"]){
										case 3:
											$rotate = 180;
											break;
										case 6:
											$rotate = 270;
											break;
										case 8:
											$rotate = 90;
											break;
										default:
											$rotate = 0;
									}

									if($rotate > 0) $img = imagerotate($img, $rotate, 0);
								}

								imageavif($img, $new);
							}

							// webpファイルの生成に失敗した場合は処理を飛ばす
							if(!file_exists($new)) continue;

							$props["src"] = x_convert_file_extension($props["src"], "avif");
							$newTag = x_rebuild_image_tag($imgTag, $props);
							
							if(strlen($newTag) && $imgTag != $newTag){
								$line = str_replace($imgTag, $newTag, $line);
							}
						}
					}
				}
			}

			$htmls[] = $line;
		}

		return implode("\n", $htmls);
	}

	function config_page($message){
		SOY2::import("site_include.plugin.convert_image_avif.config.AvifConfigPage");
		$form = SOY2HTMLFactory::createInstance("AvifConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new ConvertImageAvifPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
