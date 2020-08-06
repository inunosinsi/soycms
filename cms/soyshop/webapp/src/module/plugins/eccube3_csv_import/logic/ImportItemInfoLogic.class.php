<?php

class ImportItemInfoLogic extends ExImportLogicBase{
	
	private $labels = array("商品ID","商品名","公開ステータス(ID)","検索ワード","ショップ用メモ欄","商品画像","商品コード","在庫数","在庫無制限フラグ","通常価格","販売価格","商品カテゴリ(ID)");
	private $factors = array();

	private $type;
	private $itemDao;
	
	private $oldIds = array();
	private $pars = array();
	
	const NAME = 1;		//商品名
	const STATUS = 2;	//ステータス
	const KEYWORD = 3;	//検索ワード
	const MEMO = 4;		//備考　SOY Shopにはない
	const IMG = 5;		//詳細画像
	const CODE = 6;		//商品コード
	const STOCK = 7;	//在庫数
	const ST_F = 8;	//在庫数無制限フラグ
	const PRI_N = 9;	//通常価格
	const PRI_S = 10;	//販売価格
	const CAT = 11;		//カテゴリ
	
	function __construct(){
		$this->setCharset("Shift_JIS");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	function execute(){
					
		set_time_limit(0);
		
		$this->oldIds = SOYShop_DataSets::get("eccube_import.cat_cor_tbl", array());
		$this->pars = SOYShop_DataSets::get("eccube_import.cat_par_tbl", array());
		
		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = self::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(self::encodeFrom($lines[0]));
		
		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;
		
		unset($lines[0]);
		
		$this->itemDao->begin();
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = self::explodeLine(self::encodeFrom($line));
			
			$item = new SOYShop_Item();
						
			//商品コードですでに登録されているか調べる。商品コードがない商品はインポートしない
			 if(!isset($values[$this->factors[self::CODE]]) || !self::checkExistedItem($values[$this->factors[self::CODE]])) continue;
			$item->setCode($values[$this->factors[self::CODE]]);
						
			//$i = 0;は商品IDのためなし。$i = 1;から始める
			for($i = 1; $i <= count($this->factors); $i++){
				
				switch($i){
					case self::NAME:	//商品名
						$item->setName($values[$this->factors[$i]]);
						break;
					case self::STATUS:	//ステータス
						$isOpen = ((int)$values[$this->factors[$i]] === 1) ? SOYShop_Item::IS_OPEN : SOYShop_Item::NO_OPEN;
						$item->setIsOpen($isOpen);
						break;
					case self::KEYWORD:	//キーワード
						$item->setAttribute("keywords", $values[$this->factors[$i]]);
						break;
					case self::IMG:	//画像
						$imgs = explode(",", $values[$this->factors[$i]]);
						if(!file_exists(SOYSHOP_SITE_DIRECTORY . "files/" . $item->getCode() . "/")) mkdir(SOYSHOP_SITE_DIRECTORY . "files/" . $item->getCode() . "/");
						if(isset($imgs[0])) $item->setAttribute("image_small", "/" . SOYSHOP_ID . "/files/" . $item->getCode() . "/" . $imgs[0]);
						if(isset($imgs[1])) $item->setAttribute("image_large", "/" . SOYSHOP_ID . "/files/" . $item->getCode() . "/" . $imgs[1]);
						break;
					case self::STOCK:	//在庫数フラグ
						if(isset($values[$this->factors[$i + 1]]) && (int)$values[$this->factors[$i + 1]] === 1){
							$item->setStock(2147483647);
						//在庫数がある場合
						}else{
							$item->setStock((int)$values[$this->factors[$i]]);
						}
						$i++;
						break;
					case self::PRI_N:	//価格
						//通常価格の値がない場合がある。その時は販売価格を入れる
						if(isset($values[$this->factors[$i]])){
							$item->setAttribute("list_price", $values[$this->factors[$i]]);
						}elseif(isset($values[$this->factors[self::PRI_S]])){
							$item->setAttribute("list_price", $values[$this->factors[self::PRI_S]]);
						}
						break;
					case self::PRI_S:	//販売価格
						if(isset($values[$this->factors[$i]])) $item->setPrice($values[$this->factors[$i]]);
						break;
					case self::CAT:		//カテゴリ
						if(count($this->oldIds) > 0){
							$catId = self::getCategoryId($values[$this->factors[$i]]);
							if(isset($catId)) $item->setCategory($catId);
						}
						break;
				}
			}
			
			
			//すべての確認が終わったら、削除フラグを確認してインサートする EC CUBE3では削除
//			if(isset($values[$this->factors[self::DEL]])){
//					
//				//削除された商品の場合
//				if((int)$values[$this->factors[self::DEL]] === 1){
//					$item->setName($item->getName() . "(削除)");
//					$item->setCode($item->getCode() . "_delete_0");
//					$item->setAlias($item->getCode());
//				}
//				
//				$item->setIsDisabled($values[$this->factors[self::DEL]]);
//			}
			
			//詳細ページのページID
			$pageId = self::getDetailPageId();
			if($pageId > 0) $item->setDetailPageId($pageId);
			
			try{
				$this->itemDao->insert($item);
			}catch(Exception $e){
				//
			}

		}
		$this->itemDao->commit();
	}
	
	/**
	 * @すでに商品が登録されていないか調べる。存在していなければtrueを返す
	 * @param String code
	 * @return boolean
	 */
	private function checkExistedItem($code){
		if(strlen($code) === 0) return false;
		
		try{
			$res = $this->itemDao->executeQuery("SELECT id FROM soyshop_item WHERE item_code = :code LIMIT 1;", array(":code" => $code));
		}catch(Exception $e){
			return true;
		}
		
		return (count($res) === 0);
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
	
	private function getCategoryId($catStr){
		if(strlen($catStr) === 0) return null;
		$cats = explode(",", $catStr);
		//子カテゴリからカテゴリIDを探していく
		for($i = count($this->pars) - 1; $i >= 0; $i--){
			foreach($this->pars[$i] as $catId){
				$id = array_search($catId, $cats);
				if(isset($id) && is_numeric($id) && isset($this->oldIds[$catId])){
					return (int)$this->oldIds[$catId];
				}
			}
		}
		//ヒットしなければnull
		return null;
	}
	
	private function getDetailPageId(){
		static $id;
		if(is_null($id)){
			SOY2::import("domain.site.SOYShop_Page");
			try{
				$res = $this->itemDao->executeQuery("SELECT id FROM soyshop_page WHERE type = :type ORDER BY id ASC LIMIT 1;", array(":type" => SOYShop_Page::TYPE_DETAIL));
			}catch(Exception $e){
				$res = array();
			}
			
			$id = (isset($res[0]["id"])) ? (int)$res[0]["id"] : 0;
		}
		return $id;
	}
	
	function setType($type){
		$this->type = $type;
	}
}