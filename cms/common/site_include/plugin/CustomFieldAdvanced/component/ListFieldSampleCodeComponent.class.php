<?php

class ListFieldSampleCodeComponent {

	/**
	 * @param string, bool
	 * @return string
	 */
	public static function build(string $tag, bool $isImageUploadForm){
		$html = array();
		$html[] = "<!-- cms:id=\"" . $tag . "_visible\" -->";
		$html[] = "<ul>";
		$html[] = "	<!-- cms:id=\"" . $tag . "_list\" -->";
		if($isImageUploadForm){
			$html[] = "	<!-- cms:id=\"is_image\" -->";
			$html[] = "	<li><a cms:id=\"image_link\"><img cms:id=\"image\"></a></li>";
			$html[] = "	<!-- /cms:id=\"is_image\" -->";
			$html[] = "";
			$html[] = "	<!-- cms:id=\"no_image\" -->";
			$html[] = "	<li><!-- cms:id=\"li\" -->リンゴ<!-- /cms:id=\"li\" --></li>";
			$html[] = "	<!-- /cms:id=\"no_image\" -->";
		}else{
			$html[] = "	<li><!-- cms:id=\"li\" -->リンゴ<!-- /cms:id=\"li\" --></li>";
		}
		
		$html[] = "	<!-- /cms:id=\"" . $tag . "_list\" -->";
		$html[] = "</ul>";
		$html[] = "<!-- /cms:id=\"" . $tag . "_visible\" -->";
		return implode("\n", $html);
	}
}