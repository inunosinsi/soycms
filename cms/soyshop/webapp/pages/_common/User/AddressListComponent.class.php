<?php

class AddressListComponent extends HTMLList{

	function populateItem($entity, $key, $index){

		$this->addLabel("address_index", array(
			"text" => $index
		));

    	$this->addInput("send_name", array(
    		"name" => "Address[$key][name]",
    		"value" => (isset($entity["name"])) ? $entity["name"] : "",
    		"size" => 60

    	));

    	$this->addInput("send_reading", array(
    		"name" => "Address[$key][reading]",
    		"value" => (isset($entity["reading"])) ? $entity["reading"] : "",
    		"size" => 60
    	));

    	$this->addInput("send_office", array(
    		"name" => "Address[$key][office]",
    		"value" => (isset($entity["office"])) ? $entity["office"] : "",
    		"size" => 60

    	));

    	$this->addInput("send_zip_code", array(
    		"name" => "Address[$key][zipCode]",
    		"value" => (isset($entity["zipCode"])) ? $entity["zipCode"] : "",
    		"size" => 20
    	));

    	$this->addSelect("send_area", array(
    		"name" => "Address[$key][area]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => (isset($entity["area"])) ? $entity["area"] : ""
    	));

    	$this->addInput("send_address1", array(
    		"name" => "Address[$key][address1]",
    		"value" => (isset($entity["address1"])) ? $entity["address1"] : "",
    		"size" => 40
    	));

    	$this->addInput("send_address2", array(
    		"name" => "Address[$key][address2]",
    		"value" => (isset($entity["address2"])) ? $entity["address2"] : "",
    		"size" => 60
    	));

		$this->addInput("send_address3", array(
    		"name" => "Address[$key][address3]",
    		"value" => (isset($entity["address3"])) ? $entity["address3"] : "",
    		"size" => 60
    	));

    	$this->addInput("send_tel_number", array(
    		"name" => "Address[$key][telephoneNumber]",
    		"value" => (isset($entity["telephoneNumber"])) ? $entity["telephoneNumber"] : "",
    		"size" => 30
    	));
	}
}
?>
