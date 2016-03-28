<?php
/*
 * Created on 2009/07/09
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>

<form method="post">

<div class="span-11">

<div class="section">
	<p class="sub">メールアドレス(改行で複数入力)</p>
<textarea name="Config[mailAddress]" id="mail_address">
<?php
echo implode("\n",$this->mailAddress);
?>
</textarea>
</div>

</div>

<div class="span-10 last">
<p style="margin-top:30px;">
このプラグインはmb_send_mailを使用してメールを送信します。<br />
設定によってはメールが届かない可能性もあります。<br />
</p>
</div>

<div class="span-10" style="clear:both;">

<h3>コメントメールの設定</h3>

<div class="section">	
	<input type="hidden" name="Config[commentMail][isSend]" value="0" />
	<input id="is_send_comment_mail" type="checkbox" name="Config[commentMail][isSend]" value="1" <?php if($this->commentMail["isSend"]){ ?>checked<?php } ?>>
	<label for="is_send_comment_mail">コメントメールを送信する</label>
</div>

<div class="section">
	<p class="sub">タイトル</p>
	<input class="text" name="Config[commentMail][title]" value="<?php echo htmlspecialchars($this->commentMail["title"]); ?>" />
</div>

<div class="section">
<p class="sub">本文</p>
<textarea name="Config[commentMail][header]" style="width:350px;">
<?php
echo $this->commentMail["header"];
?>
</textarea>
</div>

</div>

<div class="span-10 last">

<h3>トラックバックメールの設定</h3>

<div class="section">	
	<input type="hidden" name="Config[trackbackMail][isSend]" value="0" />
	<input id="is_send_trackback_mail" type="checkbox" name="Config[trackbackMail][isSend]" value="1" <?php if($this->trackbackMail["isSend"]){ ?>checked<?php } ?>>
	<label for="is_send_trackback_mail">トラックバックメールを送信する</label>
</div>

<div class="section">
	<p class="sub">タイトル</p>
	<input class="text" name="Config[trackbackMail][title]" value="<?php echo htmlspecialchars($this->trackbackMail["title"]); ?>" />
</div>

<div class="section">
<p class="sub">本文</p>
<textarea name="Config[trackbackMail][header]" style="width:350px;">
<?php
echo $this->trackbackMail["header"];
?>
</textarea>
</div>
</div>

<div class="buttons span-20" style="text-align:center;margin-top:20px;padding-top:20px;border-top:solid 1px #999;">
	<input type="submit" name="save" value="保存" />
	<input type="button" name="test" value="テスト送信" style="margin-left:100px;" onclick="$('#test_mail_address').val($('#mail_address').val());common_submit_to_layer($('#test_send_mail'),{width:300,height:300});$('#test_send_mail').submit();" />
</div>

</form>

<form method="post" id="test_send_mail">
	<input type="hidden" name="test_send" value="1" />
	<input type="hidden" id="test_mail_address" name="mailAddress" />
</form>	