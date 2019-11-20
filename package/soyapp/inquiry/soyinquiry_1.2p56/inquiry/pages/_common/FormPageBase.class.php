<?php

class FormPageBase extends WebPage{

	const MODE_PREVIEW = "preview";
	const MODE_ADD = "add";	//項目の追加
	const MODE_CHANGE = "change";	//項目の並び替え

	function buildModal($formId, $mode = self::MODE_PREVIEW){
		switch($mode){
			case self::MODE_ADD:
				$title = "項目の追加";
				$path = "Form.Design.AddColumn.".$formId;
				break;
			case self::MODE_CHANGE:
				$title = "順番の変更";
				$path = "Form.Design.ChangeOrder.".$formId;
				break;
			case self::MODE_PREVIEW:
			default:
				$title = "プレビュー";
				$path = "Form.Preview.".$formId;
		}

$html = "<!-- Modal -->\n";
$html .= "<div class=\"modal fade\" id=\"" . $mode . "Modal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"" . $mode . "ModalCenterTitle\" aria-hidden=\"true\">\n";
$html .= <<<HTML
    <div class="modal-dialog　modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
HTML;
$html .= "            	<h5 class=\"modal-title\" id=\"" . $mode . "ModalLongTitle\">" . $title . "</h5>\n";
$html .= <<<HTML
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
HTML;
$html .= "            	<iframe src=\"" . SOY2PageController::createLink(APPLICATION_ID . "." .$path) . "\" style=\"width:100%;height:500px;\"></iframe>\n";
$html .= <<<HTML
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
HTML;
return $html;
	}
}
