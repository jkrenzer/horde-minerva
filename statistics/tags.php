<?php
/**
 * Minerva tags statistic
 *
 * TODO: Round values per Horde_Currencies fact digits
 *
 * $Horde: incubator/minerva/statistics/tags.php,v 1.12 2009/11/09 19:58:38 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

require_once dirname(__FILE__) . '/../lib/base.php';

$title = _("Tags graph");

$graph =& Minerva_Graph::getCanvas($title);
if ($graph instanceof PEAR_Error) {
    $notification->push($graph);
}

$form = Minerva_Graph::getDateRangeForm();

$v = $form->addVariable(_("Type"), 'type', 'enum', true, false, false, array(Minerva::getTypes()));
$v->setDefault('invoice');

$tages = Minerva::getStatuses();
$v = &$form->addVariable(_("Statuses"), 'statuses', 'set', true, false, false, array($tages));
$v->setDefault(array('pending', 'paid'));

$tags = Minerva::getTags();
$v = &$form->addVariable(_("Tags"), 'tags', 'set', true, false, false, array($tags));
$defaults = $tags;
unset($defaults[0]);
$v->setDefault(array_keys($defaults));

if ($form->validate()) {

    $form->getInfo(null, $info);
    $criteria = array('invoice' => array('type' => $info['type'],
                                         'dateto' => $info['to'],
                                         'datefrom' => $info['from']));
    $data = array();
    foreach ($info['tags'] as $tag) {
        $criteria['invoice']['tag'] = $tag;
        $invoices = Minerva_Graph::getList($criteria);
        if ($invoices instanceof PEAR_Error) {
            $notification->push($invoices);
            continue;
        }
        if (empty($invoices)) {
            continue;
        }
        foreach ($invoices as $invoice) {
            $key = substr($invoice['date'], 0, 7);
            if (isset($data[$tag][$key])) {
                $data[$tag][$key] += round($invoice[$info['tax']], 2);
            } else {
                $data[$tag][$key] = round($invoice[$info['tax']], 2);
            }
        }
        ksort($data[$tag]);
    }

    if (empty($data)) {
        $notification->push(_("No data to display"), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('statistics/tags.php'));
        exit;
    }

    // Prepare fill
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');

    $sheet = 0;
    $totals = array();
    foreach ($data as $tag => $mounths) {
        $Datasets[$sheet] =& Image_Graph::factory('dataset');
        $Datasets[$sheet]->setName($tags[$tag]);
        foreach ($mounths as $name => $value) {
            if ($value) {
                $Datasets[$sheet]->addPoint($name, $value);
                if (isset($totals[$tag])) {
                    $totals[$tag] += $value;
                } else {
                    $totals[$tag] = $value;
                }
            }
        }
        $sheet++;
        $r = rand(128, 255);
        $g = rand(128, 255);
        $b = rand(128, 255);
        $FillArray->addColor('#' . dechex($r) . dechex($g) . dechex($b));
    }

    // create the 1st plot as smoothed area chart using the 1st dataset
    $Plot =& $Plotarea->addNew('bar', array($Datasets));
    $Plot->setLineColor('gray');
    $Plot->setFillStyle($FillArray);

    // create marker
    $Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
    $Plot->setMarker($Marker);

    // output the Graph
    $graph_image = Minerva_Graph::render($graph);
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

if (isset($graph_image)) {
    echo $graph_image;
    echo '<div style="text-align: right; width: 300px; padding-bottom: 5px;">';
    foreach ($totals as $tag => $total) {
        echo $tags[$tag] . ': ' . Minerva::format_price($total) . '<br />';
    }
    echo _("Total") . ': <strong>' . Minerva::format_price(array_sum($totals)) . '</strong></div>';
}

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
