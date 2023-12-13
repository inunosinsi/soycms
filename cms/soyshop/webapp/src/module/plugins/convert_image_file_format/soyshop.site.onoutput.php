<?php
class ConvertImageFileFormatOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput(string $html){
		SOY2::import("module.plugins.convert_image_file_format.util.ImgFmtUtil");
		$to = ImgFmtUtil::getImageFormat();	// empty or webp or avif
		if($to == ImgFmtUtil::FMT_TYPE_EMPTY) return $html;

		//404ページの場合は処理を停止する
		if(SOYSHOP_PAGE_ID > 0 && soyshop_get_page_object(SOYSHOP_PAGE_ID)->getUri() == SOYSHOP_404_PAGE_MARKER) return $html;

		$lines = explode("\n", $html);
		if(!count($lines)) return $html;

		if(!function_exists("z_get_properties_by_img_tag")) SOY2::import("module.plugins.convert_image_file_format.func.fn", ".php");

		if(defined("SOYSHOP_APPLICATION_MODE") && SOYSHOP_APPLICATION_MODE){
			// アプリケーションページの表示設定はsoyshop.site.user.onoutputの方でチェックしている

		}else{	//通常ページの場合は表示の設定状況の確認
			$cnf = ImgFmtUtil::getPageDisplayConfig();
			if(!isset($cnf[SOYSHOP_PAGE_ID]) || $cnf[SOYSHOP_PAGE_ID] != 1) return $html;
			unset($cnf);
		}

		$htmls = array();
		foreach($lines as $line){
			//画像ファイルのある行を探す
			if(is_numeric(stripos($line, "<img"))){
				//一行に複数のimgタグ対応
				preg_match_all('/<img.*?>/', $line, $tmp);
				if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
					foreach($tmp[0] as $imgTag){
						$props = z_get_properties_by_img_tag($imgTag);
						if(count($props) && isset($props["src"])){
							//ファイルが存在しているか？
							$filepath = z_build_filepath($props["src"]);
							if(!file_exists($filepath)) continue;

							// WebPに変換する
							$from = z_get_extension_by_filepath($props["src"]);
							if(!strlen($from) || $from == $to) continue;

							$new = z_convert_file_extension($filepath, $to);
							if(!file_exists($new)){
								$img = null;
								switch($from){
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
										if(file_exists("imagecreatefromwebp")){
											$img = @imagecreatefromwebp($filepath);
										}
										break;
									case "avif":
										if(file_exists("imagecreatefromavif")){
											$img = @imagecreatefromavif($filepath);
										}
										break;
									default:
										//何もしない
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

								switch($to){
									case ImgFmtUtil::FMT_TYPE_WEBP:
										imagewebp($img, $new);
										break;
									case ImgFmtUtil::FMT_TYPE_AVIF:
										imageavif($img, $new);
										break;
									default:
										//何もしない
								}
							}

							// webpファイルの生成に失敗した場合は処理を飛ばす
							if(!file_exists($new)) continue;

							$props["src"] = z_convert_file_extension($props["src"], $to);
							$newTag = z_rebuild_image_tag($imgTag, $props);
							
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
}

SOYShopPlugin::extension("soyshop.site.onoutput", "convert_image_file_format", "ConvertImageFileFormatOnOutput");
