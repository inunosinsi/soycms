<?php

class UpgradeLogic extends SOY2LogicBase{

    function execute() {
    	
    	$dao = new SOY2DAO();
    			
		$sqls = array();
		$messages = array();
		
		//0.5.0の追加分
		list($sql,$message) = $this->getX5SQL();
		$sqls[] = $sql;
		$messages[] = $message;
		
		//0.6.0の追加分
		$sqls[] = $this->deleteX6SQL();
		$sqls[] = $this->addColumnX6SQL();
		list($sql, $message) = $this->getX6SQL();
		$sqls[] = $sql;
		$messages[] = $message;
		
		//0.7.0の追加分
		$sqls[] = $this->deleteX7SQL();
		list($sql, $message) = $this->getX7SQL();
		$sqls[] = $sql;
		$message[] = $message;
		
		
		$mes = array();
		foreach($sqls as $key => $sql){
			try{
				$dao->executeQuery($sql,array());
				$mes[] = $messages[$key];
			}catch(Exception $e){

			}
		}
		
		echo "<h2>SOYGalleryのバージョンアップ</h2>";
		if(count($mes)){
			echo implode("<br />",$mes);
		}else{
			echo "データベースの変更はありません<br />";
		}
		echo "<br />";
		$link = SOY2PageController::createLink(APPLICATION_ID);
		
		echo "<a href=\"".$link."\">管理画面へ</a>";
		exit;	
    }
    
    //0.5.0の追加分
    function getX5SQL(){
    	if(SOYCMS_DB_TYPE=="mysql"){
			$sql = "alter table soygallery_image add url VARCHAR(255)";
		}else{
			$sql = "alter table soygallery_image add url VARCHAR";
		}
		$message = "イメージテーブルにURLカラムを追加しました";
    	
    	return array($sql,$message);
    }
    
    //0.6.0の追加分
	function deleteX6SQL(){
		$sql = "drop view soygallery_image_view";
		return $sql;
	}
	
	function addColumnX6SQL(){
		if(SOYCMS_DB_TYPE=="mysql"){
			$sql = "alter table soygallery_image add attributes TEXT after memo";
		}else{
			$sql = "alter table soygallery_image add attributes TEXT";
		}
		return $sql;
	}

    function getX6SQL(){
    	$sql = <<<SQL
CREATE VIEW soygallery_image_view AS
	SELECT
		i.id as id,
		i.filename as filename,
		i.url as url,
		i.sort as sort,
		i.memo as memo,
		i.attributes as attributes,
		i.is_public as is_public,
		i.create_date as create_date,
		i.update_date as update_date,
		g.id as g_id,
		g.gallery_id as gallery_id,
		g.name as name
	FROM soygallery_image i 
		LEFT JOIN soygallery_gallery g ON i.gallery_id = g.id
;
SQL;
		$message = "ImageViewを追加しました。";
    	
    	return array($sql,$message);
    }
    
    //0.7.0の追加分
    function deleteX7SQL(){
		$sql = "drop view soygallery_image_view";
		return $sql;
	}
	
	function getX7SQL(){
    	$sql = <<<SQL
CREATE VIEW soygallery_image_view AS
	SELECT
		i.id as id,
		i.filename as filename,
		i.url as url,
		i.sort as sort,
		i.memo as memo,
		i.attributes as attributes,
		i.is_public as is_public,
		i.create_date as create_date,
		i.update_date as update_date,
		g.id as g_id,
		g.gallery_id as gallery_id,
		g.name as name,
		g.config as config
	FROM soygallery_image i 
		LEFT JOIN soygallery_gallery g ON i.gallery_id = g.id
;
SQL;
		$message = "ImageViewを変更しました。";
    	
    	return array($sql,$message);
    }
}