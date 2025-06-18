<?php

class DlListTextFieldSampleCodeComponent {

	/**
	 * @param string
	 * @return string
	 */
	public static function build(string $tag){
		$html = array();
		$html[] = "<!-- cms:id=\"" . $tag . "_visible\" -->";
		$html[] = "<dl>";
		$html[] = "	<!-- cms:id=\"" . $tag . "_dl_list\" -->";
		$html[] = "	<dt><!-- cms:id=\"dt_raw\" -->リンゴ<!-- /cms:id=\"dt_raw\" --></dt>";
		$html[] = "	<dd><!-- cms:id=\"dd_raw\" -->赤<!-- /cms:id=\"dd_raw\" --></dd>";
		$html[] = "	<!-- /cms:id=\"" . $tag . "_dl_list\" -->";
		$html[] = "</dl>";
		$html[] = "<!-- /cms:id=\"" . $tag . "_visible\" -->";
		return implode("\n", $html);
	}
}
