<form method="post" enctype="multipart/form-data">
    <table class="soy_inquiry_message" id="soy_inquiry_message_information">
        <tr>
            <td>
                <?php $message = $config->getMessage(); echo $message["information"]; ?>
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
    $annotation = $obj->getAnnotation();

    if($column->getRequire()){
        echo "<tr class=\"require\">";
    }else{
        echo "<tr>";
    }

    if(strlen($label)>0){
        echo "<th>";
        echo $label;
        if($column->getRequire()){
            echo "(必須)";
        }
        echo "</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>";
        echo $obj->getForm();
        if(isset($errors[$id])){
            echo "&nbsp;";
            echo "<span class=\"error_message\">";
            echo $errors[$id];
            echo "</span>";
        }
        echo "</td>";
    }else{
        echo "<td colspan=\"2\">";
        echo $obj->getForm();
        if(isset($errors[$id])){
            echo "&nbsp;";
            echo "<span class=\"error_message\">";
            echo $errors[$id];
            echo "</span>";
        }
        echo "</td>";
    }
    if(isset($annotation)){
        echo "</tr>";
        echo "<tr>";
        echo "<td>";
        echo $annotation;
        echo "</td>";
    }

    echo "</tr>";
}
?>
    </table>

    <table>
        <tr>
            <td style="text-align:center;border-style:none;">
                <input name="data[hash]" type="hidden" value="<?php echo $random_hash; ?>" />
                <input name="confirm" type="submit" value="送信" />
            </td>
        </tr>
    </table>

</form>
