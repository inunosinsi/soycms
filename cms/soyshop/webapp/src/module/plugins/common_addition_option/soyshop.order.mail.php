<?php
class AdditionOptionMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
							
		$res = array();
			
		$dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		try{
			$itemOrders = $dao->getByOrderId($order->getId());
		}catch(Exception $e){
			$itemOrders = array();
		}
		
		if(count($itemOrders) > 0){
			$res[] = "";
			$res[] = "加算対象商品";
			$res[] = "-----------------------------------------";
			foreach($itemOrders as $item){
				
				//加算フラグ
				$isAddition = ($item->getIsAddition()==1)?true:false;
				
				//加算対象商品だった場合
				if($isAddition){
					$res[] = $item->getItemName() . ":";
					
					//加算項目と金額を取得
					$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
					try{
						$array = $dao->getByItemId($item->getItemId());
					}catch(Exception $e){
						echo $e->getPDOExceptionMessage();
					}
					
					$name = (isset($array["addition_option_name"]))?$array["addition_option_name"]->getValue():"加算";
					$price = (isset($array["addition_option_price"]))?$array["addition_option_price"]->getValue():0;
					$res[] = $name . "：" . $price  . "円" . "*" . $item->getItemCount() . "個";
					
					$res[] = "";
				}	
			}
			$res[] = "";
		}
		return implode("\n", $res);
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user","common_addition_option","AdditionOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm","common_addition_option","AdditionOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.admin","common_addition_option","AdditionOptionMail");
?>