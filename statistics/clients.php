<?php
/**
 * Minerva clients calculation
 *
 * $Horde: incubator/minerva/statistics/clients.php,v 1.16 2009/11/09 19:58:38 duck Exp $
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

$title = _("Clients graph");

$graph =& Minerva_Graph::getCanvas($title);
if ($graph instanceof PEAR_Error) {
    $notification->push($graph);
}

$form = Minerva_Graph::getDateRangeForm();
$v = $form->addVariable(_("Type"), 'type', 'enum', true, false, false, array(Minerva::getTypes()));
$v->setDefault('invoice');

$statuses = Minerva::getStatuses();
$v = &$form->addVariable(_("Status"), 'status', 'set', true, false, false, array($statuses));
$v->setDefault(array('pending'));

if ($form->validate()) {

    $form->getInfo(null, $info);
    $criteria = array('invoice' => array('type' => $info['type'],
                                         'status' => $info['status'],
                                         'dateto' => $info['to'],
                                         'datefrom' => $info['from']));

    $data = array();
    $invoices = Minerva_Graph::getList($criteria);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        $invoices = array();
    }

    // Currenty frac
    $currencies = Minerva::getCurrencies();
    $currency = Minerva::getDefaultCurrency();;

    foreach ($invoices as $invoice) {
        $key = $invoice['company'];
        if (isset($data[$key])) {
            $data[$key] += round($invoice[$info['tax']], $currencies[$currency]['frac_digits']);
        } else {
            $data[$key] = round($invoice[$info['tax']], $currencies[$currency]['frac_digits']);
        }
    }
    asort($data);

    // create the dataset
    $Datasets[0] =& Image_Graph::factory('dataset');
    $Datasets[0]->setName(_("Income"));

    foreach ($data as $name => $value) {
        $Datasets[0]->addPoint($name, $value);
    }

    // create the plot as stacked area chart using the datasets
    $Plot =& $Plotarea->addNew('bar', array($Datasets, 'stacked'));
    $Plot->setLineColor('gray');

    // create and populate the fillarray
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    $FillArray->addColor('green@0.2');
    $Plot->setFillStyle($FillArray);
    $Plotarea->setFillColor('silver@0.3');

    // output the Graph
    $graph_image = Minerva_Graph::render($graph);
}

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

if (isset($graph_image)) {
    require MINERVA_TEMPLATES . '/statistics/totals.inc';
}

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
