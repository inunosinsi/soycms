<?php
class MemberSpecialPriceAddPrice extends SOYShopAddPriceBase{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.member_special_price.form.SetItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("SetItemConfigPage");
		$form->setItemId($item->getId());
		$form->execute();
		return $form->getObject();
	}

	function doPost(SOYShop_Item $item){
		if(count($_POST["member_special_price"])){
			foreach($_POST["member_special_price"] as $hash => $price){
				$fieldId = "np_" . $hash;
				$attr = soyshop_get_item_attribute_object($item->getId(), $fieldId);
				if((int)$price > 0){
					$attr->setValue($price);
				}else{
					$attr->setValue(null);
				}
				soyshop_save_item_attribute_object($attr);
			}
		}
	}

	//価格の確認
	function confirm(SOYShop_Item $item){
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		$config = MemberSpecialPriceUtil::getConfig();
		if(!is_array($config) || !count($config)) return array();

		$logic = SOY2Logic::createInstance("module.plugins.member_special_price.logic.SpecialPriceLogic");

		$list = array();
		foreach($config as $conf){
			$hash = (isset($conf["hash"])) ? $conf["hash"] : "none";
			$price = $logic->getPriceByItemIdAndHash($item->getId(), $hash);
			if(isset($price) && is_numeric($price)) $list[] = array("label" => $conf["label"], "price" => $price);

			//セール
			$price = $logic->getPriceByItemIdAndHash($item->getId(), $hash, true);
			if(isset($price) && is_numeric($price)) $list[] = array("label" => $conf["label"] . "セール", "price" => $price);
		}
		return $list;
	}
}
SOYShopPlugin::extension("soyshop.add.price", "member_special_price", "MemberSpecialPriceAddPrice");
