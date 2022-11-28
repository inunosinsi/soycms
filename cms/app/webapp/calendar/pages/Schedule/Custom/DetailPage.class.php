<?php

class DetailPage extends WebPage{

	private $id;
	private $error=false;
	private $dao;

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["custom"]) && self::_check($_POST["custom"])){
                try{
					$old= $this->dao->getById($this->id);
				}catch(Exception $e){
					$old = new SOYCalendar_CustomItem();
				}
	
				$custom = SOY2::cast($old, (object)$_POST["custom"]);
            	if($this->id > 0){	//更新
					try{
						$this->dao->update($custom);
					}catch(Exception $e){
						//var_dump($e);
					}
				} else {	//新規
					try{
						$this->dao->insert($custom);
					}catch(Exception $e){
						//var_dump($e);
					}
				}
				
				CMSApplication::jump("Schedule.Custom");
			}
		}

		$this->error = true;
	}

	/**
	 * @param array
	 * @return bool
	 */
	private function _check(array $customs){
		return (strlen($customs["label"]) > 0 && strlen($customs["alias"]) > 0);
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : 0;
        $this->dao = SOY2DAOFactory::create("SOYCalendar_CustomItemDAO");

    	parent::__construct();

    	try{
    		$custom = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		$custom = new SOYCalendar_CustomItem();
    	}

		if(isset($_POST["custom"])){
			$custom->setLabel($_POST["custom"]["label"]);
			$custom->setAlias($_POST["custom"]["alias"]);
		}

		DisplayPlugin::toggle("error", $this->error);
    	$this->addForm("form");

    	$this->addInput("label", array(
    		"name" => "custom[label]",
    		"value" => $custom->getLabel()
    	));
    	$this->addInput("alias", array(
    		"name" => "custom[alias]",
    		"value" => $custom->getAlias()
    	));

		DisplayPlugin::toggle("register_button_area", $this->id === 0);
		DisplayPlugin::toggle("update_button_area", $this->id > 0);
    }
}
