<?php

class IndexPage extends WebPage{

    private $mailId;
    private $stepId;

    function doPost(){

        if(soy2_check_token() && isset($_POST["Step"])){

            $_POST["Step"]["daysAfter"] = stepmail_convert_number($_POST["Step"]["daysAfter"], 1);

            $redirect = "Mail.Step." . $this->stepId . "?mail_id=" . $_GET["mail_id"];
            if(isset($_GET["first"])) $redirect .= "&first";

            //更新の場合
            if(isset($this->stepId)){
                $old = self::getStepObj();
                $step = SOY2::cast($old, $_POST["Step"]);
                $step->setMailId((int)$_GET["mail_id"]);
                try{
                    self::stepDao()->update($step);
                    StepMailUtil::deleteBackup($_POST["login_id"]); //バックアップを削除する
                    CMSApplication::jump($redirect . "&updated");
                }catch(Exception $e){

                }
            //作成の場合
            }else{
                $dao = self::stepDao();
                $step = SOY2::cast("StepMail_Step", $_POST["Step"]);
                $step->setMailId((int)$_GET["mail_id"]);
                try{
                    $this->stepId = $dao->insert($step);
                    StepMailUtil::deleteBackup($_POST["login_id"]); //バックアップを削除する
                    CMSApplication::jump("Mail.Step." . $this->stepId . "?mail_id=" . $_GET["mail_id"] . "&created");
                }catch(Exception $e){

                }
            }
        }

        CMSApplication::jump($redirect . "&failed");
    }


    function __construct($args){
        if(!isset($_GET["mail_id"])) CMSApplication::jump("Mail");
        $this->stepId = (isset($args[0])) ? (int)$args[0] : null;

        SOY2::import("util.StepMailUtil");

        WebPage::__construct();

        DisplayPlugin::toggle("updated", isset($_GET["updated"]));
        DisplayPlugin::toggle("created", isset($_GET["created"]));
        DisplayPlugin::toggle("failed", isset($_GET["failed"]));

        $this->addLink("every_step_mail_link", array(
            "link" => CMSApplication::createLink("Mail.Detail." . $_GET["mail_id"])
        ));

        $step = self::getStepObj();

        $this->addForm("form");

        $this->addInput("mail_title", array(
            "name" => "Step[title]",
            "value" => $step->getTitle()
        ));

        $this->addInput("mail_overview", array(
            "name" => "Step[overview]",
            "value" => $step->getOverview()
        ));

        $count = self::countStep();
        $this->addModel("no_step", array(
            "visible" => ($count == 0 || isset($_GET["first"]))
        ));

        $this->addModel("is_step", array(
            "visible" => ($count > 0 && !isset($_GET["first"]))
        ));

        $this->addInput("days_after", array(
            "name" => "Step[daysAfter]",
            "value" => (!is_null($step->getDaysAfter())) ? (int)$step->getDaysAfter() : 1
        ));

        $this->addTextArea("mail_content", array(
            "name" => "Step[content]",
            "value" => $step->getContent()
        ));

        $userSession = SOY2ActionSession::getUserSession();
        $loginId = $userSession->getAttribute("loginid");
        $isBackup = StepMailUtil::checkBackupFile($loginId);
        $this->addModel("auto_save_entry_message", array(
            "attr:id" => "auto_save_entry_message",
            "style" => ($isBackup) ? "display:none;" : "display:inline;"
        ));

        $this->addModel("restoration_area", array(
            "attr:id" => "restoration_area",
            "style" => ($isBackup) ? "display:inline;" : "display:none;"
        ));

        $this->addModel("submit_button", array(
            "type" => "submit",
            "attr:value" => (isset($this->stepId)) ? "更新" : "作成"
        ));

        $this->addInput("current_login_id", array(
            "name" => "login_id",
            "value" => $loginId
        ));

        $this->addInput("auto_save_action", array(
            "value" => CMSApplication::createLink("Mail.Step.Save")
        ));

        $this->addInput("restore_action", array(
            "value" => CMSApplication::createLink("Mail.Step.Load")
        ));

        $this->addModel("autosave_js", array(
            "src" => SOY2PageController::createRelativeLink("./webapp/" . APPLICATION_ID . "/js/autosave.js")
        ));
    }

    private function countStep(){
        try{
            return self::stepDao()->countStepByMailId($_GET["mail_id"]);
        }catch(Exception $e){
            return 0;
        }
    }

    private function getStepObj(){
        try{
            return self::stepDao()->getById($this->stepId);
        }catch(Exception $e){
            return new StepMail_Step();
        }
    }

    private function stepDao(){
        static $dao;
        if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_StepDAO");
        return $dao;
    }
}
?>
