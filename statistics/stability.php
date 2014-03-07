<?php
/**
 * Minerva stablity graph
 *
 * TODO: Round values per Horde_Currencies fact digits
 *
 * $Horde: incubator/minerva/statistics/stability.php,v 1.19 2009/11/09 19:58:38 duck Exp $
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

$title = _("Stability graph");

$graph =& Minerva_Graph::getCanvas($title);
if ($graph instanceof PEAR_Error) {
    $notification->push($graph);
}

$form = Minerva_Graph::getDateRangeForm();

$v = $form->addVariable(_("Type"), 'type', 'enum', true, false, false, array(Minerva::getTypes()));
$v->setDefault('invoice');

$statuses = Minerva::getStatuses();
$v = &$form->addVariable(_("Status"), 'status', 'set', true, false, false, array($statuses));
$v->setDefault(array_keys($statuses));

if ($form->validate()) {

    $form->getInfo(null, $info);

    /* Get Outcome */
    $criteria = array('tests' => array(array('field' => 'recived', 'test' => '>=', 'value' => $info['from']),
                                       array('field' => 'recived', 'test' => '<=', 'value' => $info['to'])));
    $outcome_months = Minerva_Graph::getOutcomes($criteria, $info['tax']);
    if (empty($outcome_months)) {
        $notification->push(_("There are no outcomes representing selected criteria."), 'horde.warning');
    }

    /* Get income */
    $criteria = array('invoice' => array('type' => $info['type'],
                                         'status' => $info['status'],
                                         'dateto' => $info['to'],
                                         'datefrom' => $info['from']));

    $income_months = array();
    $invoices = Minerva_Graph::getList($criteria);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        $invoices = array();
    }
    foreach ($invoices as $invoice) {
        $key = substr($invoice['date'], 0, 7);
        if (isset($income_months[$key])) {
            $income_months[$key] += round($invoice[$info['tax']], 2);
        } else {
            $income_months[$key] = round($invoice[$info['tax']], 2);
        }
    }
    ksort($income_months);

    // create the dataset
    $Datasets[0] =& Image_Graph::factory('dataset');
    $Datasets[0]->setName(_("Outcome"));
    $Datasets[1] =& Image_Graph::factory('dataset');
    $Datasets[1]->setName(Minerva::getTypeName($info['type']));
    $Datasets[2] =& Image_Graph::factory('dataset');
    $Datasets[2]->setName(_("Difference"));

    // Fill dataset
    $outcome_total = 0;
    foreach ($outcome_months as $name => $value) {
        $Datasets[0]->addPoint($name, $value);
        $outcome_total += $value;
    }
    $income_total = 0;
    foreach ($income_months as $name => $value) {
        $Datasets[1]->addPoint($name, $value);
        $income_total += $value;
        if (isset($outcome_months[$name])) {
            $Datasets[2]->addPoint($name, $value - $outcome_months[$name]);
        } else {
            $Datasets[2]->addPoint($name, $value);
        }
    }

    // create the 1st plot as smoothed area chart using the 1st dataset
    $Plot =& $Plotarea->addNew('bar', array($Datasets));
    $Plot->setLineColor('gray');

    // set a line color
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    $FillArray->addColor('yellow@0.2');
    $FillArray->addColor('blue@0.2');
    $FillArray->addColor('red@0.2');
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
    echo _("Outcome") . ': ' . Minerva::format_price($outcome_total) . '<br />';
    echo Minerva::getTypeName($info['type']) . ': ' . Minerva::format_price($income_total) . '<br />';
    echo _("Difference") . ': <strong>' . Minerva::format_price($income_total - $outcome_total) . '</strong></div>';
}

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
