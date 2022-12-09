<?php if(isset($_GET["updated"])){ ?>
<div class="alert alert-success">更新しました</div>
<?php } ?>

<form method="post">
	<div class="table-responsive">
		<table id="news_table" class="table table-striped">
			<caption>新着情報</caption>
			<tbody>
				<tr>
					<th>&nbsp;</th>
					<th>日時</th>
					<th>テキスト</th>
					<th>リンク先</th>
					<th>&nbsp;</th>
				</tr>

				<!--
				<tr>
					<td>
						<a onlick="news_move_up($(this));">▲</a>
						<a onlick="news_move_down($(this));">▼</a>
					</td>
					<td>
						<input type="text" style="width:90%;" name="news[][create_date]" />
					</td>
					<th>
						<input type="text" style="width:90%;" name="news[][text]" />
					</th>
					<th>
						<input type="text" style="width:90%;" name="news[][url]" />
					</th>
					<th>
						<a class="btn btn-default" href="javascript:void(0);" onlick="news_clear($(this));">Clear</a>
					</th>
				</tr>
				-->

				<?php foreach($news as $key => $array){
					$key = htmlspecialchars($key);
					if(!isset($array["text"]) || strlen($array["text"]) < 1)continue;
				?>
				<tr>
					<td>
						<a href="javascript:void(0);" onclick="news_move_up($(this));">▲</a>
						<a href="javascript:void(0);" onclick="news_move_down($(this));">▼</a>
					</td>
					<td>
						<input type="text" style="width:90%;" name="news[<?php echo $key; ?>][create_date]" value="<?php echo htmlspecialchars(@$array["create_date"],ENT_QUOTES); ?>" />
					</td>
					<th>
						<input type="text" style="width:90%;" name="news[<?php echo $key; ?>][text]" value="<?php echo htmlspecialchars(@$array["text"],ENT_QUOTES); ?>" />
					</th>
					<th>
						<input type="text" style="width:90%;" name="news[<?php echo $key; ?>][url]" value="<?php echo htmlspecialchars(@$array["url"],ENT_QUOTES); ?>" />
					</th>
					<th>
						<a class="btn btn-default" href="javascript:void(0);" onclick="news_clear($(this));">Clear</a>
					</th>
				</tr>

				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
					<td>
						<input type="text" id="news_text" style="width:90%;" />
					</td>
					<td>
						<input type="text" id="news_link" style="width:90%;" value="http://" />
					</td>
					<td>
						<a class="btn btn-primary" href="javascript:void(0);"
							onclick="add_news();">追加</a>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<div class="text-center">
		<input type="submit" name="update" class="btn btn-primary btn-lg" value="更新">
	</div>
</form>

<h4><a href="javascript:void(0);" onclick="$('#html_example').toggle();">テンプレートへの記述例</a></h4>
<pre id="html_example" style="display:none;">
<strong>&lt;!-- shop:module="common.simple_news" --&gt;</strong>
&lt;h3&gt;新着情報&lt;/h3&gt;
&lt;div&gt;
	&lt;dl&gt;
		<strong>&lt;!-- cms:id="news_list" --&gt;</strong>
		&lt;dt <strong>cms:id="create_date"</strong>&gt;2009.7.29&lt;/dt&gt;
		&lt;dd <strong>cms:id="title"</strong>&gt;新着テキスト&lt;/dd&gt;
		<strong>&lt;!-- /cms:id="news_list" --&gt;</strong>
	&lt;/dl&gt;
&lt;/div&gt;
<strong>&lt;!-- /shop:module="common.simple_news" --&gt;</strong>
</pre>

<br />

<script type="text/javascript">
var add_news = function(){

	var tbody = $("#news_table tbody");
	var key = (new Date()).getTime() +"_"+ (new Date()).getMilliseconds();

	var url = $("#news_link").val();

	var text = $("#news_text").val();
	if(text.length<1)return;

	var tr = $("<tr></tr>");
	tbody.append(tr);

	//operation
	var td = $('<td><a href="javascript:void(0);" onclick="news_move_up($(this));">▲</a>'
				+'<a href="javascript:void(0);" onclick="news_move_down($(this));">▼</a></td>');
	tr.append(td);

	//<input type="text" style="width:90%;" name="news[][create_date]" />
	var input = $('<input type="text" style="width:90%;" name="news[][create_date]" />');
	var date = (new Date);
	input.val(date.getFullYear() + "-" + (date.getMonth()+1) + "-" + date.getDate());
		 //+ date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds());
	input.attr("name","news["+key+"][create_date]");
	var td = $("<td></td>");
	td.append(input);
	tr.append(td);

	//<input type="text" style="width:90%;" name="news[][text]" />
	var input = $('<input type="text" style="width:90%;" name="news[][text]" />');
	input.val(text);
	input.attr("name","news["+key+"][text]");
	var td = $("<td></td>");
	td.append(input);
	tr.append(td);

	//<input type="text" style="width:90%;" name="news[][url]" />
	var input = $('<input type="text" style="width:90%;" name="news[][url]" />');
	input.val(url);
	input.attr("name","news["+key+"][url]");
	var td = $("<td></td>");
	td.append(input);
	tr.append(td);

	//<a class="btn btn-default" href="javascript:void(0);" onlick="news_clear($(this));">Clear</a>
	var td = $('<td><a class="btn btn-default" href="javascript:void(0);" onclick="news_clear($(this));">Clear</a></td>');
	tr.append(td);

	$("#news_link").val("http://");
	$("#news_text").val("");

}

var news_move_up = function(ele){

	var tr = ele.parent().parent();
	tr.insertBefore(tr.prev());

	check_row();

}

var news_move_down = function(ele){

	var tr = ele.parent().parent();
	tr.insertAfter(tr.next());

	check_row();

}

var news_clear = function(ele){

	var tr = ele.parent().parent();
	tr.remove();

	check_row();

}

var check_row = function(){
	$("table.form_list").each(function(){
			var counter = 1;
			var rows = null;
			if($(this).find("tbody")){
				rows = $(this).find("tbody tr");
			}else{
				rows = $(this).find("tr");
			}
			rows.each(function(){
				if(0 == (counter % 2))$(this).addClass("odd");
				counter++;
			});

		});
}
</script>
