<?php

class DepositManagerUserButton extends SOYShopUserButtonBase{

	function buttonOnTitle($userId){
		return "<a href=\"" . SOY2PageController::createLink("Extension.Detail.deposit_manager?user_id=" . $userId) . "\" class=\"btn btn-default btn-xs\">入金の登録</a>";
	}
}
SOYShopPlugin::extension("soyshop.user.button", "deposit_manager", "DepositManagerUserButton");
