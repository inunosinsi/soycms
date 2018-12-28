<?php
SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
class CommonTicketBaseCustomField extends SOYShopItemCustomFieldBase{

	private $itemAttributeDao;

	function doPost(SOYShop_Item $item){

		if(isset($_POST[TicketBaseUtil::PLUGIN_ID])){
			$count = soyshop_convert_number($_POST[TicketBaseUtil::PLUGIN_ID], 0);

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			try{
				$attr = $dao->get($item->getId(),TicketBaseUtil::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($item->getId());
				$attr->setFieldId(TicketBaseUtil::PLUGIN_ID);
			}

			$attr->setValue($count);

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

		$config = TicketBaseUtil::getConfig();

		$html = array();
		$html[] = "<dt>" . $config["label"] . "</dt>";
		$html[] = "<dd>";
		$html[] = "<input type=\"number\" name=\"" . TicketBaseUtil::PLUGIN_ID . "\" value=\"" . SOY2Logic::createInstance("module.plugins.common_ticket_base.logic.TicketBaseLogic")->getTicketCountByItemId($item->getId()) . "\" style=\"width:60px;ime-mode:inactive;\">&nbsp;" . $config["unit"];
		$html[] = "</dd>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//何もしない
	}

	function onDelete($id){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->delete($id, TicketBaseUtil::PLUGIN_ID);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_ticket_base", "CommonTicketBaseCustomField");
