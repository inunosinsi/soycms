<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CommonSoymailCustomfieldModule extends SOYShopOrderCustomfield{
	
	function clear(CartLogic $cart){
		$cart->clearAttribute("common_soymail.value");
		$cart->clearOrderAttribute("common_soymail");
	}
	
	function doPost($param){
		
		$cart = $this->getCart();
		
		if(isset($param["soymail"]) && $param["soymail"] == 1){
			$flag = true;
			$message = "希望する";
		}else{
			$flag = false;
			$message = "希望しない";
		}
		
		$cart->setAttribute("common_soymail.value", $flag);
		$cart->setOrderAttribute("common_soymail", "メールマガジン", $message);
	}
	
	function order(CartLogic $cart){
		
		$value = $cart->getAttribute("common_soymail.value");
		
		if($value){
			$this->updateSOYMailDB($cart->getCustomerInformation());
		}
	}

	function hasError($param){	
	}

	function getForm(CartLogic $cart){
		
		$value = $cart->getAttribute("common_soymail.value");
		
		$obj = array();
		$obj["name"] = "メールマガジン";
		if($value){
			$body = "<input type=\"checkbox\" name=\"customfield_module[soymail]\" value=\"1\" id=\"soymail_module\" checked=\"checked\" />";
		}else{
			$body = "<input type=\"checkbox\" name=\"customfield_module[soymail]\" value=\"1\" id=\"soymail_module\" />";
		}
		
		$body .= "<label for\"soymail_module\">希望する</label>";
		$obj["description"] = $body;
		
		$obj["error"] = "";
		
		$array = array();
		$array[] = $obj;
		
		return $array;
	}
	
	function display($orderId){		
	}
	
	function updateSOYMailDB($user){
		//ショップネームを取得しておく
		$config = SOYShop_ShopConfig::load();
		$shopName = $config->getShopName();
		
		//SQLiteのみ対応とする
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		$cmsDir = str_replace("/soyshop/webapp/src/","/common/",$oldDaoDir);
		$pagDir = str_replace("/soyshop/","/app/webapp/mail/pages/",$oldPagDir);
		$rooDir = str_replace("/soyshop/webapp/src/","/app/webapp/mail/src/",$oldRooDir);
		$daoDir = str_replace("/soyshop/webapp/src/","/app/webapp/mail/src/",$oldDaoDir);
		$entityDir = str_replace("/soyshop/webapp/src/","/app/webapp/mail/src/",$oldDaoDir);
		$dbDir = str_replace("domain/","db/soymail.db",$cmsDir);
		
		if(file_exists($dbDir)){
			
			SOY2::RootDir($rooDir);
			SOY2HTMLConfig::PageDir($pagDir);
			SOY2DAOConfig::DaoDir($daoDir);
			SOY2DAOConfig::EntityDir($entityDir);
			
			$commonDir = str_replace("domain/","",$cmsDir);
			
			include($commonDir."soycms.config.php");
			if(defined("SOYCMS_DB_TYPE")&&SOYCMS_DB_TYPE=="mysql"){				
				include($commonDir."config/db/mysql.php");
				SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
				SOY2DAOConfig::user(ADMIN_DB_USER);
				SOY2DAOConfig::pass(ADMIN_DB_PASS);	
			//sqlite
			}else{
				SOY2DAOConfig::Dsn("sqlite:" . $dbDir);
			}
			
			SOY2::import("domain.SOYMailUser");
			$register = SOY2::cast("SOYMailUser",$user);
			
			$register->setAttribute3("ショップ名:" . $shopName);
			
			$dao = SOY2DAOFactory::create("SOYMailUserDAO");
			
			try{
				$id = $dao->getIdByEmail($register->getMailAddress());
				if(!$id){
					$dao->insert($register);
				}else{
					$register->setId($id);
					$dao->update($register);
				}
			}catch(Exception $e){
				$dao->insert($register);
			}
		}
		
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);
		
		return true;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield","common_soymail","CommonSoymailCustomfieldModule");
?>