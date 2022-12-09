<?php

class ImageModalComponent {

	public static function build(){
		$html = array();
		$html[] = "<div class=\"modal fade\" id=\"image-modal\">";
		$html[] = "	<div class=\"modal-dialog\">";
		$html[] = "		<div class=\"modal-body\">";
		$html[] = "			<img id=\"modal-img\" src=\"\" class=\"aligncenter\">";
		$html[] = "		</div>";
		$html[] = "	</div>";
		$html[] = "</div>";

		$html[] = "<script>";
		$html[] = "function openModal(src){";
		$html[] = "	$(\"#modal-img\").prop(\"src\", src);";
		$html[] = "	$(\"#image-modal\").modal();";
		$html[] = "}";
		$html[] = "</script>";
		return implode("\n", $html);
	}
}
