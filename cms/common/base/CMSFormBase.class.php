<?php

class CMSFormBase extends HTMLForm{

    function createAdd($soyId,$className,$array = array()){

		if(isset($array['validate'])){

    		$validate = $array['validate'];
    		unset($array['validate']);

    	}

    	$component = SOY2HTMLFactory::createInstance($className,$array);
    	if(isset($validate))$component->setAttribute("class",$validate);

    	$this->add($soyId,$component);
    }

    function execute(){

    	if($this->getAttribute("id")){
    		$id = $this->getAttribute("id");
    		$this->setPermanentAttribute("id",$id);
    	}else{
    		$id = $this->getId();
    		$this->setAttribute("id",$id);
    	}


    	/*$script = 'var valid = new Validation(\''.$id.'\', {immediate : true});';

    	$this->createAdd($this->getId() . "_validation","HTMLScript",array(
    		"script" => $script
				));*/


    	parent::execute();

    }

    function getEndTag(){

    	$html = parent::getEndTag();
    	/*
    	$html.= '<script type="text/javascript"><?php echo $'.$this->getId().'["'.$this->getId().'_validation"]; ?></script>';
    	*/

    	return $html;

    }
}

?>