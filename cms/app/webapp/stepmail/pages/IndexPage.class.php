<?php

class IndexPage extends WebPage{

    function doPost(){

        if(soy2_check_token() && is_null(STEPMAIL_SHOP_ID) && isset($_POST["Init"]["siteId"])){
            $shopId = strtr($_POST["Init"]["siteId"], array("." => "", "/" => "", "\\" => "", "\0" => ""));
            $txt = "<?php\ndefine(\"STEPMAIL_SHOP_ID\", \"" . trim(htmlspecialchars($shopId, ENT_QUOTES, "UTF-8")) . "\");\n?>";
            file_put_contents(dirname(dirname(__FILE__)) . "/shop_id.php", $txt);

            CMSApplication::jump("");
        }
    }

    function __construct(){
        parent::__construct();

        self::displayInitArea();
        self::displayNewsArea();
    }

    private function displayInitArea(){
        //ショップサイトとの連携が行われていない時に表示するエラー
        DisplayPlugin::toggle("no_connected_shop_site", is_null(STEPMAIL_SHOP_ID));

        if(is_null(STEPMAIL_SHOP_ID)){
            $shopList = SOY2Logic::createInstance("logic.Init.InitLogic")->getSOYShopSiteList();
        }else{
            $shopList = array();
        }

        DisplayPlugin::toggle("no_shop_site", !count($shopList));
        DisplayPlugin::toggle("show_shop_site", count($shopList));

        $this->addForm("form");

        $this->addSelect("shop_list", array(
            "name" => "Init[siteId]",
            "options" => $shopList
        ));
    }

    private function displayNewsArea(){
        DisplayPlugin::toggle("connected_shop_site", !is_null(STEPMAIL_SHOP_ID));

        if(!is_null(STEPMAIL_SHOP_ID)){
            $users = SOY2Logic::createInstance("logic.SendMailLogic")->getNoSendStepMailList();
        }else{
            $users = array();
        }

        $cnt = count($users);
        DisplayPlugin::toggle("no_reserved", !$cnt);
        DisplayPlugin::toggle("show_reserved", $cnt);

        $this->createAdd("reserved_mail_list", "_common.ReservedMailListComponent", array(
            "list" => $users
        ));
    }
}
?>
