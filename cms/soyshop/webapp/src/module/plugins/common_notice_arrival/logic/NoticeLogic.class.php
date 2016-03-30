<?php

class NoticeLogic extends SOY2LogicBase{
	
	private $noticeDao;
	private $shopConfig;
	
	function NoticeLogic(){
		SOY2::imports("module.plugins.common_notice_arrival.domain.*");
		$this->noticeDao = SOY2DAOFactory::create("SOYShop_NoticeArrivalDAO");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	/**
	 * 入荷通知登録
	 * @params itemId, userId
	 */
	function registerNotice($itemId, $userId){
		
		$obj = new SOYShop_NoticeArrival();
		$obj->setItemId($itemId);
		$obj->setUserId($userId);
		try{
			$this->noticeDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * 入荷通知のメール送信後にデータベースの更新を行う
	 * @params itemId, userId
	 */
	function sended($itemId, $userId){
		$noticeItem = $this->getNoticeItem($itemId, $userId, SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
		$noticeItem->setSended(SOYShop_NoticeArrival::SENDED);
		try{
			$this->noticeDao->update($noticeItem);
		}catch(Exception $e){
			//
		}
	}
	
	function update(SOYShop_NoticeArrival $noticeItem, $sended = null, $checked = null){
		if(!is_null($sended) && is_numeric($sended)) $noticeItem->setSended($sended);
		if(!is_null($checked) && is_numeric($checked)) $noticeItem->setChecked($checked);
		
		try{
			$this->noticeDao->update($noticeItem);
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * @params 
	 * @return array(SOYShop_User)
	 */
	function getUsers($sended = null, $checked = null){
		return $this->noticeDao->getUsers($sended, $checked);
	}
	
	/**
	 * @params itemId
	 * @return array(SOYShop_User)
	 */
	function getUsersByItemId($itemId, $sended = null, $checked = null){
		return $this->noticeDao->getUsersByItemId($itemId, $sended, $checked);
	}
	
	function getUsersForNewsPage($sended = null, $checked = null){
		return $this->noticeDao->getUsersForNewsPage($sended, $checked);
	}
	
	function checkStock($itemId){
		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			return false;
		}
		
		return ((int)$item->getStock() === 0);
	}
	
	/**
	 * ログインしていればtrue
	 * return boolean
	 */
	function checkLogin(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getIsLoggedIn();
	}
	
	function getUserId(){
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getUserId();
	}
	
	/**
	 * お気に入りに登録済みか調べる。既に登録済みの場合はtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkRegisterNotice($itemId, $userId = null){
		$noticeItem = $this->getNoticeItem($itemId, $userId, SOYShop_NoticeArrival::NOT_SENDED);
		return (!is_null($noticeItem->getId()));
	}
	
	function checkNotice($itemId, $userId = null){
		$noticeItem = $this->getNoticeItem($itemId, $userId);
		return (!is_null($noticeItem->getSended()) && $noticeItem->getSended() == SOYShop_NoticeArrival::SENDED);
	}
	
	function convertMailTitle($title, SOYShop_Item $item){
		$title = $this->convertItemInformation($title, $item);
		$title = $this->convertCompanyInfomation($title);
		
		return trim($title);
	}
		
	/**
	 * @params String content, object SOYShop_User, object SOYShop_Item item
	 * @return String body
	 */
	function convertMailContent($content, SOYShop_User $user, SOYShop_Item $item){
		//ユーザー情報
		$content = str_replace("#NAME#", $user->getName(), $content);
		$content = str_replace("#READING#", $user->getReading(), $content);
		$content = str_replace("#MAILADDRESS#", $user->getMailAddress(), $content);
		$content = str_replace("#BIRTH_YEAR#", $user->getBirthdayYear(), $content);
		$content = str_replace("#BIRTH_MONTH#", $user->getBirthdayMonth(), $content);
		$content = str_replace("#BIRTH_DAY#", $user->getBirthdayDay(), $content);

		$content = $this->convertItemInformation($content, $item);
		$content = $this->convertCompanyInfomation($content);

		$content = str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);

		//最初に改行が存在した場合は改行を削除する
		return trim($content);
	}
	
	function convertItemInformation($content, SOYShop_Item $item){
		//商品情報
		$content = str_replace("#ITEM_CODE#", $item->getCode(), $content);
		$content = str_replace("#ITEM_NAME#", $item->getName(), $content);
		
		return $content;
	}
	
	function convertCompanyInfomation($content){
		
		if(!$this->shopConfig){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$this->shopConfig = SOYShop_ShopConfig::load();
		}
		$config = $this->shopConfig;

		$content = str_replace("#SHOP_NAME#", $config->getShopName(), $content);

		$company = $config->getCompanyInformation();
		foreach($company as $key => $value){
			$content = str_replace(strtoupper("#COMPANY_" . $key ."#"), $value, $content);
		}
		
		return $content;
	}
	
	/**
	 * @params itemId, userId
	 * @return SOYShop_NoticeItem
	 */
	function getNoticeItem($itemId, $userId = null, $sended = null, $checked = null){
		if(!$userId) $userId = $this->getUserId();
		
		try{
			$noticeItem = $this->noticeDao->getByItemIdAndUserId($itemId, $userId, $sended, $checked);
		}catch(Exception $e){
			$noticeItem = new SOYShop_NoticeArrival();
		}
		
		return $noticeItem;
	}	
}
?>