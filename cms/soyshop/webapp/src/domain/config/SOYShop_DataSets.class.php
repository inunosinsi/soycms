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
			// ルート設定のときにdao.phpが読まれていないことがある
			if(!function_exists("soyshop_get_hash_table_dao")) include_once(SOY2::RootDir()."base/func/dao.php");
			try{
				$res = soyshop_get_hash_table_dao("data_sets")->executeQuery(
					"SELECT class_name, object_data ".
					"FROM soyshop_data_sets ".
					"WHERE class_name = 'soyshop.ShopConfig' ".
					"OR class_name LIKE 'config.mypage.%' ".
					"OR class_name LIKE 'config.cart.%' ".
					"OR class_name LIKE '%.mapping' "
				);
			}catch(Exception $e){
				$res = array();
			}
			
			if(count($res)){
				foreach($res as $arr){
					$classTable[] = $arr["class_name"];
					$dataTable[] = unserialize($arr["object_data"]);
				}
			}
			unset($res);
		}
		
		$idx = array_search($className, $classTable);
		if(is_numeric($idx) && isset($dataTable[$idx])){
			return $dataTable[$idx];
		}else{
			//プラグイン毎の設定で複数の値を持つものがあるので一括で取得しておく
			preg_match('/(.*)\..*/', $className, $tmp);
			if(isset($tmp[1]) && $tmp[1] !== "plugin" && $tmp[1] != "common"){
				try{
					$res = soyshop_get_hash_table_dao("data_sets")->executeQuery("SELECT class_name, object_data FROM soyshop_data_sets WHERE class_name LIKE '" . $tmp[1] . "%' AND class_name NOT IN('" . implode("','", $classTable) . "')");
				}catch(Exception $e){
					$res = array();
				}
				if(count($res)){
					foreach($res as $arr){
						if(is_numeric(array_search($arr["class_name"], $classTable))) continue;
						$classTable[] = $arr["class_name"];
						$dataTable[] = unserialize($arr["object_data"]);
					}
				}
				// 一括取得で設定データを取得できなかった場合はdataTableに$onNullを入れておく
				if(is_bool(array_search($className, $classTable))){
					$classTable[] = $className;
					$dataTable[] = $onNull;
				}
				return $dataTable[array_search($className, $classTable)];
			}

			try{
				return unserialize(soyshop_get_hash_table_dao("data_sets")->getByClass($className)->getObject());
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
