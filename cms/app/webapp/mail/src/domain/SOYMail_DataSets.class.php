<?php
/**
 * @table soymail_data_sets
 */
class SOYMail_DataSets {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column class_name
	 */
	private $className;

	/**
	 * @column object_data
	 */
	private $object;


	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getClassName() {
		return $this->className;
	}
	function setClassName($className) {
		$this->className = $className;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}

	public static function put($class, $obj){
		$data = new SOYMail_DataSets();
		$data->setClassName($class);
		$data->setObject(serialize($obj));

		$dao = SOY2DAOFactory::create("SOYMail_DataSetsDAO");

		try{
			$dao->clear($class);
		}catch(Exception $e){

		}

		$dao->insert($data);
	}

	public static function get($class, $onNull = false){

		try{
			$dao = SOY2DAOFactory::create("SOYMail_DataSetsDAO");
			$data = $dao->getByClass($class);

			$res = unserialize($data->getObject());
			if($res === false)throw new Exception();

			return $res;

		}catch(Exception $e){
			if($onNull !== false){
				return $onNull;
			}

			throw $e;
		}
	}

	public static function delete($class){
		$dao = SOY2DAOFactory::create("SOYMail_DataSetsDAO");
		$dao->clear($class);
	}
}
