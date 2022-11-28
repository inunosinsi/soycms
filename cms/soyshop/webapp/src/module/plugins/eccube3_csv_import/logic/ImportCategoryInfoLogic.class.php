<?php

class ImportCategoryInfoLogic extends ExImportLogicBase{

	private $labels = array("カテゴリID","カテゴリ名","親カテゴリID");
	private $factors = array();

	private $type;
	private $categoryDao;


	const ID = 0;
	const NAME = 1;
	const PARENT = 2;

	//EC CUBEの頃のカテゴリIDを保存しておく array("oldId" => "newId")の配列を想定
	private $oldIds = array(null);

	//各カテゴリの親子関係を保持しておく array("oldId" => "level")の配列を想定 rootを0、下に行くほど+1
	private $relatives = array(null);

	function __construct(){
		$this->setCharset("Shift_JIS");
		$this->categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
	}

	function execute(){
		set_time_limit(0);

		//一度でもインポートしたかを保持しておく
		$exeFlag = false;

		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = self::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(self::encodeFrom($lines[0]));

		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;

		unset($lines[0]);

		$array = self::getSortedArray($lines);

		$this->categoryDao->begin();
		foreach($array as $values){

			//既に登録されているか調べる
			if(!self::checkExistsCategory($values[$this->factors[self::NAME]])) continue;

			$category = new SOYShop_Category();
			$category->setName($values[$this->factors[self::NAME]]);
			$category->setAlias($values[$this->factors[self::NAME]]);
			$category->setIsOpen(SOYShop_Category::IS_OPEN);

			//親カテゴリがある場合
			if((int)$values[$this->factors[self::PARENT]] > 0 && isset($this->oldIds[$values[$this->factors[self::PARENT]]])){
				$category->setParent($this->oldIds[$values[$this->factors[self::PARENT]]]);
				$this->relatives[$values[$this->factors[self::ID]]] = (int)$this->relatives[$values[$this->factors[self::PARENT]]] + 1;
			//親カテゴリがない場合
			}else{
				$this->relatives[$values[$this->factors[self::ID]]] = 0;
			}

			try{
				//カテゴリを登録した際に、oldId(EC CUBE)とnewId(SOY Shop)を紐づける
				$this->oldIds[$values[$this->factors[self::ID]]] = $this->categoryDao->insert($category);
				$exeFlag = true;
			}catch(Exception $e){
				//
			}
		}
		$this->categoryDao->commit();

		if($exeFlag){
			//IDの対応表をデータベースに保持しておく
			SOYShop_DataSets::put("eccube_import.cat_cor_tbl", $this->oldIds);
			//親子関係の配列を変換後、データベースに保持しておく
			SOYShop_DataSets::put("eccube_import.cat_par_tbl", self::convertParentRelatives());
		}
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

	private function getSortedArray($lines){
		$array = array();		//ソート用の配列
		$sorts = array();		//ソートの条件
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = self::explodeLine(self::encodeFrom($line));

			$array[] = $values;
			$sorts[] = $values[0];
		}

		//ソート
		array_multisort($sorts, SORT_ASC, $array);

		//ソート用の配列の解放
		unset($sorts);

		return $array;
	}

	/**
	 * 既にカテゴリが存在しているか調べ、なければtrueを返す
	 * @param String カテゴリ名
	 * @return boolean
	 */
	private function checkExistsCategory($name){
		try{
			$res = $this->categoryDao->executeQuery("SELECT id FROM soyshop_category where category_name = :name LIMIT 1", array(":name" => $name));
		}catch(Exception $e){
			return true;
		}

		return (count($res) < 1);
	}

	private function convertParentRelatives(){
		$max = max($this->relatives);

		$parents = array();
		foreach($this->relatives as $id => $rel){
			if(is_null($rel)) continue;
			$parents[$rel][] = $id;
		}

		return $parents;
	}

	function setType($type){
		$this->type = $type;
	}
}
