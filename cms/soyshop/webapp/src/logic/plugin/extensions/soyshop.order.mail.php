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
	private $body = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//SOYShop_Orderをセット
		$order = $this->getOrder();

		//常にorderの値が入っていなければ不便
		$action->setOrder($order);

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
			if(!isset($this->body[$displayOrder]))$this->body[$displayOrder] = array();

			$this->body[$displayOrder][$moduleId] = $res;

		}catch(Exception $e){

		}
	}

	function getBody(){
		//小さいものから並べる
		ksort($this->body);
		
		$res = "";
		
		//改行で連結
		foreach($this->body as $displayGroup){
			foreach($displayGroup as $module){
				if(strlen($module) > 0) $res .= $module."\n";
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
?>
