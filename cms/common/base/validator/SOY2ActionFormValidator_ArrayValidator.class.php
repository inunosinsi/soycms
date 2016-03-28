<?php
/**
 * 配列バリデータ
 */
class SOY2ActionFormValidator_ArrayValidator extends SOY2ActionFormValidator {

	var $countMax;
	var $countMin;
	
	var $type;
	var $max;
	var $min;
	
	function SOY2ActionFormValidator_ArrayValidator($obj){
		$this->countMax = @$obj->countMax;
		$this->countMin = @$obj->countMin;
		$this->type = @$obj->type;
		$this->max = @$obj->max;
		$this->min = @$obj->min;
		
	}
	
	function validate(SOY2ActionForm &$form,$propName,$value,$require){
		
		if(!is_array($value)){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"not Array",$this->getMessage("not Array")));
			return $value;
		}
		
		if($require && count($value) == 0){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"require",$this->getMessage("require")));
		}
		
		if(!is_null($this->countMax) && count($value) > $this->countMax){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"countMax",$this->getMessage("countMax")));
		}
		
		if(!is_null($this->countMin) && count($value) < $this->countMin){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),"countMin",$this->getMessage("countMin")));
		}
		
		list($result,$type) = $this->validate_data($value);
		
		if(!$result){
			$form->setError($propName,new ActionFormError(get_class($form),$propName,get_class($this),$type,$this->getMessage($type)));
		}
		
		return $value;
	}
	
	function validate_data($value){
				
		foreach($value as $key => $data){
			if(is_numeric($data)){
				if($this->type != 'number'){
					return array(false,"type");
				}
				if(!is_null($this->max) && $data > $this->max){
					return array(false,"max");
				}
				if(!is_null($this->min) && $data < $this->min){
					return array(false,"min");
				}
			}else if(is_string($data)){
				if($this->type != 'string'){
					return array(false,"type");
				}
				if(!is_null($this->max) && strlen($data) > $this->max){
					return array(false,"max");
				}
				if(!is_null($this->min) && strlen($data) < $this->min){
					return array(false,"min");
				}
			}else{
				return array(false,"type");
			}
		}
		
		return array(true,"");
		
	}
}
?>