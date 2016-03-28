<?php

class EntryPagerComponent extends SOYBodyComponentBase{
	
	function EntryPagerComponent($arguments){
		
		list($offset, $limit, $count, $currentLink) = $arguments;
		
		$showPrevAncor = ($offset>0);
		$showNextAncor = ($offset+$limit) < $count;
		
		$prevPage = 'offset='.($offset-$limit).'&limit='.$limit;
		$nextPage = 'offset='.($offset+$limit).'&limit='.$limit;
		
		$this->createAdd("prevAnchor","HTMLLink",array(
			"link"    => ($currentLink . ( (strpos($currentLink, "?") === false) ? "?" : "&" ) . $prevPage),
			"visible" => $showPrevAncor
		));
		$this->createAdd("nextAnchor","HTMLLink",array(
			"link"    => ($currentLink . ( (strpos($currentLink, "?") === false) ? "?" : "&" ) . $nextPage),
			"visible" => $showNextAncor
		));

		$contentPage = CMSMessageManager::get("SOYCMS_ENTRY_COUNT_PAGER", array(
        	"ALL" => $count,
        	"FROM"  => ( $count>0 ? max($offset+1,1) : 0 ),
        	"TO"    => min(($offset+$limit),$count)
        ));
		$this->createAdd("contentPage","HTMLLabel",array(
			"text" => $contentPage
		));
	}

}
