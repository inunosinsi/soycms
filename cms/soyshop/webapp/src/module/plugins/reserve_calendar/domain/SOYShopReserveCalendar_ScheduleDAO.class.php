<?php

/**
 * @entity SOYShopReserveCalendar_Schedule
 */
abstract class SOYShopReserveCalendar_ScheduleDAO extends SOY2DAO{

    /**
     * @return id
     */
    abstract function insert(SOYShopReserveCalendar_Schedule $bean);

    /**
     * @return object
     */
    abstract function getById($id);

    function getScheduleList($itemId, $year, $month){

        SOY2::import("domain.shop.SOYShop_Item");
        $now = time();
        $sql = "SELECT sch.id, sch.label_id, sch.day, sch.unsold_seat FROM soyshop_reserve_calendar_schedule sch ".
                "INNER JOIN soyshop_reserve_calendar_label lab ".
                "ON sch.label_id = lab.id ".
                "INNER JOIN soyshop_item item ".
                "ON sch.item_id = item.id ".
                "WHERE sch.year = :y ".
                "AND sch.month = :m ".
                "AND item.order_period_start < " . $now . " ".
                "AND item.order_period_end >"  . $now . " ".
                "AND item.open_period_start < " . $now . " ".
                "AND item.open_period_end > " . $now . " ".
                "AND item.item_is_open = " . SOYShop_Item::IS_OPEN . " ".
                "AND item.is_disabled != " . SOYShop_Item::IS_DISABLED . " ";

        $binds = array(":y" => $year, ":m" => $month);

        if(isset($itemId) && is_numeric($itemId)){
            $sql .= "AND sch.item_id = :itemId ";
            $binds[":itemId"] = $itemId;
        }

        $sql .= "ORDER BY lab.display_order ASC ";    //ラベルのソート順に並べ替える

        try{
            $res = $this->executeQuery($sql, $binds);
        }catch(Exception $e){
            return array();
        }


        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
            $list[$v["day"]][$v["id"]] = array("label_id" => (int)$v["label_id"], "seat" => (int)$v["unsold_seat"]);
        }

        return $list;
    }

    function getScheduleByReserveId($reserveId){
        $sql = "SELECT sch.* FROM soyshop_reserve_calendar_schedule sch ".
                "INNER JOIN soyshop_reserve_calendar_reserve res ".
                "ON sch.id = res.schedule_id ".
                "WHERE res.id = :id ".
                "LIMIT 1";

        try{
            $res = $this->executeQuery($sql, array(":id" => $reserveId));
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return new SOYShopReserveCalendar_Schedule();

        return $this->getObject($res[0]);
    }

    abstract function deleteById($id);
}
