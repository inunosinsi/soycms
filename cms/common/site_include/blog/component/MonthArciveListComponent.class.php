<?php
/**
 * 月別アーカイブを表示
 */
class MonthArciveListComponent extends HTMLList{

	var $monthPageUri;
	var $format;
	private $prevYear;
	private $secretMode = true;

	function setMonthPageUri($uri){
		$this->monthPageUri = $uri;
	}

	function setFormat($format){
		$this->format = $format;
	}

	function setSecretMode($secretMode){
		$this->secretMode = $secretMode;
	}

	protected function populateItem($count, $key, $i){

		$this->addLink("archive_link", array(
			"link" => $this->monthPageUri . date('Y/m',$key),
			"soy2prefix" => "cms"
		));

		$this->createAdd("archive_month", "DateLabel", array(
			"text" => $key,
			"soy2prefix" => "cms",
			"defaultFormat" => "Y年n月"
		));
		$this->createAdd("entry_count", "CMSLabel", array(
			"text" => $count,
			"soy2prefix" => "cms"
		));

		/** 隠しモード 初回、もしくは前年度の最後の月の前に年数を表示する **/
		if($this->secretMode){
			$showFlag = ($i === 1);

			//下記のコードで指定年の最初の月であるか？を調べる
			$y = (int)date("Y", $key);
			if(!$showFlag && $this->prevYear != $y) $showFlag = true;

			//今月の年の記録
			if($y > 1970) $this->prevYear = $y;


			$this->addModel("show_year_label", array(
				"visible" => $showFlag,
				"soy2prefix" => "cms"
			));

			// cms:id="no_first" or cms:id="not_first"
			foreach(array("no", "not") as $t){
				$this->addModel($t . "_first", array(
					"visible" => ($i > 1),
					"soy2prefix" => "cms"
				));
			}

			$this->addModel("no_year_label", array(
				"visible" => !$showFlag,
				"soy2prefix" => "cms"
			));

			$this->addLabel("year", array(
				"text" => $y,
				"soy2prefix" => "cms"
			));
		}
		/** 隠しモードここまで **/
	}
}
