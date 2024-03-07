<?php

class DebugLogic extends SOY2LogicBase {

	function __construct(){}

	function addColumnAll(int $formId){
		if($formId === 0) return;

		self::_deleteColumnAll($formId);

		$idx = 1;
		$columnItems = SOYInquiry_Column::$columnTypes;
		foreach($columnItems as $columnType => $columnName){
			$new = new SOYInquiry_Column();
			$new->setFormId($formId);
			$new->setLabel($columnName);
			$new->setType($columnType);
			$new->setColumnId("column_".$idx++);
			$new->setRequire(1);

			$dao = self::_columnDao();

			$dao->begin();
	    	$dao->insert($new);
	    	$dao->reorderColumns($formId);
	    	$dao->commit();
		}
	}

	/**
	 * フォームをすべて削除
	 * @param int
	 * @return void
	 */
	private function _deleteColumnAll(int $formId){
		try{
			$columns = self::_columnDao()->getByFormId($formId);
		}catch(Exception $e){
			$columns = array();
		}

		if(count($columns)){
			try{
				self::_columnDao()->deleteByFormId($formId);
			}catch(Exception $e){
				//
			}
		}
	}

	private function _columnDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
		return $d;
	}
}