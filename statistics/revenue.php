<?php
/**
 * Minerva ravenue calculation
 *
 * $Horde: incubator/minerva/statistics/revenue.php,v 1.18 2009/11/09 19:58:38 duck Exp $
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

$title = _("Revenue");

$form = Minerva_Graph::getDateRangeForm(false);

if ($form->validate()) {

    $form->getInfo(null, $info);
    $criteria = array('invoice' => array('type' => 'invoice',
                                         'dateto' => $info['to'],
                                         'datefrom' => $info['from']));

    $inc_total = $inc_total_tax = $inc_total_bare = $inc_count = 0;
    foreach ($minerva_invoices->getList($criteria) as $invoice) {
        $inc_total += $invoice['total'];
        $inc_total_tax += $invoice['tax'];
        $inc_total_bare += $invoice['total_bare'];
        $inc_count++;
    }

    $outcomes = new Minerva_OutcomeMapper();
    $criteria = array('tests' => array(array('field' => 'recived', 'test' => '>=', 'value' => $info['from']),
                                       array('field' => 'recived', 'test' => '<=', 'value' => $info['to'])));

    $out_total = $out_total_tax = $out_total_bare = $out_count = 0;
    foreach ($outcomes->getAll($criteria) as $invoice) {
        $out_total += $invoice['total'];
        $out_total_tax += $invoice['total_tax'];
        $out_total_bare += $invoice['total'] - $invoice['total_tax'];
        $out_count++;
    }
}

Horde::addScriptFile('stripe.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

if ($form->isSubmitted()) {
    require MINERVA_TEMPLATES . '/statistics/revenue.inc';
}

$form->renderActive(null, null, Horde::applicationUrl('statistics/revenue.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
