<?php

function registerLabel($stmt){
	$dao = SOY2DAOFactory::create("cms.LabelDAO");
	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$labels = $dao->get();
			if(!count($labels)) break;
		}catch(Exception $e){
			break;
		}

		foreach($labels as $label){
			$stmt->execute(array(
				":id" => $label->getId(),
				":caption" => $label->getCaption(),
				":description" => $label->getDescription(),
				":alias" => $label->getAlias(),
				":icon" => $label->getIcon(),
				":display_order" => $label->getDisplayOrder(),
				":color" => $label->getColor(),
				":background_color" => $label->getBackgroundColor()
			));
		}
	}
}
