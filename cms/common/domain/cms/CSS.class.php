<?php

/**
 * @table CSS
 */
class CSS {
	
	/**
	 * @id
	 */
	private $id;
    
    /**
     * @column filepath
     */
    private $filePath;
    
    /**
     * @no_persistent
     */
    private $contents;
    
    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getFilePath() {
    	return $this->filePath;
    }
    function setFilePath($filePath) {
    	$this->filePath = $filePath;
    }
    function getContents() {
    	if(is_null($this->contents)){
    		$this->contents = file_get_contents(get_site_directory().'/'.$this->filePath);
    	}
    	return $this->contents;
    }
    function setContents($contents) {
    	$this->contents = $contents;
    }
}
?>