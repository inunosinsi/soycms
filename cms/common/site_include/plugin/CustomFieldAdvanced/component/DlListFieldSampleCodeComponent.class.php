<?php

class DlListFieldSampleCodeComponent {

	/**
	 * @param string, bool
	 * @return string
	 */
	public static function build(string $tag, bool $isImageUploadForm){
		$html = array();
		$html[] = "<!-- cms:id=\"" . $tag . "_visible\" -->";
		$html[] = "<dl>";
		$html[] = "	<!-- cms:id=\"" . $tag . "_dl_list\" -->";
		$html[] = "	<dt><!-- cms:id=\"dt\" -->リンゴ<!-- /cms:id=\"dt\" --></dt>";
		if($isImageUploadForm){
			$html[] = "";
			$html[] = "	<!-- cms:id=\"is_image\" -->";
			$html[] = "	<dd><img cms:id=\"image\"></dd>";
			$html[] = "	<!-- /cms:id=\"is_image\" -->";
			$html[] = "";
			$html[] = "	<!-- cms:id=\"no_image\" -->";
			$html[] = "	<dd><!-- cms:id=\"dd\" -->赤<!-- /cms:id=\"dd\" --></dd>";
			$html[] = "	<!-- /cms:id=\"no_image\" -->";
		}else{
			$html[] = "	<dd><!-- cms:id=\"dd\" -->赤<!-- /cms:id=\"dd\" --></dd>";
		}
		$html[] = "	<!-- /cms:id=\"" . $tag . "_dl_list\" -->";
		$html[] = "</dl>";
		$html[] = "<!-- /cms:id=\"" . $tag . "_visible\" -->";
		return implode("\n", $html);
	}
}