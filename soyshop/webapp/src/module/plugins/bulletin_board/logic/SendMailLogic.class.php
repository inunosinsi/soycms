<?php

class SendMailLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
	}

	// トピックが新規で作成された時に運営者全員宛にメールを送信する
	function sendTopicNotice($topicId, $userId){
		//運営者全員のメールアドレスを取得
		$adminMailAddressList = self::_getAdminMailAddressList($userId);	//第一引数は通知を除外するユーザID
		if(!count($adminMailAddressList)) return;

		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($topicId, true);
		if(is_null($topic->getId())) return;

		$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());

		$title = $group->getName() . "に新しいトピックが作成されました。";
		$body = "トピック名：" . $topic->getLabel() . "\n";
		$body .= "作成者：" . soyshop_get_user_object($userId)->getDisplayName() . "\n";
		$body .= self::_getTopicDetailLink($topic->getId()) . "\n";	// URLを入れる

		// footer
		$cnf = BulletinBoardUtil::getMailConfig();
		if(isset($cnf["footer"])) $body .= $cnf["footer"];

		$body = trim($body);

		//MailLogicの呼び出し
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		foreach($adminMailAddressList as $mailAddress){
			//メールアドレスがなし or ダミーメールアドレスの場合はスルー
			if(is_numeric(strpos($mailAddress, DUMMY_MAIL_ADDRESS_DOMAIN))) continue;
			$mailLogic->sendMail($mailAddress, $title, $body);
		}
	}

	// 同一トピック内で投稿したアカウント全員に通知メールを送信する
	function sendPostNotice($postId, $userIds, $loggedInUserId){
		$post = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($postId, $loggedInUserId);
		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($post->getTopicId(), true);

		$title = $topic->getLabel() . "に新しい投稿がありました。";
		$body = "トピック名：" . $topic->getLabel() . "\n";
		$body .= "投稿者：" . soyshop_get_user_object($loggedInUserId)->getDisplayName() . "\n";
		$body .= self::_getTopicDetailLink($topic->getId()) . "#" . $post->getId() . "\n";	// URLを入れる

		// footer
		$cnf = BulletinBoardUtil::getMailConfig();
		if(isset($cnf["footer"])) $body .= $cnf["footer"];

		//MailLogicの呼び出し
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

		foreach($userIds as $userId){
			if($userId == $loggedInUserId) continue;	//投稿者とIDが一致する場合はメールを送信しない
			$mailAddress = trim(soyshop_get_user_object($userId)->getMailAddress());

			//メールアドレスがなし or ダミーメールアドレスの場合はスルー
			if(strlen($mailAddress) === 0 || is_numeric(strpos($mailAddress, DUMMY_MAIL_ADDRESS_DOMAIN))) continue;
			$mailLogic->sendMail($mailAddress, $title, $body);
		}
	}

	private function _getAdminMailAddressList($excludeUserId=null){
		//カスタムサーチフィールドから運営者のメールアドレスを取得
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT user_id FROM soyshop_user_custom_search WHERE management_side IS NOT NULL");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			if(!isset($v["user_id"]) || !is_numeric($v["user_id"])) continue;
			if($v["user_id"] == $excludeUserId) continue;
			$list[] = trim(soyshop_get_user_object($v["user_id"])->getMailAddress());
		}

		return $list;
	}

	private function _getTopicDetailLink($topicId){
		return soyshop_get_mypage_url() . "/board/topic/detail/" . $topicId;
	}
}
