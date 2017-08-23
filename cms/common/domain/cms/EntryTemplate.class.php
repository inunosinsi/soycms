<?php

class EntryTemplate {

	const TEMP_DEFAULT_CODE = '<?xml version="1.0" encoding="UTF-8"?><entryTemplate></entryTemplate>';
	

	private $id;
    private $name;
    private $description;
    private $templates;
    private $labelRestrictionPositive = array();
    
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
    function getTemplates() {
    	return $this->templates;
    }
    function setTemplates($templates) {
    	$this->templates = $templates;
    }
    function getStyle(){
    	return $this->templates["style"];    	
    }
    function getLabelId(){
    	return @$this->templates["labelId"];
    }
    
    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    
    function getLabelRestrictionPositive(){
    	return (array)$this->labelRestrictionPositive;
    }
    function setLabelRestrictionPositive($array){
    	$this->labelRestrictionPositive = $array;    	
    }
}
?>