<?php
/**
 * @table soy_mail_log
 */
class SOYMailLog {

	public static function add($content,$more){
		try{

			$obj = new SOYMailLog();
			$obj->setContent($content);
			$obj->setMore($more);
			$obj->setTime(time());

			$dao = SOY2DAOFactory::create("SOYMailLogDAO");
			$id = $dao->insert($obj);
			
			$dao->deleteOld($id - 30);

		}catch(Exception $e){

		}
	}
	
	public static function get(){
		$dao = SOY2DAOFactory::create("SOYMailLogDAO");
		return $dao->get();
	}
	
	public static function clear(){
		$dao = SOY2DAOFactory::create("SOYMailLogDAO");
		$dao->deleteAll();
	}

	/**
	 * @id
	 */
    private $id;

    /**
     * @column log_time
     */
    private $time;

    private $content;

    private $more;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getTime() {
    	return $this->time;
    }
    function setTime($time) {
    	$this->time = $time;
    }
    function getContent() {
    	return $this->content;
    }
    function setContent($content) {
    	$this->content = $content;
    }
    function getMore() {
    	return $this->more;
    }
    function setMore($more) {
    	$this->more = $more;
    }
}

abstract class SOYMailLogDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYMailLog $bean);
	
	abstract function get();
	
	/**
	 * @query 1 = 1
	 */
	abstract function deleteAll();
	
	/**
	 * @query id < :id
	 */
	abstract function deleteOld($id);

	/**
	 * @final
	 */
	function init(){
		switch (SOYCMS_DB_TYPE) {
			case "mysql":
				$sql = "CREATE TABLE soy_mail_log(id INTEGER primary key AUTO_INCREMENT,log_time integer not null,	content TEXT,	more TEXT) TYPE = InnoDB";
				break;
			case "sqlite":
				$sql = "CREATE TABLE soy_mail_log(	id INTEGER primary key,	log_time integer not null,	content VARCHAR,	more VARCHAR)";
				break;
		}

		$this->executeUpdateQuery($sql,array());
	}

}
?>