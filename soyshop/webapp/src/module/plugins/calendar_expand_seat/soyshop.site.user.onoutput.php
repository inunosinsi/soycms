<?php

class CalendarExpandSeatUserOnOutput extends SOYShopSiteUserOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput(string $html){
		//改行
		return str_replace("{#br#}", "<br>", $html);
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "calendar_expand_seat", "CalendarExpandSeatUserOnOutput");
