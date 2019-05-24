<?php

class CalendarExpandSeatUserOnOutput extends SOYShopSiteUserOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		//改行
		$html = str_replace("{#br#}", "<br>", $html);
		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "calendar_expand_seat", "CalendarExpandSeatUserOnOutput");
