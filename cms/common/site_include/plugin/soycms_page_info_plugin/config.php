<style type="text/css">
.row{
	border-bottom:solid 1px #ccc;
}
.even td{
	background-color:#FFee99;
	vertical-align:middle;
	height:40px;
	
}
img.page_list_image{
	width:30px;
	height:30px;
	margin-right:10px;
	vertical-align:middle;
}
img.close_icon_image{
	margin-left:-20px;
	width:16px;
	height:16px;
}
td.op{
	width:2em;
	vertical-align:top;
	text-align:center;	
}

</style>
<script type="text/javascript">
function open_info(id){
	$("#page_info_" + id).toggle();
	
}
function toggle_all(){
	var args = arguments;
	for(var i=0,l=args.length;i<l;i++){
		open_info(args[i]);		
	}
		
}
function show_help(){
	$("#page_info_plugin_help").show();
}
</script>

<div style="text-align:right"><a href="#help_show_keyword" onclick="show_help();">使い方</a></div>

<?php
/*
 * Created on 2009/05/25
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
$blogPageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
$pages = $pageDAO->get();

$closeIcon = SOY2PageController::createRelativeLink("./css/pagelist/images/draft.gif");

echo "<form method=\"post\">";

echo "<table>";

echo "<tr><th class=\"op\"><a href=\"javascript:void(0);\" onclick=\"toggle_all(".implode(",",array_keys($pages)).");\">[*]</a></th><th colspan=\"2\">ページ情報</th></tr>";

$counter = 0;
foreach($pages as $key => $page){
	$counter+=1;
	$class = "row";
	
	$id = $page->getId();
	
	if((int)($counter % 2) == 0){
//		$class .= " even";
	}
	
	$bgcolor = ( (int)($counter % 2) == 0 ) ? "#E5ECF9" : "" ;

	echo "<tr class=\"$class\" style=\"background-color:$bgcolor;\" onclick=\"open_info($id);\" onmouseover=\"this.style.cursor='pointer';this.style.backgroundColor='#FFEE99'\" onmouseout=\"this.style.backgroundColor='$bgcolor';\">";
	
	echo "<td onmouseover=\"this.style.backgroundColor='#FFEE99'\" onmouseout=\"this.style.backgroundColor=''\">"; 
	echo "<img src=\"".$page->getIconUrl()."\" class=\"page_list_image\" />";
	if(!$page->getIsPublished())echo "<img src=\"$closeIcon\" class=\"close_icon_image\" />";
	echo "</td>";
	
	echo "<td>";
	echo htmlspecialchars($page->getTitle());
	echo '&nbsp;( <a href="'.UserInfoUtil::getSiteURL().$page->getUri().'" target="_blank">/' . $page->getUri() . "</a> )";
	echo "</td>";
	echo "<td style=\"text-align:right\">" . "<span>最終更新日：" . date("Y-m-d H:i:s",$page->getUdate()) . "</span></td>";
	
	echo "</tr>";
	
	echo "<tr id=\"page_info_".$id."\" style=\"display:none;\">";
	echo "<td>&nbsp;</td>";
	echo "<td colspan=\"2\">";
	
	
	echo "<div >";
	
	echo '<div class="section"><p class="sub">タイトル</p>';
	echo '<input type="text" class="title" name="format['.$page->getId().'][title]" value="'.htmlspecialchars($page->getTitle(),ENT_QUOTES).'" />';
	echo '</div>';
	
	echo '<div class="section"><p class="sub">URL</p>';
	echo '<input type="text" class="text" name="format['.$page->getId().'][uri]" value="'.$page->getUri().'" />';
	echo '</div>';
	
	echo '<div class="section"><p class="sub">タイトルフォーマット</p>';
	if($page->getPageType() == Page::PAGE_TYPE_BLOG){
		
		$page = $blogPageDAO->getById($page->getId());
		
		
		
		$value = $page->getTopTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		$value2 = htmlspecialchars($page->getTopPageUri(),ENT_QUOTES);
		$sortValue = htmlspecialChars($page->getTopEntrySort(), ENT_QUOTES);
		
		echo '<table>';
		echo '<col style="width:3em" />';
		echo '<col style="width:1em" />';
		
		echo '<tr>';		
		echo "<td>top</td><td>:</td><td><input class=\"text\" name=\"format[".$page->getId()."][topTitleFormat]\" value=\"$value\"/>";
		echo "</td><td>"."<input class=\"text\" name=\"format[".$page->getId()."][topPageUrl]\" value=\"$value2\"/>";
		echo '</td><td><select name="format['.$page->getId().'][topEntrySort]">';
		if($sortValue != "desc"){
			echo '<option value="desc">降順</option>';
			echo '<option value="asc" selected>昇順</option>';
		}else{
			echo '<option value="desc" selected>降順</option>';
			echo '<option value="asc">昇順</option>';
		}
		echo '</select></td></tr>';
		
		$value = $page->getMonthTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		$value2 = htmlspecialchars($page->getMonthPageUri(),ENT_QUOTES);
		$sortValue = htmlspecialChars($page->getMonthEntrySort(), ENT_QUOTES);
		
		echo '<tr>';
		echo "<td>month</td><td>:</td><td><input class=\"text\" name=\"format[".$page->getId()."][monthTitleFormat]\" value=\"$value\"/>";
		echo '</td><td>'."<input class=\"text\" name=\"format[".$page->getId()."][monthPageUri]\" value=\"$value2\"/>";
		echo '</td><td><select name="format['.$page->getId().'][monthEntrySort]">';
		if($sortValue != "desc"){
			echo '<option value="desc">降順</option>';
			echo '<option value="asc" selected>昇順</option>';
		}else{
			echo '<option value="desc" selected>降順</option>';
			echo '<option value="asc">昇順</option>';
		}
		echo '</select></td></tr>';
		
		$value = $page->getCategoryTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		$value2 = htmlspecialchars($page->getCategoryPageUri(),ENT_QUOTES);
		$sortValue = htmlspecialChars($page->getCategoryEntrySort(), ENT_QUOTES);
		
		echo '<tr>';
		echo "<td>category</td><td>:</td><td><input class=\"text\" name=\"format[".$page->getId()."][categoryTitleFormat]\" value=\"$value\"/>";
		echo '</td><td>'."<input class=\"text\" name=\"format[".$page->getId()."][categoryPageUri]\" value=\"$value2\"/>";
		echo '</td><td><select name="format['.$page->getId().'][categoryEntrySort]">';
		if($sortValue != "desc"){
			echo '<option value="desc">降順</option>';
			echo '<option value="asc" selected>昇順</option>';
		}else{
			echo '<option value="desc" selected>降順</option>';
			echo '<option value="asc">昇順</option>';
		}
		echo '</select></td></tr>';
		
		$value = $page->getEntryTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		$value2 = htmlspecialchars($page->getEntryPageUri(),ENT_QUOTES);
		
		echo '<tr>';
		echo "<td>entry</td><td>:</td><td><input class=\"text\" name=\"format[".$page->getId()."][entryTitleFormat]\" value=\"$value\"/>";
		echo '</td><td>'."<input class=\"text\" name=\"format[".$page->getId()."][entryPageUri]\" value=\"$value2\"/>";
		echo '</td></tr>';
		
		$value = $page->getFeedTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		$value2 = htmlspecialchars($page->getRssPageUri(),ENT_QUOTES);
		
		echo '<tr>';
		echo "<td>feed</td><td>:</td><td><input class=\"text\" name=\"format[".$page->getId()."][feedTitleFormat]\" value=\"$value\"/>";
		echo '</td><td>'."<input class=\"text\" name=\"format[".$page->getId()."][rssPageUri]\" value=\"$value2\"/>";
		echo '</td></tr>';
		echo '</table>';
		
	}else{
	
		$value = $page->getPageTitleFormat();
		$value = htmlspecialchars($value,ENT_QUOTES);
		
		echo "<input class=\"text\" name=\"format[".$page->getId()."][pageTitleFormat]\" value=\"$value\"/>";
		
	}
	echo '</div>';
	
	$published = ($page->getIsPublished()) ? "checked" : "";
	
	echo '<div class="section"><p class="sub">公開状態</p>';
	echo "<input type=\"hidden\" name=\"format[$id][isPublished]\" value=\"0\"/>";
	echo "<input type=\"checkbox\" id=\"is_published_$id\" name=\"format[$id][isPublished]\" value=\"1\" $published/>";
	echo "<label for=\"is_published_$id\">公開する</label>";
	echo '</div>';
	
	$start = $page->getOpenPeriodStart();
	$end   = $page->getOpenPeriodEnd();
	
	
	$show = CMSUtil::getOpenPeriodMessage($start, $end);

	
	$startText = (is_null($start)) ? "" : @date("Y-m-d H:i:s",$start);
	$endText = (is_null($end)) ? "" : @date("Y-m-d H:i:s",$end);
	
	echo '<div class="section">
		<p class="sub">公開期間</p>
		<span id="open_period_show_'.$id.'">'.$show.'</span>
		<button id="open_period_show_button_'.$id.'" type="button" onclick="$(\'#open_period_show_button'.$id.'\').hide();$(\'#open_period_input_'.$id.'\').show();$(\'#open_period_show_button_'.$id.'\').hide();">公開期間を設定する</button>
		<div id="open_period_input_'.$id.'" style="display:none;">
			<table style="width: 28em">
				<tr>
					<td style="vertical-align:top;">
						<input type="text" name="format['.$id.'][openPeriodStart]" value="'.$startText.'" id="start_date_'.$id.'" size="25" maxlength="19" style="width:100%">
						
						<div style="font-size:10px;margin-top:5px;margin-left:5px;">
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,0,0,0,0),true,false));return false;">今日</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,1,0,0,0),true,false));return false;">明日</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,7,0,0,0),true,false));return false;">来週</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(buildDateString(movedate(new Date,0,1,0,0,0,0),true,false));return false;">来月</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,0,0,0,0),false,false));return false;">現在の時刻</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#start_date_'.$id.'\').val(\'\');return false;">日時のクリア</a>
						</div>
					</td>
					<td style="vertical-align:top;text-align:center;width:4em;">から</td>
					<td style="vertical-align:top;">
						<input type="text" name="format['.$id.'][openPeriodEnd]" value="'.$endText.'" id="end_date_'.$id.'" size="25" maxlength="19" style="width:100%">
						<div style="font-size:10px;">
							<a href="javascript:void(0);" onclick="$(\'#end_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,1,0,0,0),true,true));return false;">明日</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#end_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,7,0,0,0),true,true));return false;">来週</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#end_date_'.$id.'\').val(buildDateString(movedate(new Date,0,1,0,0,0,0),true,true));return false;">来月</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#end_date_'.$id.'\').val(buildDateString(movedate(new Date,0,0,0,0,0,0),false,true));return false;">現在の時刻</a><br/>
							<a href="javascript:void(0);" onclick="$(\'#end_date_'.$id.'\').val(\'\');return false;">日時のクリア</a>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>';
	
	$value = htmlspecialchars(@$this->keywords[$page->getId()],ENT_QUOTES);
	
	echo '<div class="section"><p class="sub">キーワード(<a href="#help_show_keyword" onclick="show_help();">?</a>)</p>';
	echo "<input class=\"text\" name=\"keyword[".$page->getId()."]\" value=\"$value\"/>";	
	echo '</div>';
	
	$value = htmlspecialchars(@$this->description[$page->getId()],ENT_QUOTES);
	
	echo '<div class="section"><p class="sub">概要(<a href="#help_show_description" onclick="show_help();">?</a>)</p>';
	echo "<input class=\"text\" name=\"description[".$page->getId()."]\" value=\"$value\"/>";
	echo '</div>';
	
	echo "</div>";
	echo "</td>";
	
	echo "</tr>";
	
}

echo "</table>";

echo "<input type=\"submit\" value=\"保存\" />";

echo "</form>";
?>

<div id="page_info_plugin_help" style="display:none;">
<a name="help_show_keyword"></a>
<h3>キーワードの表示方法</h3>

テンプレートに以下のように記述してください。
<pre>
&lt;meta cms:id="page_keyword" /&gt;
</pre>

<a name="help_show_description"></a>
<h3>概要の表示方法</h3>
<pre>
&lt;meta cms:id="page_description" /&gt;
</pre>
</div>