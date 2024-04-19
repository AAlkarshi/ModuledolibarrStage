<?php
function altemailing_completesubstitutionarray(&$substitutionarray,$langs,$object)
{
    global $conf;
    if (isset($object->trackid)) {
        $substitutionarray['__ALTEMG_UNSUBSCRIBE__'] = str_replace('https://','',str_replace('http://', '', $conf->global->ALTEMAILING_URL_PUBLIC.'/index.php?action=unsubscribe&id='.$object->trackid));
		if($object->campagne){
			$object->campagne->getLinesArray();
			$i = 0;
			while($object->campagne->lines[$i]){
				$substitutionarray['__LINK_'.$object->campagne->lines[$i]->code.'__'] = str_replace('https://','',str_replace('http://', '', $conf->global->ALTEMAILING_URL_PUBLIC.'/index.php?action=link&code='.$object->campagne->lines[$i]->code.'&id='.$object->trackid));
				$i++;
			}
		}
	}
}

?>