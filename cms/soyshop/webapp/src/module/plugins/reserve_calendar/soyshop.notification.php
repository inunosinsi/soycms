<?php
class ReserveCalendarOrderNotification extends SOYShopNotification{

	function execute(){

	}
}
SOYShopPlugin::extension("soyshop.notification", "reserve_calendar", "ReserveCalendarOrderNotification");
