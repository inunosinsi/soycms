<?php

class MultiUploaderFormComponent {

	public static function buildForm(int $entryId, array $labelIds){
		$html = array();

		$html[] = "<script>";
		$html[] = "function open_multi_uploader_filemanager(\$form){";
		$html[] = "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload?multi_uploader") . "\");";
		$html[] = "}";
		$html[] = "</script>";

		//ラベルと連動の設定
		if(count($labelIds)){
			$classProps = array();
			foreach($labelIds as $labelId){
				$classProps[] = "toggled_by_label_" . $labelId;
			}

			$html[] = "<div class=\"" . implode(" ", $classProps) . "\" style=\"display:none;\">";
		}else{
			$html[] = "<div>";
		}

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>画像アップローダ</label>";
		$html[] = "<div class=\"form-inline\">";
		$html[] = "<input type=\"text\" class=\"custom_field_input form-control\" style=\"width:50%\" id=\"multi_uploader\" name=\"" . MultiUploaderUtil::FIELD_ID . "\" value=\"\" readonly=\"readonly\"> ";
		$html[] = "<button type=\"button\" class=\"btn btn-default\" onclick=\"open_multi_uploader_filemanager($('#multi_uploader'));\" style=\"margin-right:10px;\">ファイルを指定する</button>";
		$html[] = "</div>";
		$html[] = "</div>";

		$images = (is_numeric($entryId) && $entryId > 0) ? MultiUploaderUtil::getImagePathes($entryId) : array();
		if(is_array($images) && count($images)){
			$alts = MultiUploaderUtil::getAltList($entryId);
			$html[] = "<table class=\"table table-striped\" style=\"width:600px;\">";
			$html[] = "<thead>";
			$html[] = "<tr><th>&nbsp;</th><th class=\"text-center\">並び順</th><th>&nbsp;</th></tr>";
			$html[] = "</thead>";
			$html[] = "<tbody>";
			foreach($images as $idx => $img){
				$hash = MultiUploaderUtil::path2Hash($img);	//並べ替えや削除の時に使う
				$alt = (isset($alts[$hash])) ? htmlspecialchars($alts[$hash], ENT_QUOTES, "UTF-8") : "";

				$html[] = "<tr>";
				$html[] = "	<td class=\"text-center\" rowspan=\"2\">";
				$html[] = "		<img src=\"/" . UserInfoUtil::getSite()->getSiteId() . "/im.php?src=" . $img . "&width=150\">";
				$html[] = "	</td>";
				$html[] = "	<td class=\"text-center\"><input type=\"number\" class=\"form-control\" name=\"" . MultiUploaderUtil::FIELD_ID . "_sort[" . $hash . "]\" style=\"width:80px;\" value=\"\"></td>";
				$html[] = "	<td class=\"text-center\">";
				$html[] = "		<a href=\"javascript:void(0);\" class=\"btn btn-warning\" id=\"multi_uploader_delete_btn_" . $hash . "\" onclick=\"toggle_multi_uploader_delete('" . $hash . "');\">削除</a>";
				$html[] = "		<input type=\"hidden\" name=\"" . MultiUploaderUtil::FIELD_ID . "_delete[" . $hash . "]\" id=\"multi_uploader_delete_" . $hash . "\" value=\"0\">";
				$html[] = "	</td>";
				$html[] = "</tr>";
				$html[] = "<tr>";
				$html[] = "	<td colspan=\"2\" class=\"form-inline\">";
				$html[] = "		alt：<input type=\"text\" class=\"form-control\" name=\"" . MultiUploaderUtil::FIELD_ID . "_alt[" . $hash . "]\" value=\"" . $alt . "\">";
				$html[] = "	</td>";
				$html[] = "</tr>";
			}
			$html[] = "</tbody>";
			$html[] = "</table>";
		}
		$html[] = "</div>";

		$html[] = "<script>";
		$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/delete.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}

	/**
	 * 念の為にURLからサイトIDを除いておく
	 * @param string url
	 * @return string url
	 */
	private static function _getDomainUrl(){
		$url = (string)soycms_get_site_config_object()->getConfigValue("url");
		$siteId = UserInfoUtil::getSite()->getSiteId();

		if(strpos($url, "/" . $siteId . "/")){
			$url = str_replace("/" . $siteId . "/", "/" , $url);
		}

		return $url;
	}
}
