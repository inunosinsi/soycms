<?php

class TagCloudBlockItemLogic extends SOY2LogicBase {

    function __construct(){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
    }

    function search(int $wordId, int $count = 0){
        static $items;
        if(is_null($items)){
            $items = array();

			$now = time();

			$sql = "SELECT item.* FROM soyshop_item item ".
	            "JOIN soyshop_tag_cloud_linking lnk ".
				"ON item.id = lnk.item_id ".
				"WHERE item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND item.open_period_start <= " . $now . " ".
				"AND item.open_period_end >= " . $now . " ".
				"AND lnk.word_id = :wordId ".
				"GROUP BY item.id ".
				"ORDER BY item.create_date DESC ";


            if($count > 0){
                $sql .= "LIMIT " . $count;

                // //ページャ
                $args = soyshop_get_arguments();
				if(isset($args[0]) && strpos($args[0], "page-") === 0){
                    $pageNumber = (int)str_replace("page-", "", $args[0]);
                    if($pageNumber > 0){
                        $offset = $count * ($pageNumber-1);
                        $sql .= " OFFSET " . $offset;
                    }
                }
            }

			$binds = array(
				":wordId" => $wordId
			);

            $dao = soyshop_get_hash_table_dao("item");

            try{
                $results = $dao->executeQuery($sql, $binds);
            }catch(Exception $e){
                return array();
            }

            if(!count($results)) return array();

            foreach($results as $key => $row){
                if(isset($row["id"]) && (int)$row["id"]){
                    $items[$row["id"]] = soyshop_set_item_object($dao->getObject($row));
                }
            }
        }

        return $items;
    }

    function getTotal(int $wordId){
        if($wordId == 0) return 0;

		$now = time();

		$sql = "SELECT COUNT(item.id) AS TOTAL FROM soyshop_item item ".
			"JOIN soyshop_tag_cloud_linking lnk ".
			"ON item.id = lnk.item_id ".
			"WHERE item.item_is_open = 1 ".
			"AND item.is_disabled = 0 ".
			"AND item.open_period_start <= " . $now . " ".
			"AND item.open_period_end >= " . $now . " ".
			"AND lnk.word_id = :wordId ";

		$binds = array(
			":wordId" => $wordId,
		);

        $dao = new SOY2DAO();
        try{
            $res = $dao->executeQuery($sql, $binds);
        }catch(Exception $e){
			return 0;
        }

        return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
    }
}
