<?php
class MemberSpecialPriceAddPrice extends SOYShopAddPriceBase{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.member_special_price.form.SetItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("SetItemConfigPage");
		$form->setConfigObj($this);
		$form->setItemId($item->getId());
		$form->execute();
		return $form->getObject();
	}

	function doPost(SOYShop_Item $item){
		if(count($_POST["member_special_price"])){
			foreach($_POST["member_special_price"] as $hash => $price){
				$fieldId = "np_" . $hash;
				if((int)$price > 0){
					$attr = self::getAttributeObject($item->getId(), $fieldId);
					$attr->setValue($price);
					try{
						self::dao()->insert($attr);
					}catch(Exception $e){
						try{
							self::dao()->update($attr);
						}catch(Exception $e){
							var_dump($e);
						}
					}
				}else{
					try{
						self::dao()->delete($item->getId(), $fieldId);
					}catch(Exception $e){
						var_dump($e);
					}
				}
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

	private function getAttributeObject($itemId, $fieldId){
		try{
			return self::dao()->get($itemId, $fieldId);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId($fieldId);
			return $attr;
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.add.price", "member_special_price", "MemberSpecialPriceAddPrice");
