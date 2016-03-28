<?php
/*
 * Created on 2008/12/15
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<form method="post">
<table class="list" style="width:80%;">
	<tr>
		<th>文字列認証</th>
		<td>
			<input type="hidden" name="useKeyword" value="0" />
			<input id="use_keyword_input" onclick="if(this.checked){$('#keyword_input_box').show();}else{$('#keyword_input_box').hide();}" type="checkbox" name="useKeyword" value="1" <?php if($this->useKeyword){ ?>checked="checked"<?php } ?> />
			<label for="use_keyword_input">文字列認証を使用する</label>
			
			<div id="keyword_input_box" style="<?php if(!$this->useKeyword){ ?>display:none;<?php } ?>">
				パスワード文字列：
				<input type="text" name="keyword" value="<?php echo htmlspecialchars($this->keyword,ENT_QUOTES); ?>"/>
				パスワードキー：
				<input type="text" name="name" value="<?php echo htmlspecialchars($this->name,ENT_QUOTES); ?>"/>
			</div>
		</td>
	</tr>
	
	<tr>
		<th>禁止ワード</th>
		<td>
			<textarea name="prohibitionWords" rows="10" cols="50" wrap="off"><?php
				echo implode("\n",$this->prohibitionWords);
			?></textarea>
  	</td>
	</tr>
	

	<tr>
		<td colspan="2" style="text-align:center;">
			<input type="submit" name="save" value="保存" />
		</td>
	</tr>

</table>
</form>

<h3>使い方</h3>

<div>
文字列認証を使う場合は、テンプレートに以下のように記述して下さい。
</div>


<textarea style="width:500px;height:100px;">
<p>お手数ですが、下記入力項目に「<?php echo htmlspecialchars($this->keyword,ENT_QUOTES); ?>」と入力して下さい。</p>
<input type="text" name="<?php echo htmlspecialchars($this->name,ENT_QUOTES); ?>" /> 
</textarea>