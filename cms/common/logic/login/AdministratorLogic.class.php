<?php
SOY2::import("domain.admin.Administrator");

/**
 * 使わない
 */
class AdministratorLogic extends Administrator implements SOY2LogicInterface{

	public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}


}
