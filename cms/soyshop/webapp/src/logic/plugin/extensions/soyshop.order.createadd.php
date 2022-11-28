<?php
/**
 * 注文を表示するページを、プラグインごとに独自に拡張する
 */
class SOYShopOrderCreateadd implements SOY2PluginAction{

	/* 注文内容 SOYShop_Order */
	private $order;
	private $orders;
	private $page;
	
	function createadd(){}

	public function getOrder() {
		return $this->order;
	}
	public function setOrder($order) {
		$this->order = $order;
	}

	public function getOrders() {
		return $this->orders;
	}
	public function setOrders($orders) {
		$this->orders = $orders;
	}

	public function getPage() {
		return $this->page;
	}
	public function setPage($page) {
		$this->page = $page;
	}
}
class SOYShopOrderCreateaddDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $orders = array();
	private $page;
	
	/**
	 * @param string $extetensionId soyshop.order.createadd
	 * @param string $moduleId
	 * @param SOYShopOrderCreateadd $action 
	 */
	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->setOrder($this->getOrder());
		$action->setOrders($this->getOrders());
		$action->setPage($this->getPage());
		$action->createadd();
		
	}

	public function getOrder() {
		return $this->order;
	}
	public function setOrder($order) {
		$this->order = $order;
	}

	public function getOrders() {
		return $this->orders;
	}
	public function setOrders($orders) {
		$this->orders = $orders;
	}

	public function getPage() {
		return $this->page;
	}
	public function setPage($page) {
		$this->page = $page;
	}

}
SOYShopPlugin::registerExtension("soyshop.order.createadd", "SOYShopOrderCreateaddDeletageAction");
?>