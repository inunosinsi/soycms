<?php

class ImportGrantLogic extends ExImportLogicBase{
	
	private $labels = array("商品コード","ポイント付与率");
	private $factors = array();
	
	private $attrDao;
	private $type;
	
	const POINT_PLUGIN_ID = "common_point_base";
	const ATTR_ID = "common_point_base";
	
	const CODE = 0;		//商品コード
	const PER = 1;		//ポイント付与率
	
	function ImportGrantLogic(){
		$this->setCharset("Shift_JIS");
		$this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		
		//ポイントプラグインのインストール
		if(!SOYShopPluginUtil::checkIsActive(self::POINT_PLUGIN_ID)){
			$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
		    $logic->prepare();
		    $logic->installModule(self::POINT_PLUGIN_ID);
		    unset($logic);
		}
	}
	
	function execute(){
		set_time_limit(0);
		
		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = self::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(self::encodeFrom($lines[0]));
		
		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;
		
		unset($lines[0]);
		
		$this->attrDao->begin();
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = self::explodeLine(self::encodeFrom($line));
			
			if((int)$values[$this->factors[self::PER]] === 0) continue;
			
			$id = self::getItemIdByCode($values[$this->factors[self::CODE]]);
			
			//IDが取得できなかった場合はスルー
			if($id < 1) continue;
			
			//すでにポイント付与設定が行われているか確認する
			if(!self::checkExistedPointConfig($id)) continue;
			
			//ポイントの設定を付与する
			$obj = new SOYShop_ItemAttribute();
			$obj->setItemId($id);
			$obj->setFieldId(self::ATTR_ID);
			$obj->setValue($values[$this->factors[self::PER]]);
			
			try{
				$this->attrDao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
		$this->attrDao->commit();
	}
	
	/**
	 * EC CUBEからダウンロードしてきたCSVにある表示されている項目の状況を調べる
	 * @param String カンマ区切りの文字列
	 */
	private function setFactors($line){
		foreach(explode(",", $line) as $n => $t){
			$i = array_search($t, $this->labels);
			if($i === false) continue;
			$this->factors[$i] = $n;
			unset($this->labels[$i]);
		}
	}
	
	/**
	 * 商品コードから商品IDを取得する
	 * @param string code
	 * @return integer id
	 */
	private function getItemIdByCode($code){
		if(count($code) === 0) return 0;
		
		try{
			$res = $this->attrDao->executeQuery("SELECT id FROM soyshop_item WHERE item_code = :code LIMIT 1;", array(":code" => $code));
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["id"])) ? (int)$res[0]["id"] : 0;
	}
	
	/**
	 * 指定の商品ですでにポイント付与率が設定されているかを確認する。設定されていなければtrueを返す
	 * @param integer id
	 * @return boolean
	 */
	private function checkExistedPointConfig($id){
		try{
			$obj = $this->attrDao->get($id, self::ATTR_ID);
		}catch(Exception $e){
			return true;
		}
		
		return (is_null($obj->getItemId()));
	}
	
	function setType($type){
		$this->type = $type;
	}
}
?>