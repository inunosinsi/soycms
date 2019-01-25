<?php

function registerBlock($stmt){
	try{
		$blocks = SOY2DAOFactory::create("cms.BlockDAO")->get();
		if(!count($blocks)) return;
	}catch(Exception $e){
		return;
	}

	foreach($blocks as $block){
		$stmt->execute(array(
			":id" => $block->getId(),
			":soy_id" => $block->getSoyId(),
			":page_id" => $block->getPageId(),
			":class" => $block->getClass(),
			":object" => $block->getObject(),
		));
	}
}
