<?php

class UploadFormComponent {

	public static function build(){
		$html = array();
		$html[] = "<label>画像のアップロード(" . ini_get("upload_max_filesize") . ")</label><br>";
		$html[] = "<input type=\"file\" name=\"image\" accept=\"image/*\">";
		$html[] = "<button class=\"btn btn-secondary\" id=\"upload_button\">画像のアップロード</button>";

		$html[] = "<script>";
		$html[] = "(function(){";
		$html[] = "	$(\"#upload_button\").on(\"click\", function(){";
		$html[] = "		var formObj = $(\"#post_form\");";
		$html[] = "		formObj.attr(\"novalidate\", true);";
		$html[] = "		$(\"<input>\").attr({";
		$html[] = "			\"type\":\"hidden\",";
		$html[] = "			\"name\":\"upload\",";
		$html[] = "			\"value\":\"アップロード\"";
		$html[] = "		}).appendTo(formObj);";
		$html[] = "		formObj.submit();";
		$html[] = "	});";
		$html[] = "}());";
		$html[] = "function remove_image(filename){";
		$html[] = "	var ipts = document.getElementsByName(\"soy2_token\");";
		$html[] = "	location.href=location.pathname + \"?remove=\" + filename + \"&soy2_token=\" + ipts[0].value;";
		$html[] = "}";
		$html[] = "</script>";

		$html[] = "<style>";
		$html[] = "div.upload-image{";
		$html[] = "	position: relative;";
		$html[] = "}";
		$html[] = "button.close {";
		$html[] = "	position:absolute;";
		$html[] = "	z-index:1;";
		$html[] = "	width:24px;";
		$html[] = "	top: 5%;";
		$html[] = "	left: 85px;";
		$html[] = "	background-color: white;";
		$html[] = "	border-radius: 50%;";
		$html[] = "}";
		$html[] = "</style>";

		return implode("\n", $html);
	}
}
