<?php

function registerEntryLabel($stmt){
	try{
		$entrylabels = SOY2DAOFactory::create("cms.EntryLabelDAO")->get();
		if(!count($entrylabels)) return;
	}catch(Exception $e){
		return;
	}

	foreach($entrylabels as $entrylabel){
		$stmt->execute(array(
			":entry_id" => $entrylabel->getEntryId(),
			":label_id" => $entrylabel->getLabelId(),
			":display_order" => (!is_null($entrylabel->getDisplayOrder())) ? $entrylabel->getDisplayOrder() : 10000000
		));
	}
}
