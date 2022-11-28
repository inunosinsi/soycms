<?php
class SOYShopAddMailAddress implements SOY2PluginAction{

    /**
     * 追加するメールアドレスの配列を取得
     * @return array
     */
    function getMailAddress(SOYShop_Order $order, bool $orderFlag=false){
		return array();
    }
}

class SOYShopAddMailAddressDeletageAction implements SOY2PluginDelegateAction{

    private $order;
    private $orderFlag;
    private $_mailaddress;

    function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($this->order instanceof SOYShop_Order){
        	$this->_mailaddress = $action->getMailAddress($this->order, $this->orderFlag);
		}
    }

    function getMailAddress(){
        return $this->_mailaddress;
    }
    function getOrder(){
        return $this->order;
    }
    function setOrder(SOYShop_Order $order) {
        $this->order = $order;
    }
    function setOrderFlag(bool $orderFlag){
        $this->orderFlag = $orderFlag;
    }
}
SOYShopPlugin::registerExtension("soyshop.add.mailaddress","SOYShopAddMailAddressDeletageAction");
