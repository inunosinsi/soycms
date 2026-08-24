<?php
class SuspiciousPage extends CMSWebPageBase{

	private function _register(int $id){
		$dao = SOY2DAOFactory::create("admin.IpAddressDAO");
		try{
			$ipObj = $dao->getById($id);
		}catch(Exception $e){
			$ipObj = new IpAddress();
		}

		$ip = (string)$ipObj->getIpV4();
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			SOY2::import("util.CMSAccessRestrictionsUtil");
			CMSAccessRestrictionsUtil::saveConfig(CMSAccessRestrictionsUtil::MODE_MALICIOUS, $ip);
		}

		try{
			$dao->deleteById($id);
		}catch(Exception $e){
			//
		}
	
		$this->jump("NotFound.Suspicious");
	}

	function __construct(){
		if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("");
		}

		if(isset($_GET["ip_id"]) && soy2_check_token()){
			self::_register($_GET["ip_id"]);
		}

		SOY2::import("util.AccessLogUtil");
		
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("ip_list", "_common.Log.SuspiciousIpAddressListComponent", array(
			"list"	=> SOY2DAOFactory::create("admin.IpAddressDAO")->getSuspiciousIpAddressList()
		));
	}
}
