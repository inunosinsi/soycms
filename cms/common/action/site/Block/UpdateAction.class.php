<?php

class UpdateAction extends SOY2Action{

	private $id;

	function setId($id){
		$this->id = $id;
	}

    function execute($request,$form,$response) {
    	$dao = SOY2DAOFactory::create("cms.BlockDAO");
		$block = $dao->getById($this->id);

		$component = $block->getBlockComponent();
		SOY2::cast($component,$form->object);
		if(!property_exists($form->object, "displayCountFrom") || strlen($form->object->displayCountFrom) === 0) $component->setDisplayCountFrom(null);
		if(!property_exists($form->object, "displayCountTo") || strlen($form->object->displayCountTo) === 0) $component->setDisplayCountTo(null);

		$block->setObject($component);

		$dao->updateObject($block);

		CMSUtil::notifyUpdate();

		$this->setAttribute("Block",$block);

		return SOY2Action::SUCCESS;
    }
}

class UpdateActionForm extends SOY2ActionForm{
	var $object;

	function setObject($object){
		$this->object = (object)$object;
	}
}
