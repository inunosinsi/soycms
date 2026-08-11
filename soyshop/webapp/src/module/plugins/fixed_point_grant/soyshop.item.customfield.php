<?php
SOY2::import("module.plugins.fixed_point_grant.util.FixedPointGrantUtil");
class FixedPointGrantCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){

		if(isset($_POST[FixedPointGrantUtil::PLUGIN_ID])){
			$fixedPoint = soyshop_convert_number($_POST[FixedPointGrantUtil::PLUGIN_ID], 0);

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

			try{
				$attr = $dao->get($item->getId(), FixedPointGrantUtil::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($item->getId());
				$attr->setFieldId(FixedPointGrantUtil::PLUGIN_ID);
			}

			$attr->setValue($fixedPoint);

			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	function getForm(SOYShop_Item $item){

		$point = SOY2Logic::createInstance("module.plugins.fixed_point_grant.logic.FixedPointGrantLogic")->getFixedPointByItemId($item->getId());
		if(is_null($point) || (int)$point === 0){
			SOY2::import("module.plugins.fixed_point_grant.util.FixedPointGrantUtil");
			$config = FixedPointGrantUtil::getConfig();
			$point = (int)$config["fixed_point"];
		}

		$html = array();
		$html[] = "<dt>ポイント</dt>";
		$html[] = "<dd>";
		$html[] = "<input type=\"number\" name=\"" . FixedPointGrantUtil::PLUGIN_ID . "\" value=\"" . $point . "\" style=\"width:60px;ime-mode:inactive;\">&nbsp;ポイント";
		$html[] = "</dd>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		$htmlObj->addLabel("item_fixed_point", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => SOY2Logic::createInstance("module.plugins.fixed_point_grant.logic.FixedPointGrantLogic")->getFixedPointByItemId($item->getId())
		));
	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->delete($itemId, FixedPointGrantUtil::PLUGIN_ID);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "fixed_point_grant", "FixedPointGrantCustomField");
