<?php
SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Schedule");
/**
 * @entity SOYShopReserveCalendar_Schedule
 */
abstract class SOYShopReserveCalendar_ScheduleDAO extends SOY2DAO{

    /**
     * @return id
	 * @trigger onInsert
     */
    abstract function insert(SOYShopReserveCalendar_Schedule $bean);

    abstract function update(SOYShopReserveCalendar_Schedule $bean);

    /**
     * @return object
     */
    abstract function getById($id);

    function getScheduleList($itemId, $year, $month){

        SOY2::import("domain.shop.SOYShop_Item");
        $now = time();
        $sql = "SELECT sch.id, sch.label_id, sch.price, sch.day, sch.unsold_seat FROM soyshop_reserve_calendar_schedule sch ".
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

        $sql .= "ORDER BY lab.display_order ASC, lab.id ASC ";    //ラベルのソート順に並べ替える

        try{
            $res = $this->executeQuery($sql, $binds);
        }catch(Exception $e){
            return array();
        }


        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
            $list[$v["day"]][$v["id"]] = array("label_id" => (int)$v["label_id"], "price" => $v["price"], "seat" => (int)$v["unsold_seat"]);
        }

        return $list;
    }

	function getScheduleUnseatCountByItemId($itemId){

		//計算前に検索用のデータを必ず最新の状態にしておく schedule_dateを利用する
		SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Search.CustomSearchLogic")->prepare();

        $sql = "SELECT SUM(sch.unsold_seat) AS TOTAL FROM soyshop_reserve_calendar_schedule sch ".
                "INNER JOIN soyshop_item item ".
                "ON sch.item_id = item.id ".
				"INNER JOIN soyshop_reserve_calendar_schedule_search search ".
				"ON sch.id = search.schedule_id ".
                "WHERE sch.item_id = :itemId ".
				"AND search.schedule_date >= :today";
		$binds = array(
			":itemId" => $itemId,
			":today" => soyshop_shape_timestamp(time())
		);

        try{
            $res = $this->executeQuery($sql, $binds);
        }catch(Exception $e){
			return array();
        }

		if(!count($res)) return array();

		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
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
            return new SOYShopReserveCalendar_Schedule();
        }

        if(!count($res)) return new SOYShopReserveCalendar_Schedule();

        return $this->getObject($res[0]);
    }

	function getScheduleDates($scheduleId){
		$sql = "SELECT id, year, month, day FROM soyshop_reserve_calendar_schedule WHERE id > :id";
		try{
			$res = $this->executeQuery($sql, array(":id" => $scheduleId));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[$v["id"]] = mktime(0, 0, 0, $v["month"], $v["day"], $v["year"]);
		}

		return $list;
	}

	function getLowPriceAndHighPriceByItemId($itemId, $start = null, $end = null){
		$sql = "SELECT MIN(price) AS MIN, MAX(price) AS MAX from soyshop_reserve_calendar_schedule WHERE item_id = :itemId";

		//スケジュールの日時も加味
		if(
			(isset($start) && is_numeric($start)) ||
			(isset($end) && is_numeric($end))
		){
			$q = array();
			if(isset($start) && is_numeric($start)){
				$q[] = "schedule_date >= " . $start;
			}

			if(isset($end) && is_numeric($end)){
				$q[] = "schedule_date <= " . $end;
			}

			if(count($q)){
				$sql .= " AND id IN (SELECT schedule_id FROM soyshop_reserve_calendar_schedule_search WHERE " . implode(" AND ", $q) .")";
			}
		}

		try{
			$res = $this->executeQuery($sql, array(":itemId" => $itemId));
		}catch(Exception $e){
			$res = array();
		}

		if(!isset($res[0])) return array(0, 0);

		$min = (isset($res[0]["MIN"])) ? (int)$res[0]["MIN"] : 0;
		$max = (isset($res[0]["MAX"])) ? (int)$res[0]["MAX"] : 0;
		return array($min, $max);
	}

	/**
	 * @final
	 */
	function findLatestScheduleDate($year, $month){
		$sql = "SELECT id, year, month, day FROM soyshop_reserve_calendar_schedule WHERE year > " . $year . " OR (year = " . $year . " AND month >= " . $month . ") ORDER BY id ASC LIMIT 100";
		try{
			$res = $this->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array($year, $month);

		$list = array();
		$now = time();
		foreach($res as $v){
			$t = mktime(0, 0, 0, $v["month"], $v["day"], $v["year"]);
			if($t < $now) continue;
			$list[$v["id"]] = $t;
		}

		if(!count($list)) return array($year, $month);

		sort($list);
		$latest = $list[0];

		return array(date("Y", $latest), date("n", $latest));
	}

	/**
	 * @final
	 */
	function getScheduleListFromDays($itemId, $now, $days){
		//計算前に検索用のデータを必ず最新の状態にしておく schedule_dateを利用する
		SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Search.CustomSearchLogic")->prepare();

		$sql = "SELECT sch.* FROM soyshop_reserve_calendar_schedule sch ".
                "INNER JOIN soyshop_reserve_calendar_label lab ".
                "ON sch.label_id = lab.id ".
				"INNER JOIN soyshop_reserve_calendar_schedule_search search ".
				"ON sch.id = search.schedule_id ".
                "INNER JOIN soyshop_item item ".
                "ON sch.item_id = item.id ".
                "WHERE search.schedule_date >= :start ".
                "AND search.schedule_date <= :end ";
                "AND item.order_period_start < " . $now . " ".
                "AND item.order_period_end >"  . $now . " ".
                "AND item.open_period_start < " . $now . " ".
                "AND item.open_period_end > " . $now . " ".
                "AND item.item_is_open = " . SOYShop_Item::IS_OPEN . " ".
                "AND item.is_disabled != " . SOYShop_Item::IS_DISABLED . " ";
        $binds = array(":start" => $now, ":end" => strtotime("+" . ($days+1) . "day", $now));

        if(isset($itemId) && is_numeric($itemId)){
            $sql .= "AND sch.item_id = :itemId ";
            $binds[":itemId"] = $itemId;
        }

        $sql .= "ORDER BY lab.display_order ASC, lab.id ASC ";    //ラベルのソート順に並べ替える

        try{
            $res = $this->executeQuery($sql, $binds);
        }catch(Exception $e){
			return array();
        }


        if(!count($res)) return array();

        $list = array();
        foreach($res as $v){
			$timestamp = mktime(0, 0, 0, $v["month"], $v["day"], $v["year"]);
			$list[$timestamp][$v["id"]] = array("label_id" => (int)$v["label_id"], "price" => $v["price"], "seat" => (int)$v["unsold_seat"]);
        }

        return $list;
	}

    function deleteById($id){
		try{
			$this->executeQuery("DELETE FROM soyshop_reserve_calendar_schedule WHERE id = :id", array(":id" => $id));
			$this->executeQuery("DELETE FROM soyshop_reserve_calendar_schedule_search WHERE schedule_id = :id", array(":id" => $id));
		}catch(Exception $e){
			//
		}
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":price"]) || !is_numeric($binds[":price"])) $binds[":price"] = 0;
		return array($query, $binds);
	}
}
