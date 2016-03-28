<?php 
/**
 * @class UpdateDisplayOrder
 * @date 2008-03-24T21:08:01+09:00
 * @author SOY2ActionFactory
 */ 
class UpdateDisplayOrder extends SOY2Action{
	
	/**
	 * Actionの実行を行います。
	 */
	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form, SOY2ActionResponse &$response){
		
		$displayOrders = @$_POST["display_order"];
		
		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
		foreach($displayOrders as $labelId => $displayOrder){
			if(!strlen($displayOrder) || !is_numeric($displayOrder))$displayOrder = Label::ORDER_MAX;
						
			$displayOrder = min($displayOrder,Label::ORDER_MAX);
			$labelDAO->updateDisplayOrder($labelId,$displayOrder);
		}
		
		return SOY2Action::SUCCESS;
	}
}
?>