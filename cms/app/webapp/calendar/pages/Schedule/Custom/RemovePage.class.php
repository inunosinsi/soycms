<?php

class RemovePage extends WebPage{

	function __construct($args) {
		if(!isset($args[0])){
			CMSApplication::jump("Schedule.Custom");
		}
		$customId = (int)$args[0];
		
		try{
			SOY2DAOFactory::create("SOYCalendar_CustomItemDAO")->deleteById($customId);
		}catch(Exception $e){
			//
		}

		CMSApplication::jump("Schedule.Custom?deleted");
	}
}
