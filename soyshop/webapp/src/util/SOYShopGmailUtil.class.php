<?php
if(!defined("CMS_COMMON")) define("CMS_COMMON", dirname(dirname(dirname(SOY2::RootDir())))."/common/");
class SOYShopGmailUtil{

	const SOYSHOP_GMAIL_API_OAUTH_CLIENT_SECRET_FILEPATH = CMS_COMMON."config/api/client_secret.json";
	const SOYSHOP_GMAIL_API_OAUTH_TOKEN_FILEPATH = CMS_COMMON."config/api/gmail_oauth.json";

}
