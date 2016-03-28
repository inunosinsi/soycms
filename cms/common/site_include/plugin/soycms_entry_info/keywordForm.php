<script type="text/javascript">
var update_count_entry_description = function(ele){
	
	$("#count_entry_description").html(ele.value.length);
	
	return true;
}
</script>
<div class="section">

	<table>
		<caption style="padding:5px 10px;font-size:1.2em;">記事のメタ情報設定</caption>
		<tr>
			<td style="width:45%;border:solid 1px #eee;padding:10px 5px;">
				<p class="sub">キーワード(カンマ(<b>,</b>)&nbsp;区切りで複数入力)</p>
				<input type="text" style="width:98%;margin:2px 7px;" name="keyword" value="<?php echo htmlspecialchars($keyword,ENT_QUOTES); ?>" />
			</td>
			
			<td style="border:solid 1px #eee;padding:10px 5px;">

				<p class="sub">概要(<span id="count_entry_description"><?php echo mb_strlen($description); ?></span>文字)</p>
				<input type="text" style="width:98%;margin:2px 7px;" name="description" value="<?php echo htmlspecialchars($description,ENT_QUOTES); ?>" onkeyup="return update_count_entry_description(this);" />
			</td>
			
		</tr>
	</table>
	
</div>