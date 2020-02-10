<?php

class PayJpRecurringUtil {

	private static function _errorMessageList(){
		return array(
			"invalid_number" => "不正なカード番号",
			"invalid_cvc" => "不正なCVC",
			"invalid_expiration_date" => "不正な有効期限年、または月",
			"invalid_expiry_month" => "不正な有効期限月",
			"invalid_expiry_year" => "不正な有効期限年",
			"expired_card" => "有効期限切れ",
			"card_declined" => "カード会社によって拒否されたカード",
			"processing_error" => "決済ネットワーク上で生じたエラー",
			"missing_card" => "顧客がカードを保持していない",
			"unacceptable_brand" => "対象のカードブランドが許可されていない",
			"invalid_id" => "不正なID",
			"no_api_key" => "APIキーがセットされていない",
			"invalid_api_key" => "不正なAPIキー",
			"invalid_plan" => "不正なプラン",
			"invalid_expiry_days" => "不正な失効日数",
			"unnecessary_expiry_days" => "失効日数が不要なパラメーターである場合",
			"invalid_flexible_id" => "不正なID指定",
			"invalid_timestamp" => "不正なUnixタイムスタンプ",
			"invalid_trial_end" => "不正なトライアル終了日",
			"invalid_string_length" => "不正な文字列長",
			"invalid_country" => "不正な国名コード",
			"invalid_currency" => "不正な通貨コード",
			"invalid_address_zip" => "不正な郵便番号",
			"invalid_amount" => "不正な支払い金額",
			"invalid_plan_amount" => "不正なプラン金額",
			"invalid_card" => "不正なカード",
			"invalid_card_name" => "不正なカードホルダー名",
			"invalid_card_country" => "不正なカード請求先国名コード",
			"invalid_card_address_zip" => "不正なカード請求先住所(郵便番号)",
			"invalid_card_address_state" => "不正なカード請求先住所(都道府県)",
			"invalid_card_address_city" => "不正なカード請求先住所(市区町村)",
			"invalid_card_address_line" => "不正なカード請求先住所(番地など)",
			"invalid_customer" => "不正な顧客",
			"invalid_boolean" => "不正な論理値",
			"invalid_email" => "不正なメールアドレス",
			"no_allowed_param" => "パラメーターが許可されていない場合",
			"no_param" => "パラメーターが何もセットされていない",
			"invalid_querystring" => "不正なクエリー文字列",
			"missing_param" => "必要なパラメーターがセットされていない",
			"invalid_param_key" => "指定できない不正なパラメーターがある",
			"no_payment_method" => "支払い手段がセットされていない",
			"payment_method_duplicate" => "支払い手段が重複してセットされている",
			"payment_method_duplicate_including_customer" => "支払い手段が重複してセットされている(顧客IDを含む)",
			"failed_payment" => "指定した支払いが失敗している場合",
			"invalid_refund_amount" => "不正な返金額",
			"already_refunded" => "すでに返金済み",
			"invalid_amount_to_not_captured" => "確定されていない支払いに対して部分返金ができない",
			"refund_amount_gt_net" => "返金額が元の支払い額より大きい",
			"capture_amount_gt_net" => "支払い確定額が元の支払い額より大きい",
			"invalid_refund_reason" => "不正な返金理由",
			"already_captured" => "すでに支払いが確定済み",
			"cant_capture_refunded_charge" => "返金済みの支払いに対して支払い確定はできない",
			"cant_reauth_refunded_charge" => "返金済みの支払いに対して再認証はできない",
			"charge_expired" => "認証が失効している支払い",
			"already_exist_id" => "すでに存在しているID",
			"token_already_used" => "すでに使用済みのトークン",
			"already_have_card" => "指定した顧客がすでに保持しているカード",
			"dont_has_this_card" => "顧客が指定したカードを保持していない",
			"doesnt_have_card" => "顧客がカードを何も保持していない",
			"already_have_the_same_card" => "すでに同じカード番号、有効期限のカードを保持している",
			"invalid_interval" => "不正な課金周期",
			"invalid_trial_days" => "不正なトライアル日数",
			"invalid_billing_day" => "不正な支払い実行日",
			"billing_day_for_non_monthly_plan" => "支払い実行日は月次プランにしか指定できない",
			"exist_subscribers" => "購入者が存在するプランは削除できない",
			"already_subscribed" => "すでに定期課金済みの顧客",
			"already_canceled" => "すでにキャンセル済みの定期課金",
			"already_paused" => "すでに停止済みの定期課金",
			"subscription_worked" => "すでに稼働している定期課金",
			"cannnot_change_prorate_status" => "日割り課金の設定はプラン変更時のみ可能",
			"too_many_metadata_keys" => "metadataキーの登録上限(20)を超過している",
			"invalid_metadata_key" => "不正なmetadataキー",
			"invalid_metadata_value" => "不正なmetadataバリュー",
			"apple_pay_disabled_in_livemode" => "本番モードのApple Pay利用が許可されていない",
			"invalid_apple_pay_token" => "不正なApple Payトークン",
			"test_card_on_livemode" => "本番モードのリクエストにテストカードが使用されている",
			"not_activated_account" => "本番モードが許可されていないアカウント",
			"too_many_test_request" => "テストモードのリクエストリミットを超過している",
			"payjp_wrong" => "PAY.JPのサーバー側でエラーが発生している",
			"pg_wrong" => "決済代行会社のサーバー側でエラーが発生している",
			"not_found" => "リクエスト先が存在しないことを示す",
			"not_allowed_method" => "許可されていないHTTPメソッド",
			"other" => "その他のエラー"
		);
	}

	public static function getErrorMessageList(){
		return self::_errorMessageList();
	}

	public static function getErrorText($code){
		$errorCodes = self::_errorMessageList();

		if(!isset($errorCodes[$code])) $code = "other";
		return $errorCodes[$code];
	}

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("payment_pay_jp_recurring.config", array(
			"sandbox" => 1,
		));
	}

	public static function saveConfig($values){
		foreach(array("sandbox") as $t){
			$values[$t] = (isset($values[$t]) && $values[$t] == 1) ? 1 : 0;
		}
		SOYShop_DataSets::put("payment_pay_jp_recurring.config", $values);
	}

	public static function save($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("payment_pay_jp_recurring." . $key, $value);
	}

	public static function get($key){
		return SOY2ActionSession::getUserSession()->getAttribute("payment_pay_jp_recurring." . $key);
	}

	public static function clear($key){
		SOY2ActionSession::getUserSession()->setAttribute("payment_pay_jp_recurring." . $key, null);
	}

	public static function isTestMode(){
		$config = self::_getConfig();
		return (isset($config["sandbox"]) && $config["sandbox"] == 1);
	}
}
