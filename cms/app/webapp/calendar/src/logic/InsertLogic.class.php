<?php
SOY2::Import("domain.SOYCalendar_ItemDAO");
class InsertLogic extends SOY2LogicBase{
	
	/**
	 * @param array, int<timestamp>, int, array<曜日の配列>
	 */
	function insertSchedules(array $items, int $endDate, int $cnt, array $ws){
		
		$_arr = array();
	 	for($i = 0; $i < $cnt; $i++){
	 		$month = $items["month"] + $i;
	 		$year = $items["year"];
	 		if($month > 12){
	 			$month = $month-12;
	 			$year++;
	 		}
				 		
	 		$lastDate = soycalendar_get_last_date_timestamp($year, $month);
				 		
	 		//開始日の最終日と終了日を比較する。最終日よりも終了日が大きい場合はその月の最後までインサートする。
	 		$lastDay = ($endDate > $lastDate) ? $items["day"] : date("j", $endDate);
						
			//2週目以降のループは0からスタートする。
			$day = ($i == 0) ? $items["day"] : 1;
						
			for($j = $day; $j <= $lastDay; $j++){
				$schedule = soycalendar_get_schedule($year, $month, $j);	
				if($schedule > $lastDate) continue;
				
				//曜日のチェック
				if(is_bool(array_search(date("D", $schedule), $ws))) continue;

				$values = $items;
				$values["scheduleDate"] = $schedule;

				$_arr[] = $values;
			}
	 	}

		// 一括で登録する
		if(count($_arr)){
			$ids = array();

			$pdo = new PDO(SOY2DAOConfig::dsn(), SOY2DAOConfig::user(), SOY2DAOConfig::pass());
			
			$pdo->beginTransaction();
			$stmt = $pdo->prepare("INSERT INTO soycalendar_item(schedule_date, title_id, start, end, create_date, update_date) VALUES(:schedule_date, :title_id, :start, :end, :create_date, :update_date)");
			foreach($_arr as $_v){
				try{
					$stmt->execute(array(
						":schedule_date" => $_v["scheduleDate"], 
						":title_id" => (int)$_v["titleId"], 
						":start" => $_v["start"],
						":end" => $_v["end"],
						":create_date" => time(),
						":update_date" => time()
					));
					$ids[] = $pdo->lastInsertId();
				}catch(Exception $e){
					//
				}
			}
			$pdo->commit();

			if(count($ids) && isset($_POST["Custom"]) && is_array($_POST["Custom"]) && count($_POST["Custom"])){
				$pdo->beginTransaction();
				$stmt = $pdo->prepare("INSERT INTO soycalendar_custom_item_checked(item_id, custom_id) VALUES(:item_id, :custom_id)");
				foreach($ids as $id){
					foreach($_POST["Custom"] as $cusId){
						try{
							$stmt->execute(array(
								":item_id" => $id, 
								":custom_id" => $cusId
							));
						}catch(Exception $e){
							//
						}
					}
				}
				$pdo->commit();
			}
		}
		
	}

	/**
	 * @param array, int<timestamp>
	 */
	function insertComplete(array $items, int $schedule){
		self::_insertComplete($items, $schedule);
	}

	/**
	 * @param array, int<timestamp>
	 */
	private function _insertComplete(array $posts, int $schedule){
		$posts["scheduleDate"] = $schedule;
		self::save($posts);
	}

	/**
	 * insertとupdateを統合する
	 * @param array, id
	 * @return bool
	 */
	function save(array $posts, int $id=0){
		$dao = self::_dao();
		$item = new SOYCalendar_Item();
		if($id > 0){
			try{
				$item = self::_dao()->getById($id);
			}catch(Exception $e){
				//
			}
		}

		$item = SOY2::cast($item, $posts);
		$itemId = $item->getId();

		$dao->begin();
		if(is_numeric($item->getId()) && $item->getId() > 0){	//更新
			try{
				self::_dao()->update($item);
				$itemId = $item->getId();
			}catch(Exception $e){
				//
			}
		}else{
			try{
				$itemId = self::_dao()->insert($item);
				$item->setId($itemId);
			}catch(Exception $e){
				//
			}
		}

		if(!is_numeric($itemId)){
			$dao->commit();
			return false;
		}

		/** カスタム項目 */
		try{
			self::_customDao()->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}

		if(isset($_POST["Custom"]) && is_array($_POST["Custom"]) && count($_POST["Custom"])){
			foreach($_POST["Custom"] as $cusId){
				$obj = new SOYCalendar_CustomItem_Checked();
				$obj->setItemId($itemId);
				$obj->setCustomId($cusId);
				try{
					self::_customDao()->insert($obj);
				}catch(Exception $e){
					//
				}
			}
		}

		$dao->commit();
		return true;
	}

	private function _dao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}

	private function _customDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_CustomItem_CheckedDAO");
		return $d;
	}
}
