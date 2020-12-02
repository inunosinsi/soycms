<?php

class SearchBlockEntryLogic extends SOY2LogicBase {

    function __construct(){

    }

    function search($labelId, $query, $count = null){
        static $entries;
        if(is_null($entries)){
            $entries = array();

            $sql = "SELECT * FROM Entry entry ".
                 "INNER JOIN EntryLabel label ".
                 "ON entry.id = label.entry_id ".
                 "WHERE label.label_id = :label_id ".
                 "AND (entry.title LIKE :query OR entry.content LIKE :query OR entry.more LIKE :query) ".
                 "AND entry.isPublished = 1 ".
                 "AND entry.openPeriodEnd >= :now ".
                 "AND entry.openPeriodStart < :now ".
                 "ORDER BY entry.cdate desc ";

            if(isset($count) && $count > 0){
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

    function getTotal($labelId, $query){
        if(is_null($query) || !strlen($query)) return 0;
        if(is_null($labelId)) return 0;

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
