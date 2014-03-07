<?php
/**
 * Minerva Invoices list
 *
 * $Horde: incubator/minerva/list/taxes.php,v 1.19 2009/11/09 19:58:37 duck Exp $
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
require_once MINERVA_BASE . '/list/tabs.php';

// Get filter
$criteria = Minerva::getInvoiceCriteria();

$criteria['invoice']['type'] = 'invoice';
$list = $minerva_invoices->getList($criteria);
$print = Horde_Util::getGet('print', false);

$taxes = array();
foreach ($list as $item) {
    $invoice = $minerva_invoices->getOne($item['invoice_id']);
    foreach ($invoice['taxes'] as $id => $value) {
        if (!isset($taxes[$id])) {
            $taxes[$id] = $value;
            $taxes[$id]['count'] = 0;
        }
        $taxes[$id]['count'] += 1;
        $taxes[$id]['total'] += $value['total'];
    }
}

$title = _("Taxes");
if (Horde_Util::getGet('print')) {
    $notification->push('window.onload=window.print()', 'javascript');
    require MINERVA_TEMPLATES . '/common-header.inc';
    echo Horde::stylesheetLink('horde', 'print');
} else {
    Horde::addScriptFile('tables.js', 'horde');
    require MINERVA_TEMPLATES . '/common-header.inc';
    require MINERVA_TEMPLATES . '/menu.inc';
    echo $tabs->render();
    require MINERVA_TEMPLATES . '/list/taxes/actions.inc';
}

require MINERVA_TEMPLATES . '/list/taxes/header.inc';
foreach ($taxes as $id => $item) {
    $item['total'] = Minerva::format_price($item['total']);
    require MINERVA_TEMPLATES . '/list/taxes/row.inc';
}
require MINERVA_TEMPLATES . '/list/taxes/footer.inc';

require $registry->get('templates', 'horde') . '/common-footer.inc';
