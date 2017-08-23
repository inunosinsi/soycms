<?php

class CSSLogic extends SOY2LogicBase{

    function get(){
    	$dao = SOY2DAOFactory::create("cms.CSSDAO");
    	$this->reflesh();//ファイルの存在をチェック
    	return $dao->get();
    }
    
    function getById($id){
    	$dao = SOY2DAOFactory::create("cms.CSSDAO");
    	return $dao->getById($id);
    }
    function update($entity){
		if(!file_put_contents(get_site_directory().'/'.$entity->getFilePath(),$entity->getContents())){
			throw new Exception("ファイル書き込み失敗");	
		}
   	}
   	
   	function delete($id){
   		$dao = SOY2DAOFactory::create("cms.CSSDAO");
   		return $dao->delete($id);
   	}
   	
   	/**
   	 * ファイルの存在しないデータ列を削除する
   	 */
   	function reflesh(){
   		$dao = SOY2DAOFactory::create("cms.CSSDAO");
   		foreach($dao->get() as $key => $css){
   			if(!file_exists(get_site_directory().'/'.$css->getFilePath())){
   				$this->delete($css->getId());
   			}
   		}
   	}
}
?>