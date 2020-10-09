<?php

class TagCloudBlockEntryLogic extends SOY2LogicBase {

    function __construct(){

    }

    function search($labelId, $wordId, $count = null){
        static $entries;
        if(is_null($entries)){
            $entries = array();

			$sql = "SELECT ent.* FROM Entry ent ".
	            "JOIN EntryLabel lab ".
	            "ON ent.id = lab.entry_id ".
				"JOIN TagCloudLinking lnk ".
				"ON ent.id = lnk.entry_id ".
	            "WHERE ent.openPeriodStart <= :now ".
	            "AND ent.openPeriodEnd >= :now ".
	            "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND lab.label_id = :labelId ".
				"AND lnk.word_id = :wordId ".
				"GROUP BY ent.id ".
				"ORDER BY ent.cdate DESC ";


            if(isset($count) && $count > 0){
                $sql .= "LIMIT " . $count;

                //ページャ
                $args = self::__getArgs();
                if(isset($args[0]) && strpos($args[0], "page-") === 0){
                    $pageNumber = (int)str_replace("page-", "", $args[0]);
                    if($pageNumber > 0){
                        $offset = $count * $pageNumber;
                        $sql .= " OFFSET " . $offset;
                    }
                }
            }

			$binds = array(
				":labelId" => $labelId,
				":wordId" => $wordId,
				":now" => time()
			);

            $dao = SOY2DAOFactory::create("cms.EntryDAO");

            try{
                $results = $dao->executeQuery($sql, $binds);
            }catch(Exception $e){
                return array();
            }

            if(!count($results)) return array();

            foreach($results as $key => $row){
                if(isset($row["id"]) && (int)$row["id"]){
                    $entries[$row["id"]] = $dao->getObject($row);
                }
            }
        }

        return $entries;
    }

    function getTotal($labelId, $wordId){
        if(is_null($wordId) || is_null($labelId)) return 0;

		$sql = "SELECT COUNT(ent.id) AS TOTAL FROM Entry ent ".
			"JOIN EntryLabel lab ".
			"ON ent.id = lab.entry_id ".
			"JOIN TagCloudLinking lnk ".
			"ON ent.id = lnk.entry_id ".
			"WHERE ent.openPeriodStart <= :now ".
			"AND ent.openPeriodEnd >= :now ".
			"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
			"AND lab.label_id = :labelId ".
			"AND lnk.word_id = :wordId ";

		$binds = array(
			":labelId" => $labelId,
			":wordId" => $wordId,
			":now" => time()
		);

        $dao = new SOY2DAO();
        try{
            $res = $dao->executeQuery($sql, $binds);
        }catch(Exception $e){
            return 0;
        }

        return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
    }

    function getArgs(){
        return self::__getArgs();
    }

    private function __getArgs(){
        if(!isset($_SERVER["PATH_INFO"])) return array();
        //末尾にスラッシュがない場合はスラッシュを付ける
        $pathInfo = $_SERVER["PATH_INFO"];
        if(strrpos($pathInfo, "/") !== strlen($pathInfo) - 1){
            $pathInfo .= "/";
        }
        $argsRaw = rtrim(str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $pathInfo), "/");
        return explode("/", $argsRaw);
    }
}
