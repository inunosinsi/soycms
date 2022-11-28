<?php
class CommonNotepadAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "メモ詳細";
	}

	function getContent(){
		SOY2::import("module.plugins.common_notepad.page.NotepadEditorPage");
		$form = SOY2HTMLFactory::createInstance("NotepadEditorPage");
		$form->setConfigObj($this);
		$form->setDetailId($this->getDetailId());
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "common_notepad", "CommonNotepadAdminDetail");
