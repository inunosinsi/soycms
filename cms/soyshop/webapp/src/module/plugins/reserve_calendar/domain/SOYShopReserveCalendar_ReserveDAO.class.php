<?php
SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Reserve");
/**
 * @entity SOYShopReserveCalendar_Reserve
 */
abstract class SOYShopReserveCalendar_ReserveDAO extends SOY2DAO {

    /**
     * @return id
	 * @trigger onInsert
     */
    abstract function insert(SOYShopReserveCalendar_Reserve $bean);

	abstract function update(SOYShopReserveCalendar_Reserve $bean);

    /**
     * @return object
     */
    abstract function getById($id);

	abstract function getByOrderId($orderId);

	/**
	 * @return object
	 */
	abstract function getByToken($token);

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

        $sql = "SELECT res.id, res.reserve_date, res.seat, u.id AS user_id, u.name AS user_name, u.mail_address, u.telephone_number, o.id FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "INNER JOIN soyshop_user u ".
                "ON o.user_id = u.id ".
                "WHERE res.schedule_id = :schId ";

		if($isTmp){	//仮登録モード
			$sql .= "AND o.order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " ";
			$sql .= "AND res.temp = " . SOYShopReserveCalendar_Reserve::IS_TEMP . " ";
		}else{	//本登録モード
			$sql .= "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ";
		}

		//$sql .= "GROUP BY res.schedule_id";

        try{
			return $this->executeQuery($sql, array(":schId" => $scheduleId));
        }catch(Exception $e){
			return array();
        }
    }

	function getReservedCountByScheduleId($scheduleId, $isTmp = false){	//isTmpで仮登録の予約を検索
        SOY2::import("domain.order.SOYShop_Order");

        $sql = "SELECT SUM(res.seat) AS SEAT FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "INNER JOIN soyshop_user u ".
                "ON o.user_id = u.id ".
                "WHERE res.schedule_id = :schId ";

		if($isTmp){	//仮登録モード
			$sql .= "AND o.order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " ";
			$sql .= "AND res.temp = " . SOYShopReserveCalendar_Reserve::IS_TEMP;
		}else{	//本登録モード
			$sql .= "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ";
		}

        try{
			$res = $this->executeQuery($sql, array(":schId" => $scheduleId));
        }catch(Exception $e){
			$res = array();
        }

		return (isset($res[0]["SEAT"])) ? (int)$res[0]["SEAT"] : 0;
    }

    function getReservedSchedulesByPeriod($year, $month, $isTmp = false){	//isTmpで仮登録の予約を検索
        SOY2::import("domain.order.SOYShop_Order");

        $sql = "SELECT res.schedule_id, SUM(res.seat) AS SEAT " .
                "FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_reserve_calendar_schedule sch ".
                "ON res.schedule_id = sch.id ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "WHERE sch.year = :y ".
                "AND sch.month = :m ";

		if($isTmp){	//仮登録モード
			$sql .= "AND o.order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " ";
			$sql .= "AND res.temp = " . SOYShopReserveCalendar_Reserve::IS_TEMP . " ";
		}else{	//本登録モード
			$sql .= "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ";
		}

		$sql .=	"GROUP BY res.schedule_id";

        try{
            $res = $this->executeQuery($sql, array(":y" => $year, ":m" => $month));
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
            $list[$v["schedule_id"]] = (int)$v["SEAT"];
        }

        return $list;
    }

	function getReservedScheduleListByUserIdAndPeriod($userId, $year, $month, $isTmp = false){	//isTmpで仮登録の予約を検索
        SOY2::import("domain.order.SOYShop_Order");

		//schedule_idは念の為
        $sql = "SELECT res.id, res.schedule_id, sch.label_id, sch.day " .
                "FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_reserve_calendar_schedule sch ".
                "ON res.schedule_id = sch.id ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "WHERE sch.year = :y ".
                "AND sch.month = :m ".
				"AND o.user_id = :userId ";

		if($isTmp){	//仮登録モード
			$sql .= "AND o.order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " ";
			$sql .= "AND res.temp = " . SOYShopReserveCalendar_Reserve::IS_TEMP . " ";
		}else{	//本登録モード
			$sql .= "AND o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_INTERIM . ", ".SOYShop_Order::ORDER_STATUS_CANCELED . ") ";
		}

		$sql .=	"GROUP BY res.schedule_id";

        try{
            $res = $this->executeQuery($sql, array(":userId" => $userId, ":y" => $year, ":m" => $month));
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
			$list[$v["day"]][(int)$v["id"]] = array("schedule_id" => (int)$v["schedule_id"], "label_id" => (int)$v["label_id"]);
        }

        return $list;
    }

    function checkIsUnsoldSeatByScheduleId($scheduleId){
        $now = time();
        SOY2::import("domain.shop.SOYShop_Item");
        $sql = "SELECT res.schedule_id, SUM(res.seat) AS SEAT, sch.unsold_seat " .
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

        return ((int)$res[0]["SEAT"] < (int)$res[0]["unsold_seat"]);
    }

	function checkReserveByUserId($reserveId, $userId){

		//schedule_idは念の為
        $sql = "SELECT res.id " .
                "FROM soyshop_reserve_calendar_reserve res ".
                "INNER JOIN soyshop_order o ".
                "ON res.order_id = o.id ".
                "WHERE res.id = :id ".
				"AND o.user_id = :userId ".
				"LIMIT 1";

		try{
			$res = $this->executeQuery($sql, array(":id" => $reserveId, ":userId" => $userId));
			return (isset($res[0]));
		}catch(Exception $e){
			return false;
		}
	}

	function getTokensByOrderId($orderId){
		try{
			$res = $this->executeQuery("SELECT token FROM soyshop_reserve_calendar_reserve WHERE order_id = :orderId", array(":orderId" => $orderId));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$tokens = array();
		foreach($res as $v){
			if(!isset($v["token"]) || !strlen($v["token"])) continue;
			$tokens[] = $v["token"];
		}
		return $tokens;
	}

	abstract function deleteByOrderId($orderId);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		static $i;
		if(is_null($i)) $i = 0;
		if(!isset($binds[":seat"]) || !is_numeric($binds[":seat"])) $binds[":seat"] = 1;

		for(;;){
			$i++;
			try{
				$res = $this->executeQuery("SELECT id FROM soyshop_reserve_calendar_reserve WHERE schedule_id = :scheduleId AND order_id = :orderId AND reserve_date = :reserveDate LIMIT 1;", array(":scheduleId" => $binds[":scheduleId"], ":orderId" => $binds[":orderId"], ":reserveDate" => $binds[":reserveDate"] + $i));
			}catch(Exception $e){
				var_dump($e);
				$res = array();
			}

			if(!count($res)) break;
		}
		$binds[":reserveDate"] += $i;

		return array($query, $binds);
	}
}
