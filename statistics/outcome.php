<?php
/**
 * Minerva outcome calculation
 *
 * $Horde: incubator/minerva/statistics/outcome.php,v 1.21 2009/11/09 19:58:38 duck Exp $
 *
 * TODO: Round values per Horde_Currencies fact digits
 *
 * $Horde: incubator/minerva/statistics/outcome.php,v 1.21 2009/11/09 19:58:38 duck Exp $
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

$title = _("Outcome graph");

$graph =& Minerva_Graph::getCanvas($title);
if ($graph instanceof PEAR_Error) {
    $notification->push($graph);
}

$form = Minerva_Graph::getDateRangeForm();

if ($form->validate()) {

    $form->getInfo(null, $info);
    $criteria = array('tests' => array(array('field' => 'recived', 'test' => '>=', 'value' => $info['from']),
                                       array('field' => 'recived', 'test' => '<=', 'value' => $info['to'])));
    $months = Minerva_Graph::getOutcomes($criteria, $info['tax']);
    if (empty($months)) {
        $notification->push(_("There are no outcomes representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::selfUrl());
        exit;
    }

    // create the dataset
    $Datasets[0] =& Image_Graph::factory('dataset');
    $Datasets[0]->setName(_("Outcome"));

    $outcome_total = 0;
    foreach ($months as $name => $value) {
        $Datasets[0]->addPoint($name, $value);
        $outcome_total += $value;
    }

    // create the plot as stacked area chart using the datasets
    $Plot =& $Plotarea->addNew('bar', array($Datasets, 'stacked'));
    $Plot->setLineColor('gray');

    // create marker
    $Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
    $Plot->setMarker($Marker);

    // create and populate the fillarray
    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    $FillArray->addColor('green@0.2');
    $Plot->setFillStyle($FillArray);
    $Plotarea->setFillColor('silver@0.3');

    // output the Graph
    $graph_image = Minerva_Graph::render($graph);
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

if (isset($graph_image)) {
    echo $graph_image;
    echo '<div style="text-align: right; width: 300px; padding-bottom: 5px;">';
    echo _("Total") . ': <strong>' . Minerva::format_price($outcome_total) . '</strong><br />';
    echo _("Avarge") . ': <strong>' . Minerva::format_price($income_total/count($months)) . '</strong></div>';
}

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
