<?php
class SOYShopOrderMail implements SOY2PluginAction{

	/* 注文内容 SOYShop_Order */
	protected $order;

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

	}

	/**
	 * 表示順序
	 * @return number
	 */
	function getDisplayOrder(){
		return 1;
	}

	private $isUse = false;

	function setIsUse($flag){
		$this->isUse = (boolean)$flag;
	}

	function isUse(){
		return $this->isUse;
	}

	public function setOrder($order){
		$this->order = $order;
	}
}
class SOYShopOrderMailDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $_body = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//SOYShop_Orderをセット
		$order = $this->getOrder();

		//常にorderの値が入っていなければ不便
		$action->setOrder($order);
		$action->setIsUse(false);		//メール送信時に複数選択されているように見える不具合があるため、都度falseに初期化しておく

		//注文時に選択されていればisUseフラグを立てる
		if($order){
			$moduleList = $order->getModuleList();
			if(isset($moduleList[$moduleId])){
				$action->setIsUse(true);
			}
		}

		try{
			$res = $action->getMailBody($order);
			if($res === false)return;

			$displayOrder = $action->getDisplayOrder();
			if(!isset($this->_body[$displayOrder])) $this->_body[$displayOrder] = array();

			$this->_body[$displayOrder][$moduleId] = $res;

		}catch(Exception $e){

		}
	}

	function getBody(){
		$res = "";

		if(is_array($this->_body) && count($this->_body)){
			//小さいものから並べる
			ksort($this->_body);

			//改行で連結
			foreach($this->_body as $displayGroup){
				foreach($displayGroup as $module){
					if(is_string($module) && strlen($module) > 0) $res .= $module."\n";
				}
			}
		}

		return $res;
	}

	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.mail",      "SOYShopOrderMailDeletageAction");
SOYShopPlugin::registerExtension("soyshop.order.mail.user", "SOYShopOrderMailDeletageAction");
SOYShopPlugin::registerExtension("soyshop.order.mail.admin","SOYShopOrderMailDeletageAction");

//confirm
SOYShopPlugin::registerExtension("soyshop.order.mail.confirm","SOYShopOrderMailDeletageAction");
SOYShopPlugin::registerExtension("soyshop.order.mail.payment","SOYShopOrderMailDeletageAction");
SOYShopPlugin::registerExtension("soyshop.order.mail.delivery","SOYShopOrderMailDeletageAction");
