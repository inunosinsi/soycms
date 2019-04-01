<?php

/**
 * @entity SOYShopReserveCalendar_Reserve
 */
abstract class SOYShopReserveCalendar_ReserveDAO extends SOY2DAO {

    /**
     * @return id
     */
    abstract function insert(SOYShopReserveCalendar_Reserve $bean);

	abstract function update(SOYShopReserveCalendar_Reserve $bean);

    /**
     * @return object
     */
    abstract function getById($id);

    abstract function deleteById($id);

    function getReservedSchedules($time, $limit){
        SOY2::import("domain.order.SOYShop_Order");

        $dateArray = explode("-", date("Y-n-j"));
        $sql = "SELECT sch.*, res.id AS res_id, res.reserve_date, o.user_id, u.name AS user_name, i.item_name FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_reserve_calendar_schedule sch ".
                "ON res.schedule_id = sch.id ".
                "INNER JOIN soyshop_item i ".
                "ON sch.item_id = i.id ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "INNER JOIN soyshop_user u ".
                "ON o.user_id = u.id ".
                "WHERE sch.year >= :y ".
                "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ".
                "ORDER BY res.reserve_date DESC ".
                "LIMIT " . $limit * 3;

        try{
            $res = $this->executeQuery($sql, array(":y" => $dateArray[0]));
        }catch(Exception $e){
            return array();
        }


        $list = array();
        $sort = array();
        foreach($res as $v){
            $values = array();
            $t = mktime(0, 0, 0, $v["month"], $v["day"], $v["year"]);
            if($t < time()) continue;

            $values["schedule_date"] = $t;
            $values["label_id"] = $v["label_id"];
            $values["item_id"] = $v["item_id"];
            $values["item_name"] = $v["item_name"];
            $values["user_id"] = $v["user_id"];
            $values["user_name"] = $v["user_name"];
            $values["reserve_date"] = $v["reserve_date"];

            $list[$v["res_id"]] = $values;
            $sort[$v["res_id"]] = $t;

            if(count($list) === $limit) break;
        }


        //開催日でソートしたい
        array_multisort($sort, SORT_ASC, $list);

        return $list;
    }

    function getReservedListByScheduleId($scheduleId, $isTmp = false){	//isTmpで仮登録の予約を検索
        SOY2::import("domain.order.SOYShop_Order");

        $sql = "SELECT res.id, res.reserve_date, u.id AS user_id, u.name AS user_name, u.mail_address, u.telephone_number FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "INNER JOIN soyshop_user u ".
                "ON o.user_id = u.id ".
                "WHERE res.schedule_id = :schId ";

		//仮登録モード
		if($isTmp){
			$sql .= "AND o.order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " ";
			$sql .= "AND res.temp = " . SOYShopReserveCalendar_Reserve::IS_TEMP;
		}else{	//本登録モード
			$sql .= "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ";
		}

        try{
			return $this->executeQuery($sql, array(":schId" => $scheduleId));
        }catch(Exception $e){
			return array();
        }
    }

    function getReservedSchedulesByPeriod($year, $month){
        SOY2::import("domain.order.SOYShop_Order");

        $sql = "SELECT res.schedule_id, COUNT(res.schedule_id) AS COUNT " .
                "FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_reserve_calendar_schedule sch ".
                "ON res.schedule_id = sch.id ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "WHERE sch.year = :y ".
                "AND sch.month = :m ".
                "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ".
                "GROUP BY res.schedule_id";

        try{
            $res = $this->executeQuery($sql, array(":y" => $year, ":m" => $month));
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
            $list[$v["schedule_id"]] = (int)$v["COUNT"];
        }

        return $list;
    }

    function checkIsUnsoldSeatByScheduleId($scheduleId){
        $now = time();
        SOY2::import("domain.shop.SOYShop_Item");
        $sql = "SELECT res.schedule_id, COUNT(res.schedule_id) AS COUNT, sch.unsold_seat " .
                "FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_reserve_calendar_schedule sch ".
                "ON res.schedule_id = sch.id ".
                "INNER JOIN soyshop_item item ".
                "ON sch.item_id = item.id ".
                "WHERE res.schedule_id = :schId ".
                "AND item.order_period_start < " . $now . " ".
                "AND item.order_period_end > " . $now . " ".
                "AND item.open_period_start < " . $now . " ".
                "AND item.open_period_end > " . $now . " ".
                "AND item.item_is_open " . SOYShop_Item::IS_OPEN . " ".
                "AND item.is_disabled != " . SOYShop_Item::IS_DISABLED . " ".
                "GROUP BY res.schedule_id";

        try{
            $res = $this->executeQuery($sql, array(":schId" => $scheduleId));
        }catch(Exception $e){
            return true;
        }

        if(!count($res)) return true;

        return ((int)$res[0]["COUNT"] < (int)$res[0]["unsold_seat"]);
    }
}
