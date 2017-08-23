<?php
SOY2::import("domain.admin.Administrator");

/**
 * ASP版のログイン処理を行う
 */
class ASPLoginLogic extends Administrator implements SOY2LogicInterface{

	public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}
	
	/**
	 * ログイン処理を行う
	 * 
	 * @return boolean ログイン成功したかどうか
	 */
	function login($userid,$password){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{		
			$bean = $dao->getByUserId($userid);
		}catch(Exception $e){
			echo "<!--".var_export($e,true)."-->";
			return false;
		}
		
		SOY2::cast($this,$bean);
		
		if($bean->getUserId() == $userid && $bean->getUserPassword() == $password){
			return true;
		}else{
			return false;
		}
		
		
	}
		
}

    

?>