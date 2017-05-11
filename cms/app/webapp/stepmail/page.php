<?php
/**
 * ページ表示
 */
class StepMail_PageApplication{

    private $page;
    private $serverConfig;


    function init(){
        CMSApplication::main(array($this, "main"));
    }

    function prepare(){

    }

    function main($page){

        $this->page = $page;

        //SOY2::RootDir()の書き換え
        $oldRooDir = SOY2::RootDir();
        $oldPagDir = SOY2HTMLConfig::PageDir();
        $oldCacheDir = SOY2HTMLConfig::CacheDir();
        $oldDaoDir = SOY2DAOConfig::DaoDir();
        $oldEntityDir = SOY2DAOConfig::EntityDir();
        $oldDsn = SOY2DAOConfig::Dsn();
        $oldUser = SOY2DAOConfig::user();
        $oldPass = SOY2DAOConfig::pass();

        //設定ファイルの読み込み
        include_once(dirname(__FILE__) . "/config.php");
        $this->prepare();

        //DBの初期化を行う
        $initLogic = SOY2Logic::createInstance("logic.Init.InitLogic");
        if($initLogic->check()){
            $initLogic->init();
        }
        unset($initLogic);

        $arguments = CMSApplication::getArguments();

        //app:id="appline_api"
        $this->page->createAdd("stepmail", "StepMail_InterfaceComponent", array(
            "application" => $this,
            "page" => $page,
            "soy2prefix" => "app"
        ));

        //元に戻す
        SOY2::RootDir($oldRooDir);
        SOY2HTMLConfig::PageDir($oldPagDir);
        SOY2HTMLConfig::CacheDir($oldCacheDir);
        SOY2DAOConfig::DaoDir($oldDaoDir);
        SOY2DAOConfig::EntityDir($oldEntityDir);
        SOY2DAOConfig::Dsn($oldDsn);
        SOY2DAOConfig::user($oldUser);
        SOY2DAOConfig::pass($oldPass);

    }
}

class StepMail_InterfaceComponent extends SOYBodyComponentBase{

    private $page;
    private $application;

    function setPage($page){
        $this->page = $page;
    }

    function doPost(){
        if(soy2_check_token() && isset($_POST["StepMail"])){

            $flashSession = SOY2ActionSession::getFlashSession();
            $flashSession->setAttribute("stepmail_mailaddress_" . $this->getAttribute("app:mailid"), $_POST["StepMail"]["mail_address"]);

            $userLogic = SOY2Logic::createInstance("logic.UserLogic");
            $userId = $userLogic->getUserIdByMailAddress($_POST["StepMail"]["mail_address"]);

            //顧客名簿からユーザを取得できなかった場合は登録する
            if(is_null($userId)) $userId = $userLogic->register($_POST["StepMail"]["mail_address"]);

            //メールIDを取得
            $registLogic = SOY2Logic::createInstance("logic.RegistLogic");
            $mailId = $registLogic->getStepMailIdByMailId($this->getAttribute("app:mailid"));

            if(is_null($mailId)) self::jump("failed");

            //登録する
            if($registLogic->register($userId, $mailId)){
                $flashSession->setAttribute("stepmail_mailaddress_" . $this->getAttribute("app:mailid"), null);
                self::jump("successed");
            }
        }

        self::jump("failed");
    }

    private function jump($param){
        header("Location: ".SOY2PageController::createLink("",true) . $this->page->page->getUri() . "?" . $param);
        exit;
    }

    function execute(){
        if(count($_POST)) $this->doPost();

        $this->addModel("successed", array(
            "soy2prefix" => "cms",
            "visible" => (isset($_GET["successed"]))
        ));

        $this->addModel("failed", array(
            "soy2prefix" => "cms",
            "visible" => (isset($_GET["failed"]))
        ));

        $this->addModel("register_form_area", array(
            "soy2prefix" => "a_block",
            "visible" => (!isset($_GET["successed"]) && !isset($_GET["failed"]))
        ));

        $this->addForm("register_form", array(
            "soy2prefix" => "cms"
        ));

        $flashSession = SOY2ActionSession::getFlashSession();
        $this->addInput("mail_address", array(
            "soy2prefix" => "cms",
            "type" => "email",
            "name" => "StepMail[mail_address]",
            "value" => $flashSession->getAttribute("stepmail_mailaddress_" . $this->getAttribute("app:mailid")),
            "attr:required" => "required"
        ));

        parent::execute();
    }

    function getApplication(){
        return $this->application;
    }

    function setApplication($application){
        $this->application = $application;
    }
}

$app = new StepMail_PageApplication();
$app->init();

