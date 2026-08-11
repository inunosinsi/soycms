<?php

function register_data_sets($stmt){
	$dao = SOY2DAOFactory::create("config.SOYShop_DataSetsDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$datas = $dao->get();
			if(!count($datas)) break;
		}catch(Exception $e){
			break;
		}

		foreach($datas as $data){
			$stmt->execute(array(
				":id" => $data->getId(),
				":class_name" => $data->getClassName(),
				":object_data" => $data->getObject()
			));
		}
	}
}
