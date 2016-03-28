<style type="text/stylesheet">
<!--
.item tr td{
	border:solid 1px #eee;
	padding:10px 5px;
}
-->
</style>
<div class="section">
	<table class="item">
		<caption style="padding:5px 10px;font-size:1.2em;">商品紹介設定</caption>
		<tr>
			<td colspan="2">
				商品コード:<input type="text" style="width:40%;margin:2px 7px;" name="item_code" value="<?php if(isset($item)){echo $item->getCode();}?>" />
			</td>
			<td>&nbsp;</td>
		</tr>
		<?php
			if(!is_null($item->getCode())){
				$html = array();
				$html[] = "<tr>";
				$html[] = "<td rowspan=\"5\">";
				$html[] = "<img src=\"" . $item->getAttribute("image_small") . "\" width=\"150\" />";
				$html[] = "</td>";
				$html[] = "<td>商品ID:</td>";
				$html[] = "<td>" . $item->getId() . "</td>";
				$html[] = "</tr>";
				$html[] = "<tr>";
				$html[] = "<td>商品コード:</td>";
				$html[] = "<td>".$item->getCode()."</td>";
				$html[] = "</tr>";
				$html[] = "<tr>";
				$html[] = "<td>商品名:</td>";
				$html[] = "<td>" . $item->getName() . "</td>";
				$html[] = "</tr>";
				$html[] = "<tr>";
				$html[] = "<td>価格:</td>";
				$html[] = "<td>" . number_format($item->getPrice()) . "</td>";
				$html[] = "</tr>";
				$html[] = "<tr>";
				$html[] = "<td>セール価格:</td>";
				$html[] = "<td>" . number_format($item->getSalePrice()) . "</td>";
				$html[] = "</tr>";
				
				echo implode("\n",$html);
			}
		?>
	</table>
</div>