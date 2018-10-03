<?php
class Template {

	const TEMP_DEFAULT_CODE = '<?xml version="1.0" encoding="UTF-8"?><template></template>';

	private $id;
	private $name;
	private $description;
	private $active;
	private $archieveFileName;
	private $templatesDirectory;

	private $fileList = array();

	/**
	 * array
	 * key : id
	 * ex: blog_template
	 * 	id = ("entry","top","popup","archive")
	 */
	private $template;

	private $pageType;


	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function getTemplate() {
		return $this->template;
	}
	function setTemplate($template) {
		$this->template = $template;
	}

	function addTemplate($template){
		if(is_array($this->template)){
			$this->template = array_merge($this->template,$template);
		}else{
			$this->template = $template;
		}

	}

	function getTemplateById($id){
		return @$this->template[$id];
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}

	function getPageType() {
		return $this->pageType;
	}
	function setPageType($pageType) {
		$this->pageType = $pageType;
	}

	function getActive() {
		return $this->active;
	}


	function setActive($active) {
		$this->active = $active;
	}

	function isActive(){
		return (boolean)$this->active;
	}

	function getFileList() {
		return $this->fileList;
	}
	function setFileList($fileList) {
		$this->fileList = $fileList;
	}

	function getArchieveFileName() {
		return $this->archieveFileName;
	}
	function setArchieveFileName($archieveFileName) {
		$this->archieveFileName = $archieveFileName;
	}

	function getTemplateContent($id = null){

		if($id){
			$template = $this->template[$id];
			return @file_get_contents($this->getTemplatesDirectory() . $id);
		}

		$array = array();
		foreach($this->template as $key => $template){
			$array[$template["id"]] = @file_get_contents($this->getTemplatesDirectory() . $key);
		}

		return $array;
	}

	function getTemplatesDirectory() {
		return $this->templatesDirectory;
	}
	function setTemplatesDirectory($templatesDirecotry) {
		$this->templatesDirectory = $templatesDirecotry;
	}
}
