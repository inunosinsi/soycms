<?php

class SearchBlockEntryLogic extends SOY2LogicBase {

    function __construct(){}

    /**
     * @param int, string, int
     * @return array
     */
    function search(int $labelId, string $query, int $count=0){
        static $entries;
        if(is_null($entries)){
            $entries = array();

            $sql = "SELECT DISTINCT entry.id, entry.* FROM Entry entry ".
                 "INNER JOIN EntryLabel label ".
                 "ON entry.id = label.entry_id ".
                 "WHERE label.label_id = :label_id ".
                 "AND (entry.title LIKE :query OR entry.content LIKE :query OR entry.more LIKE :query) ".
                 "AND entry.isPublished = 1 ".
                 "AND entry.openPeriodEnd >= :now ".
                 "AND entry.openPeriodStart < :now ".
                 "ORDER BY entry.cdate desc ";

            if($count > 0){
                $sql .= "LIMIT " . $count;

                //ページャ
                $args = self::__getArgs();
                if(isset($args[0]) && strpos($args[0], "page-") === 0){
                    $pageNumber = (int)str_replace("page-", "", $args[0]);
                    if($pageNumber > 0) $sql .= " OFFSET " . (($count * $pageNumber) - 1);
                }
            }

            $binds = array(
                ":label_id" => $labelId,
                ":query" => "%" . $query . "%",
                ":now" => time()
            );

            $dao = soycms_get_hash_table_dao("entry");

            try{
                $results = $dao->executeQuery($sql, $binds);
            }catch(Exception $e){
                return array();
            }

            if(!count($results)) return array();

            foreach($results as $key => $row){
                if(isset($row["id"]) && (int)$row["id"]){
                    $entries[$row["id"]] = soycms_set_entry_object($dao->getObject($row));
                }
            }
        }

        return $entries;
    }

    /**
     * @param int, string
     * @return int
     */
    function getTotal(int $labelId, string $query=""){
        if($labelId <= 0 || !strlen($query)) return 0;
        
        $sql = "SELECT COUNT(*) AS TOTAL FROM Entry entry ".
             "INNER JOIN EntryLabel label ".
             "ON entry.id = label.entry_id ".
             "WHERE label.label_id = :label_id ".
             "AND (entry.title LIKE :query OR entry.content LIKE :query OR entry.more LIKE :query) ".
             "AND entry.isPublished = 1 ".
             "AND entry.openPeriodEnd >= :now ".
             "AND entry.openPeriodStart < :now";

        $binds = array(
            ":label_id" => $labelId,
            ":query" => "%" . $query . "%",
            ":now" => time()
        );

        try{
            $res = soycms_get_hash_table_dao("entry")->executeQuery($sql, $binds);
        }catch(Exception $e){
            return 0;
        }

        return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
    }

    function getArgs(){
        return self::__getArgs();
    }

    /**
     * @return array
     */
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
