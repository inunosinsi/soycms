<style type="text/stylesheet">
<!--
.item tr td{
	border:solid 1px #eee;
	padding:10px 5px;
}
-->
</style>
<div class="alert alert-success" style="margin-top:15px;">商品紹介設定</div>

<div class="form-group">
	<div class="form-inline">
		<label>商品コード:</label>
		<input type="text" class="form-control" name="item_code" value="<?php if(isset($item)){echo $item->getCode();}?>" style="width:380px;">
	</div>
</div>

<table class="table" style="width:500px;">
	<?php
		if(strlen((string)$item->getCode())){
			$html = array();
			$html[] = "<tr>";
			$html[] = "<td rowspan=\"5\">";
			$imageSmall = (string)$item->getAttribute("image_small");
			if(strlen($imageSmall)) $html[] = "<img src=\"/" . UserInfoUtil::getSite()->getSiteId() . "/im.php?src=" . $imageSmall . "&width=150\">";
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
			$html[] = "<td>" . soy2_number_format($item->getPrice()) . "</td>";
			$html[] = "</tr>";
			$html[] = "<tr>";
			$html[] = "<td>セール価格:</td>";
			$html[] = "<td>" . soy2_number_format($item->getSalePrice()) . "</td>";
			$html[] = "</tr>";

			echo implode("\n",$html);
		}
	?>
</table>
