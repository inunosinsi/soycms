<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class CommonNoticeArrivalOnOutput extends SOYShopSiteOnOutputAction{

	const INSERT_INTO_THE_END_OF_HEAD = 2;
	const INSERT_INTO_THE_BEGINNING_OF_BODY = 1;
	const INSERT_INTO_THE_END_OF_BODY = 0;

	/**
	 * @return string
	 */
	function onOutput(string $html){
		if(!isset($_GET["notice"]) || $_GET["notice"] != "successed") return $html;

		//登録した時の挙動
		$script = "<script>(function(){alert('入荷通知登録を行いました。\\n詳しくはマイページの「入荷通知一覧」をご確認ください。');})();</script>";
		return $html . "\n" . $script;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "common_notice_arrival", "CommonNoticeArrivalOnOutput");
