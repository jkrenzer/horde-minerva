<?php
/**
 * Minerva Invoices list
 *
 * $Horde: incubator/minerva/list/list.php,v 1.49 2009/12/10 17:42:34 jan Exp $
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

// Check types premission
if (!Minerva::hasTypePermission($criteria['invoice']['type'], Horde_Perms::SHOW)) {
    $notification->push(sprintf(_("You don't have permisson to access type %s."), Minerva::getTypeName($criteria['invoice']['type'])), 'horde.warning');
    Horde::authenticationFailureRedirect();
}

// Switch to search if we jumping to a new search type
if (($submitbutton = Horde_Util::getPost('submitbutton')) !== null) {
    if ($submitbutton == _("Advanced Search") || $submitbutton == _("Basic Search")) {
        require MINERVA_BASE . '/list/search.php';
        exit;
    }
}

$invoice_url = Horde::applicationUrl('invoice/invoice.php');
$list_url = Horde::applicationUrl('list/list.php');
$imagedir = $registry->getImageDir('horde');
$page = Horde_Util::getGet('page');
$perpage = $prefs->getValue('invoice_perpage');
$statuses = Minerva::getStatuses(Horde_Perms::SHOW, $criteria['invoice']['type']);
$title = $types[$criteria['invoice']['type']];
$vars = Horde_Variables::getDefaultVariables();

$print_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/print.php'),
                                array('type' => 'html',
                                      'template' => $criteria['invoice']['type']));

$pager = new Horde_Ui_Pager('page',
                            $vars,
                            array('num' => $minerva_invoices->count($criteria),
                                  'url' => 'list/list.php',
                                  'page_count' => 10,
                                  'perpage' => $perpage));

$criteria['limit'] = array('from' => $page*$perpage, 'count' => $perpage);
$list = $minerva_invoices->getList($criteria);
if ($list instanceof PEAR_Error) {
    $notification->push($list);
    $list = array();
}

Horde::addScriptFile('popup.js', 'horde');
Horde::addScriptFile('tables.js', 'horde');
Horde::addScriptFile('prototype.js', 'horde');
Horde::addScriptFile('protomenu.js', 'minerva', true);

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';
echo $tabs->render($criteria['invoice']['type']);

if (empty($list)) {

    require MINERVA_TEMPLATES . '/list/empty.inc';

} else {

    require MINERVA_TEMPLATES . '/list/actions.inc';
    require MINERVA_TEMPLATES . '/list/header.inc';

    $total = $total_tax = $total_bare = 0;
    foreach ($list as $invoice) {

        // Do we need to exchange?
        if ($invoice['currency'] != Minerva::getDefaultCurrency()) {
            $invoice_currencies = $minerva_invoices->getCurrencies($invoice['invoice_id']);
            $exchange_rate = $invoice_currencies[Minerva::getDefaultCurrency()]['exchange_rate'];
            $invoice['total'] = $invoice['total'] / $exchange_rate;
            $invoice['total_bare'] = $invoice['total_bare'] / $exchange_rate;
            $invoice['tax'] = $invoice['tax'] / $exchange_rate;
        }

        require MINERVA_TEMPLATES . '/list/row.inc';
        if (!isset($totals[$invoice['status']])) {
            $totals[$invoice['status']] = 0;
        }

        $totals[$invoice['status']] += $invoice['total'];
        $total_tax += $invoice['tax'];
        $total_bare += $invoice['total_bare'];
    }
    require MINERVA_TEMPLATES . '/list/footer.inc';
    require MINERVA_TEMPLATES . '/list/pager.inc';

}

require $registry->get('templates', 'horde') . '/common-footer.inc';
