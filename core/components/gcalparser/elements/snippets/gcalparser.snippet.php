<?php
/**
 * Created by PhpStorm.
 * User: mat
 * Date: 11/20/2017
 * Time: 22:18
 */
$corePath = $modx->getOption('gcalparser.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/gcalparser/');
/** @var gcalparser $gcalparser */
$gcalparser = $modx->getService(
    'gcalparser',
    'gcalparser',
    $corePath . 'model/gcalparser/',
    array(
        'core_path' => $corePath
    )
);

if (!($gcalparser instanceof gcalparser))
    return;

$calendars = $modx->getOption('calendars', $scriptProperties, null);
$calendars = $gcalparser->explodeAndClean($calendars);
$limit = $modx->getOption('limit', $scriptProperties, 0);
$offset = $modx->getOption('offset', $scriptProperties, 0);
$tpl = $modx->getOption('tpl', $scriptProperties, null);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'page.total');
$output = null;
if(!empty($calendars)){
    $events = $gcalparser->getAllEvents($calendars,$limit,$offset,$totalVar);
    if(empty($tpl)){
        $output = $modx->toJSON($events);
    }else{
        foreach($events as $event){
            $output .= $modx->getChunk($tpl, $event);
        }
    }
}

return $output;