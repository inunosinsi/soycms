<?php
class SOYShopCartSkipBase implements SOY2PluginAction{

	/**
	 * @return bool
	 */
	function isSkip01Page(){
		return false;	
	}

	/**
	 * @param CartLogic
	 */
	function exitFromCart(CartLogic $cart){
		//
	}

	/**
	 * @return bool
	 */
	function isSkip02Page(){
		return false;	
	}

	/**
	 * @param CartLogic
	 */
	function runVirtually02Page(CartLogic $cart){}

	/**
	 * @return bool
	 */
	function isSkip03Page(){
		return false;	
	}

	/**
	 * @param CartLogic
	 */
	function runVirtually03Page(CartLogic $cart){}
}
class SOYShopCartSkipDeletageAction implements SOY2PluginDelegateAction{

	private $cart;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$pageId = $this->cart->getAttribute("page");
		if(is_null($pageId)) $pageId = "Cart01";

		preg_match('/Cart0([\d])/', $pageId, $tmp);
		if(isset($tmp[1])){
			$current = (int)$tmp[1];
			switch($current){
				case 1:
					$isSkip = $action->isSkip01Page();
					break;
				case 2:
					$isSkip = $action->isSkip02Page();
					break;
				case 3:
					$isSkip = $action->isSkip03Page();
					break;
				default:
					$isSkip = false;
			}

			if($isSkip) {
				$prevPageId = (string)$this->cart->getAttribute("prev_page");
				preg_match('/Cart0([\d])/', $prevPageId, $tmp);
				$prev = (isset($tmp[1]) && is_numeric($tmp[1])) ? (int)$tmp[1] : 0;
				if($current === 1 && $prev === 1) $prev = 0;
				$next = ($current >= $prev) ? $current+1 : $current-1;
				if(0 < $next && $next < 5){
					$this->cart->setAttribute("page", "Cart0".(string)$next);
					$this->cart->setAttribute("prev_page", "Cart0".(string)$current);

					switch($current){
						case 2:
							$action->runVirtually02Page($this->cart);
							break;
						case 3:
							$action->runVirtually03Page($this->cart);
							break;
						default:
							//
					}

					$this->cart->save();
					soyshop_redirect_cart();
				}else if($current < $prev){
					// カートから抜ける
					$this->cart->setAttribute("page", null);
					$this->cart->setAttribute("prev_page", null);
					$this->cart->save();
					$action->exitFromCart($this->cart);		
				}
			}
		}
	}

	function setCart(CartLogic $cart){
		$this->cart = $cart;
	}
}
SOYShopPlugin::registerExtension("soyshop.cart.skip","SOYShopCartSkipDeletageAction");
