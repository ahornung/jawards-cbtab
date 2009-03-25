<?php
/*************************************************************
 * Tab to display medals of the jAwards Component in a CB-Profile
 * Version: 0.4, needs jAwards > 0.8 
 * Author: Armin Hornung @  www.arminhornung.de
 * Released under GNU/GPL License : 
 * http://www.gnu.org/copyleft/gpl.html
 *************************************************************/
class getAwardsTab extends cbTabHandler {
	function getAwardsTab() {
		$this->cbTabHandler();
	}
	
  function getDisplayTab($tab,$user,$ui) {
	global $database,$mosConfig_live_site, $mosConfig_absolute_path, $mosConfig_lang;
	// Language:
	if (file_exists($mosConfig_absolute_path."/administrator/components/com_jawards/language/".$mosConfig_lang.".php"))
	    include_once($mosConfig_absolute_path."/administrator/components/com_jawards/language/".$mosConfig_lang.".php");
	else if(file_exists($mosConfig_absolute_path."/administrator/components/com_jawards/language/english.php"))
		include_once($mosConfig_absolute_path."/administrator/components/com_jawards/language/english.php");
	else return "Error: No language file could be loaded. Is the jAwards component properly installed?";
	
	// Config & jAwards-component-check:
	if (file_exists($mosConfig_absolute_path."/administrator/components/com_jawards/config.jawards.php"))
		require_once($mosConfig_absolute_path."/administrator/components/com_jawards/config.jawards.php");
	else return "Error: jAwards Config file could not be read. Is the appropriate version of the jAwards-component installed?";
	
	if (file_exists($mosConfig_absolute_path."/components/com_jawards/jawards.interface.php"))
		require_once($mosConfig_absolute_path."/components/com_jawards/jawards.interface.php");
	else return "Error: jAwards Interface could not be found. This plugin requires jAwards 1.0 or later!";
	
	$interface = new jAwardsInterface();
	
	$params = $this->params;
	$Itemid = $interface->getItemId();
	
	$total=$interface->getNumAwardsUser($user->id);	
	$items = $interface->getAwardsUser($user->id);
	
	if(!count($items)>0) {
		//debugging:
		// return $database->stderr();
		return null;
	}

	// Link for individual awards to medal:
	$medalLink='index.php?option=com_jawards&task=listusers&award=';

	$return="";
	$return .= "<p>"._AWARDS_TOTAL_NUMBER_AWARDS.": $total</p>";
	
	$return .= "<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"95%\">";
	$return .= "<tr class=\"sectiontableheader\">";
	$return .= "<td colspan=\"2\">"._AWARDS_AWARD."</td><td>"._AWARDS_DATE."</td>";
	if ($ja_config['showawardreason'])
		$return .="<td>"._AWARDS_REASON."</td>";
	$return .= "</tr>";
	$i=1;
	foreach($items AS $item) {
		$i= ($i==1) ? 2 : 1;
		$displayImages = 1;
		$maxGrouping = max(1,intval($params->get('maxGrouping')));
		if ($ja_config['groupawards'])
			$displayImages = min($item->count, $maxGrouping);
		$return .= "<tr class=\"sectiontableentry$i\">"
		."<td>";
		
		for ($j=0;$j<$displayImages; $j++){
			$return .="<img src=\"".$mosConfig_live_site."/images/medals/".$item->image."\" alt=\"".$item->image."\" style=\"vertical-align:middle;\"/>";
		}

		if ($ja_config['groupawards'] && $item->count > 1)
			$return .= "<span style=\"display:inline-block; vertical-align:middle\">".$item->count."x</span>";

		$return .= "</td><td><a href=\"". sefRelToAbs($medalLink.$item->award.$Itemid) . "\">$item->name</a> </td>";
		$return .= "<td> ".date("d. m. Y",strtotime($item->date)) . "</td>";
		if ($ja_config['showawardreason'])
			$return .="<td>$item->reason</td>";
			
		$return.= "</tr>\n";

	}
	$return .= "</table>";
	$descUrl = $params->get('awardsDesc','');
	$showDesc = $params->get('showAwardsDesc','1');
	
	if ($descUrl =='')
		$descUrl='index.php?option=com_jawards'.$Itemid;
	
	if ($showDesc)
      		$return.="<br /><a href=\"$descUrl\">"._AWARDS_INFORMATION."</a>";
	      
	return $return;
	}
}	
?>