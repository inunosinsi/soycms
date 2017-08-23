<?php
SOY2::import("domain.cms.EntryTemplate");
class TemplateLogic extends SOY2LogicBase{

	function get(){
		if(!$this->isSimpleXmlEnabled()) return array();
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->get();
	}

	function getByFileName($filename){
		if(!$this->isSimpleXmlEnabled()) return null;
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->getByFileName($filename);
	}

	function getById($id){
		if(!$this->isSimpleXmlEnabled()) return null;
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->getById($id);
	}

	function insert(EntryTemplate $data){
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->insert($data);
	}

	function update(EntryTemplate $data){
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->update($data);
	}

	function delete($filename){
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->delete($filename);
	}
	function deleteById($id){
		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		return $dao->deleteById($id);
	}

	function uploadTemplate($file){
		if(is_null($file)){
			return false;
		}
		if(!preg_match('/\.xml$/i',$file['name'])){
			return false;
		}
		if($file['type'] != 'text/xml'){
			return false;
		}

		$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
		if(!EntryTemplateDAO::test($file['tmp_name'])){
			return false;
		}

		$max = 0;
		foreach($dao->get() as $template){
			if($max < $template->getId()){
				$max = $template->getId();
			}
		}
		$filename = ($max+1).'.xml';

		if(!@move_uploaded_file($file['tmp_name'],$dao->getTemplateDirectory().'/'.$filename)){
			return false;
		}

		return true;

	}

	private function isSimpleXmlEnabled(){
		return function_exists("simplexml_load_file");
	}

}
