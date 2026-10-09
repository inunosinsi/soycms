<?php
/**
 * @table OpenStreetMap
 */
class OpenStreetMap {

	/**
	 * @column entry_id
	 */
	private $entryId;
	private $latitude;
	private $longitude;
	private $zoom = 17;
	private $label;
	private $icon;

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	function getLatitude(){
		return $this->latitude;
	}
	function setLatitude($latitude){
		$this->latitude = $latitude;
	}

	function getLongitude(){
		return $this->longitude;
	}
	function setLongitude($longitude){
		$this->longitude = $longitude;
	}

	function getZoom(){
		return $this->zoom;
	}
	function setZoom($zoom){
		$this->zoom = $zoom;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}

	function getIcon(){
		return $this->icon;
	}
	function setIcon($icon){
		$this->icon = $icon;
	}
}
