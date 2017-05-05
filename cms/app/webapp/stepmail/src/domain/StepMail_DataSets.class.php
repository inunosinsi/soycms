<?php

/**
 * @table stepmail_data_sets
 */
class StepMail_DataSets{

    /**
     * @id
     */
    private $id;

    /**
     * @column class_name
     */
    private $className;
    private $value;

    function getId(){
        return $this->id;
    }
    function setId($id){
        $this->id = $id;
    }

    function getClassName(){
        return $this->className;
    }
    function setClassName($className){
        $this->className = $className;
    }

    function getValue(){
        return $this->value;
    }
    function setValue($value){
        $this->value = $value;
    }

    public static function put($key, $value){
        if(is_array($value)) $value = soy2_serialize($value);
        $obj = new StepMail_DataSets();
        $obj->setClassName($key);
        $obj->setValue($value);

        $dao = SOY2DAOFactory::create("StepMail_DataSetsDAO");
        try{
            $dao->clear($key);
        }catch(Exception $e){

        }

        $dao->insert($obj);
    }

    public static function get($key, $onNull = null){
        try{
            $value = SOY2DAOFactory::create("StepMail_DataSetsDAO")->getByClassName($key)->getValue();
        }catch(Exception $e){
            return $onNull;
        }

        if(@soy2_unserialize($value) !== false) $value = soy2_unserialize($value);
        return (isset($value)) ? $value : $onNull;
    }
}
?>
