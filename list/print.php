<?php
/**
 * Print invoice list
 *
 * $Horde: incubator/minerva/list/print.php,v 1.10 2009/11/09 19:58:37 duck Exp $
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

// Get selected invoices data
$list = $minerva_invoices->getList($criteria);
$statuses = Minerva::getStatuses();

// $notification->push('window.onload=window.print()', 'javascript');
require MINERVA_TEMPLATES . '/common-header.inc';
echo Horde::includeStylesheetFiles();

$total = $total_tax = $total_bare = 0;
require MINERVA_TEMPLATES . '/list/header.inc';
foreach ($list as $invoice) {
    require MINERVA_TEMPLATES . '/list/row_print.inc';
    if (!isset($totals[$invoice['status']])) {
        $totals[$invoice['status']] = 0;
    }
    $totals[$invoice['status']] += $invoice['total'];
    $total_tax += $invoice['tax'];
    $total_bare += $invoice['total_bare'];
}
require MINERVA_TEMPLATES . '/list/footer.inc';

require $registry->get('templates', 'horde') . '/common-footer.inc';
