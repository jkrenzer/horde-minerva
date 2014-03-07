<?php
/**
 * Minerva status statistic
 *
 * TODO: Round values per Horde_Currencies fact digits
 *
 * $Horde: incubator/minerva/statistics/statuses_pie.php,v 1.13 2009/11/09 19:58:38 duck Exp $
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

$title = _("Statuses pie graph");

$graph =& Minerva_Graph::getCanvas($title);
if ($graph instanceof PEAR_Error) {
    $notification->push($graph);
}

$form = Minerva_Graph::getDateRangeForm();

$v = $form->addVariable(_("Type"), 'type', 'enum', true, false, false, array(Minerva::getTypes()));
$v->setDefault('invoice');

$statuses = Minerva::getStatuses();
$v = &$form->addVariable(_("Statuses"), 'status', 'set', true, false, false, array($statuses));
$v->setDefault(array('pending', 'paid'));

if ($form->validate()) {

    $form->getInfo(null, $info);
    $criteria = array('invoice' => array('type' => $info['type'],
                                         'status' => $info['status'],
                                         'dateto' => $info['to'],
                                         'datefrom' => $info['from']));

    $sum = 0;
    $data = array();
    $invoices = Minerva_Graph::getList($criteria);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        $invoices = array();
    }
    foreach ($invoices as $invoice) {
        $key = $invoice['status'];
        if (isset($data[$key])) {
            $data[$key] += round($invoice[$info['tax']], 2);
        } else {
            $data[$key] = round($invoice[$info['tax']], 2);
        }
        $sum += round($invoice[$info['tax']], 2);
    }
    ksort($data);

    // Prepare fill
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');

    $Datasets[] =& Image_Graph::factory('dataset');
    foreach ($data as $status => $total) {
        $Datasets[0]->addPoint(Minerva::getStatusName($status), ($total/$sum)*100);
        $r = rand(128, 255);
        $g = rand(128, 255);
        $b = rand(128, 255);
        $FillArray->addColor('#' . dechex($r) . dechex($g) . dechex($b));
    }

    // create the 1st plot as smoothed area chart using the 1st dataset
    $Plot =& $Plotarea->addNew('Image_Graph_Plot_Pie', array($Datasets));
    $Plot->Radius = 5;
    $Plot->setLineColor('gray');
    $Plot->setFillStyle($FillArray);
    $Plot->explode(10);
    $Plotarea->hideAxis();

    // create marker
    $Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
    $Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.2f%%'));
    $Plot->setMarker($Marker);

    // output the Graph
    $graph_image = Minerva_Graph::render($graph);
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

if (isset($graph_image)) {
    echo $graph_image;
    echo '<div style="text-align: right; width: 300px; padding-bottom: 5px;">';
    foreach ($data as $status => $total) {
        echo Minerva::getStatusName($status) . ': ' . Minerva::format_price($total) . '<br />';
    }
    echo _("Total") . ': <strong>' . Minerva::format_price($sum) . '</strong></div>';
}

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
