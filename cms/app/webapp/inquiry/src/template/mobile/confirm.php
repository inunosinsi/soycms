<form method="post" id="soy_inquiry_form">

    <table class="soy_inquiry_message" id="soy_inquiry_message_confirm">
        <tr>
            <td>
                <?php $message = $config->getMessage(); echo $message["confirm"]; ?>
            </td>
        </tr>
    </table>

    <table id="inquiry_form">
<?php
$dummyFormObj = new SOYInquiry_Form();
foreach($columns as $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

    $id = $column->getId();
    $obj = $column->getColumn($dummyFormObj);
    $label = $obj->getLabel();
    $view = $obj->getView();

    if(strlen((string)$view) < 1) continue;

	//個人情報保護方針は表示しない
	if(get_class($obj) == "PrivacyPolicyColumn" && (int)$view === 1) continue;

    echo "<tr>";

    if(strlen((string)$label) > 0){
        echo "<th><strong>".$label."</strong></th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>".$view."</td>";
    }

    echo "</tr>";
}
?>
    </table>

<?php
echo $hidden_forms;
?>

        <?php if($config->getIsUseCaptcha()){ ?>
        <div id="inquiry_form_captcha">
            <img src="<?php echo $captcha_url; ?>" />

            <div>
                <input type="text" name="captcha_value" value="" /> 表示されてる画像の文字(半角英数字大文字)を入力してください。
            </div>
        </div>
        <?php } ?>

        <table>
            <tr>
                <td>
                    <input name="form" type="submit" value="戻る" />
                </td>

                <td>
                    <input name="send" type="submit" value="送信" />
                </td>
            </tr>
        </table>

</form>
