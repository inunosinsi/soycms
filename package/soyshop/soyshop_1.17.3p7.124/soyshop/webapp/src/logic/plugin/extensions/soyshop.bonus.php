<?php
class SOYShopBonus implements SOY2PluginAction{

	private $cart;
	
	/* ボーナスについて */
	
	private $hasBonus = false;
	private $name = "";
	private $html = "";
	private $bonusContent;
	
	/**
	 * 注文処理時
	 * @param string $moduleId
	 */
	function order($moduleId){
		
	}
	
	/**
	 * ボーナス条件を判定して$hasBonus, $name, $html, $bonusContentに諸々詰める
	 */
	function confirmBonus(){
		
	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
	
	public function getHasBonus() {
		return $this->hasBonus;
	}
	public function setHasBonus($hasBonus) {
		$this->hasBonus = $hasBonus;
	}

	function getName(){
		return $this->name;
	}
	public function setName($name) {
		$this->name = $name;
	}

	public function getHtml() {
		return $this->html;
	}
	public function sethtml($html) {
		$this->html = $html;
	}

	public function getBonusContent() {
		return $this->bonusContent;
	}
	public function setBonusContent($bonusContent) {
		$this->bonusContent = $bonusContent;
	}
}
class SOYShopBonusDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "confirmBonus";
	private $cart;
	
	private $_list = array();
	private $hasBonus = false;//一つ以上ボーナスがあるか
	
	/**
	 * @param string $extetensionId
	 * @param string $moduleId
	 * @param SOY2PluginAction $action
	 */
	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.bonus needs cart information.");
		}

		$action->setCart($this->getCart());

		switch($this->mode){
			
			//ボーナスの有無、内容
			case "bonusList":
				$action->confirmBonus();
				$this->_list[$moduleId] = array(
					"hasBonus" => $action->getHasBonus(),
					"name" => $action->getName(),
					"html" => $action->gethtml(),
					"bonusContent" => $action->getBonusContent()
				);
				
				//一つ以上ボーナスがあるか
				if($action->getHasBonus()){
					$this->setHasBonus(true);
				}
				
				break;
			
			//注文処理後
			case "order":
				$action->order($moduleId);
				break;
			
		}

	}
	
	/**
	 * @return string 連結したボーナス内容HTML
	 */
	function getHtml(){
		$list = $this->_list;
		$html = array();
		
		foreach($list as $module){
			
		}
		
	}
	
	function getList(){
		return $this->_list;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}

	public function getHasBonus() {
		return $this->hasBonus;
	}
	public function setHasBonus($hasBonus) {
		$this->hasBonus = $hasBonus;
	}
}
SOYShopPlugin::registerExtension("soyshop.bonus", "SOYShopBonusDeletageAction");
?>
