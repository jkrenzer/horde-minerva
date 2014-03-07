<?php
/**
 * Reseller calculation
 *
 * $Horde: incubator/minerva/list/resellers.php,v 1.21 2009/11/09 19:58:37 duck Exp $
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

$resellers = new Minerva_Resellers();
$resellers_list = $resellers->getAll();

// Remove all resellers without link
if (!empty($resellers_list)) {
    foreach ($resellers_list as $reseller_id => $reseller_data) {
        if (empty($reseller_data['clients'])) {
            unset($resellers_list[$reseller_id]);
        }
    }
}

// Check if empty
if (empty($resellers_list)) {
    $notification->push(_("No resellers found."), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$title = _("Resellers");
$print = Horde_Util::getGet('print', false);
$reseller_id = Horde_Util::getGet('reseller_id', false);

// Trick to print only a selected reseller
if ($print && $reseller_id) {
    $resellers_list = array($resellers_list[$reseller_id]);
}

if ($print) {
    $notification->push('window.onload=window.print()', 'javascript');
    require MINERVA_TEMPLATES . '/common-header.inc';
    echo Horde::stylesheetLink('horde', 'print');
} else {
    Horde::addScriptFile('stripe.js', 'horde');
    require MINERVA_TEMPLATES . '/common-header.inc';
    require MINERVA_TEMPLATES . '/menu.inc';
}

$criteria = Minerva::getCriteria();
$criteria['invoice']['type'] = 'invoice';
foreach ($resellers_list as $reseller => $reseller_data) {
    require MINERVA_TEMPLATES . '/list/resellers/actions.inc';
    require MINERVA_TEMPLATES . '/list/resellers/header.inc';
    $criteria['resellers'] = array($reseller);
    $list = Minerva::getList($criteria);
    if (empty($list)) {
        require MINERVA_TEMPLATES . '/list/resellers/empty.inc';
        continue;
    }
    $total_revenue = $total = $total_tax = $total_bare = 0;
    foreach ($list as $invoice) {
        $revenue = ($resellers_list[$reseller]['clients'][$invoice['company_id']]/100)*$invoice['total_bare'];
        $total_revenue += $revenue;
        $total += $invoice['total'];
        $total_tax += $invoice['tax'];
        $total_bare += $invoice['total_bare'];
        require MINERVA_TEMPLATES . '/list/resellers/row.inc';
    }
    require MINERVA_TEMPLATES . '/list/resellers/footer.inc';
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
