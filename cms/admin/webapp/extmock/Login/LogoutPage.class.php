<?php
class LogoutPage extends WebPage{

	function __construct() {
		SOY2::import("action.login.LogoutAction");
		$action = SOY2ActionFactory::createInstance('LogoutAction');
		$action->run();
		SOY2PageController::jump("");
    }
}
?>