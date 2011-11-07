<?php
/*************************************************************
 * Tab to display medals of the jAwards Component in a CB-Profile
 * Version: 0.6, needs jAwards > 1.1 and Joomla 1.6/1.7
 * Author: Armin Hornung @  www.arminhornung.de
 * Ported to Joomla 1.5 native with help of Chris Lehr
 * Released under GNU/GPL License : 
 * http://www.gnu.org/copyleft/gpl.html
 *************************************************************/
class getAwardsTab extends cbTabHandler {
    function getAwardsTab() {
        $this->cbTabHandler();
    }
    
  function getDisplayTab($tab,$user,$ui) {
    $database = &JFactory::getDbo();
    
    // backwards-compatible language name:
    $params   = JComponentHelper::getParams('com_languages');
    $lang = $params->get('site', 'en-GB');
    
    //$lang = $lg->getBackwardLang();
    
    // fallback to english when language not available.
     if (!file_exists(JPATH_COMPONENT.DS.'plugin/user/plug_jawards-tab/language'.DS.$lang.".php"))
         $language = "en-GB";

    // include language:
    include_once(JPATH_COMPONENT.DS.'plugin/user/plug_jawards-tab/language'.DS.$lang.".php");
        
    // Config & jAwards-component-check:
    if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jawards'.DS."config.jawards.php"))
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jawards'.DS."config.jawards.php");
    else return "Error: jAwards Config file could not be read. Is the appropriate version of the jAwards-component installed?";
    
    if (file_exists(JPATH_BASE.DS.'components'.DS.'com_jawards'.DS."jawards.interface.php"))
        require_once(JPATH_BASE.DS.'components'.DS.'com_jawards'.DS."jawards.interface.php");
    else return "Error: jAwards Interface could not be found. This plugin requires jAwards 1.1 or later!";
    
    $interface = new jAwardsInterface();
    
    $params = $this->params;
    $Itemid = $interface->getItemId();
    
    $total=$interface->getNumAwardsUser($user->id); 
    
    // display no tab when there are no awards:
    if($total < 1) {
        return null;
    }
    
    // Pagination:
    $startpage=1;
    $perpage = $params->get('numAwards','10');
        
    $pagingParams = $this->_getPaging(array(),array("awardstab_"));
    if ($pagingParams["awardstab_limitstart"] === null)
        $pagingParams["awardstab_limitstart"] = "0";
    if ($perpage > $total) 
        $pagingParams["awardstab_limitstart"] = "0";

    $limitstart = $pagingParams["awardstab_limitstart"]?$pagingParams["awardstab_limitstart"]:"0";
    $items = $interface->getAwardsUser($user->id, "a.date DESC", $perpage,$limitstart);
    
    // Link for individual awards to medal:
    $medalLink='index.php?option=com_jawards&task=listusers&award=';

    $return="";
    $return .= "<p>".AWARDS_TOTAL_NUMBER_AWARDS.": $total</p>";
    
    $return .= "<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"95%\">";
    $return .= "<tr class=\"sectiontableheader\">";
    $return .= "<td colspan=\"2\">".AWARDS_AWARD."</td><td>".AWARDS_DATE."</td>";
    if ($jAwards_Config['showawardreason'])
        $return .="<td>".AWARDS_REASON."</td>";
    $return .= "</tr>";
    $i=1;
    foreach($items AS $item) {
        $i= ($i==1) ? 2 : 1;
        $displayImages = 1;
        $maxGrouping = max(1,intval($params->get('maxGrouping')));
        if ($jAwards_Config['groupawards'])
            $displayImages = min($item->count, $maxGrouping);
        $return .= "<tr class=\"sectiontableentry$i\">"
        ."<td>";
        
        for ($j=0;$j<$displayImages; $j++){
            $return .="<img src=\"".JUri::base(true)."/images/medals/".$item->image."\" alt=\"".$item->image."\" style=\"vertical-align:middle;\"/>";
        }

        if ($jAwards_Config['groupawards'] && $item->count > 1)
            $return .= "<span style=\"display:inline-block; vertical-align:middle\">".$item->count."x</span>";

        $return .= "</td><td><a href=\"".  JRoute::_($medalLink.$item->award.$Itemid) . "\">$item->name</a> </td>";
        $return .= "<td> ".strftime($jAwards_Config['dateformat'],strtotime($item->date)) . "</td>";
        if ($jAwards_Config['showawardreason'])
            $return .="<td>$item->reason</td>";
            
        $return.= "</tr>\n";

    }
    $return .= "</table>";
    
    // pagination:
    if ($perpage < $total) {
        $return .= "<div style='width:95%;text-align:center;'>"
        .$this->_writePaging($pagingParams,"awardstab_",$perpage,$total)
        ."</div>";
    }
    $descUrl = $params->get('awardsDesc','');
    $showDesc = $params->get('showAwardsDesc','1');
    
    if ($descUrl =='')
        $descUrl='index.php?option=com_jawards'.$Itemid;
    
    if ($showDesc)
            $return.="<br /><a href=\"$descUrl\">".AWARDS_INFORMATION."</a>";
          
    return $return;
    }
}   
?>