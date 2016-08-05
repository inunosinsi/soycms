<?php

class NotificationMailLogic extends SOY2LogicBase{
	
	private $shopConfig;
	private $saleDao;
	
	function __construct(){
		SOY2::import("module.plugins.common_sale_period.util.SalePeriodUtil");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$this->shopConfig = SOYShop_ShopConfig::load();
		
		SOY2::imports("module.plugins.common_sale_period.domain.*");
		$this->saleDao = SOY2DAOFactory::create("SOYShop_SalePeriodDAO");
	}
	
	function execute(){
		
		$config = SalePeriodUtil::getConfig();
		$items = $this->saleDao->getItemNearSaleEnd((int)$config["end"]);
		
		if(count($items) === 0){
			echo "セールの期限" . (int)$config["end"] . "日前の商品はありません";
			exit;
		}
		
		$users = self::getUsers();
		
		//MailLogicの呼び出し
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$mailConfig = SalePeriodUtil::getMailConfig("end");
		
		$counter = 0;
		foreach($items as $item){
			foreach($users as $user){
				if(strlen($user->getMailAddress()) === 0) continue;
				$title = self::convertMailTitle($mailConfig["title"], $item);
				$body = self::convertMailContent($mailConfig["content"], $user, $item);
				$mailLogic->sendMail($user->getMailAddress(), $title, $body);
				$counter++;
			}
		}
		
		//動作確認
		echo $counter . "人にメールを送信しました。";
		return true;
	}
	
	private function getUsers(){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$sql = "SELECT * ".
				"FROM soyshop_user ".
				"WHERE not_send = 0";
		try{
			$res = $userDao->executeQuery($sql, array());
		}catch(Exception $e){
			return array();
		}
		
		$users = array();
		foreach($res as $obj){
			if(!isset($obj["id"])) continue;
			$users[] = $userDao->getObject($obj);
		}
		
		return $users;
	}
	
	private function convertMailTitle($title, SOYShop_Item $item){
		$content = self::convertCompanyInfomation($title);
		return trim(self::convertItemInfo($content, $item));
	}
		
	/**
	 * @params String content, object SOYShop_User, object SOYShop_Item item
	 * @return String body
	 */
	private function convertMailContent($content, SOYShop_User $user, SOYShop_Item $item){
		//ユーザー情報
		$content = str_replace("#NAME#", $user->getName(), $content);
		$content = str_replace("#READING#", $user->getReading(), $content);
		$content = str_replace("#MAILADDRESS#", $user->getMailAddress(), $content);
		$content = str_replace("#BIRTH_YEAR#", $user->getBirthdayYear(), $content);
		$content = str_replace("#BIRTH_MONTH#", $user->getBirthdayMonth(), $content);
		$content = str_replace("#BIRTH_DAY#", $user->getBirthdayDay(), $content);

		$content = self::convertItemInfo($content, $item);

		//最初に改行が存在した場合は改行を削除する
		return trim(self::convertCompanyInfomation($content));
	}
	
	private function convertItemInfo($content, SOYShop_Item $item){
		try{
			$obj = $this->saleDao->getByItemId($item->getId());
		}catch(Exception $e){
			return $content;
		}
		
		$content = str_replace("#ITEM_NAME#", $item->getName(), $content);
		return str_replace("#SALE_END#", date("Y-m-d", $obj->getSalePeriodEnd()), $content);
	}
	
	private function convertCompanyInfomation($content){
		$content = str_replace("#SHOP_NAME#", $this->shopConfig->getShopName(), $content);

		$company = $this->shopConfig->getCompanyInformation();
		foreach($company as $key => $value){
			$content = str_replace(strtoupper("#COMPANY_" . $key ."#"), $value, $content);
		}
		return str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);
	}
}
?>