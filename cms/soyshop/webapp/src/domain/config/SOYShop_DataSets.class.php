<?php
/**
 * @table soyshop_data_sets
 */
class SOYShop_DataSets {

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

	public static function put(string $class, $obj){
		$data = new SOYShop_DataSets();
		$data->setClassName($class);
		$data->setObject(serialize($obj));

		$dao = soyshop_get_hash_table_dao("data_sets");

		try{
			$dao->clear($class);
		}catch(Exception $e){
			//
		}

		$dao->insert($data);
	}

	public static function get(string $className, $onNull = false){
		static $dataTable, $classTable;
		$len = 6;	//ハッシュの文字数
		if(is_null($dataTable)){	//よく使うものだけ事前に取得しておく
			$dataTable = array();
			$classTable = array();
			try{
				$res = soyshop_get_hash_table_dao("data_sets")->executeQuery(
					"SELECT class_name, object_data ".
					"FROM soyshop_data_sets ".
					"WHERE class_name NOT LIKE 'mail%' ".
					"AND class_name NOT LIKE '%attributes' ".
					"AND class_name NOT LIKE 'item%' ".
					"AND class_name NOT LIKE '%.config'"
				);
			}catch(Exception $e){
				$res = array();
			}
			
			if(count($res)){
				foreach($res as $arr){
					$classTable[] = $arr["class_name"];
					$dataTable[] = soy2_unserialize($arr["object_data"]);
				}
			}
			unset($res);
		}

		$idx = array_search($className, $classTable);
		if(is_numeric($idx) && isset($dataTable[$idx])){
			return $dataTable[$idx];
		}else{
			try{
				return soy2_unserialize(soyshop_get_hash_table_dao("data_sets")->getByClass($className)->getObject());
			}catch(Exception $e){
				if($onNull !== false) return $onNull;
				throw $e;
			}
		}
	}

	public static function delete(string $class){
		soyshop_get_hash_table_dao("data_sets")->clear($class);
	}
}
