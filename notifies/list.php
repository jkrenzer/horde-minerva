<?php
/**
 * Notifies list
 *
 * $Horde: incubator/minerva/notifies/list.php,v 1.20 2009/12/10 17:42:34 jan Exp $
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
require_once MINERVA_BASE . '/notifies/tabs.php';

// Check types premission
if (!Minerva::hasNotifiesPermission(Horde_Perms::SHOW)) {
    $notification->push(_("You don't have permisson to view notifies."), 'horde.warning');
    Horde::authenticationFailureRedirect();
}

// Get filter
$criteria = Minerva::getInvoiceCriteria();

// Prepare data
$criteria['invoice']['type'] = 'notify';
$delete_url = Horde::applicationUrl('notifies/delete.php');
$imagedir = $registry->getImageDir('horde');
$page = Horde_Util::getGet('page');
$perpage = $prefs->getValue('invoice_perpage');
$statuses = Minerva::getStatuses(Horde_Perms::SHOW, $criteria['invoice']['type']);
$types = Minerva::getTypes();
$title = _("Notifies");
$vars = Horde_Variables::getDefaultVariables();

$print_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/print.php'),
                                array('type' => 'html',
                                      'template' => $criteria['invoice']['type']));

$pager = new Horde_Ui_Pager('page',
                            $vars,
                            array('num' => $minerva_invoices->count($criteria),
                                  'url' => 'notifies/list.php',
                                  'page_count' => 10,
                                  'perpage' => $perpage));

$criteria['limit'] = array('from' => $page*$perpage, 'count' => $perpage);
$list = $notifies->getList($criteria);
if ($list instanceof PEAR_Error) {
    $notification->push($list);
    $list = array();
}

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('list');

if (empty($list)) {

    require MINERVA_TEMPLATES . '/list/empty.inc';

} else {

    require MINERVA_TEMPLATES . '/notifies/actions.inc';
    require MINERVA_TEMPLATES . '/list/header.inc';

    $total = $total_tax = $total_bare = 0;
    foreach ($list as $invoice) {
        require MINERVA_TEMPLATES . '/notifies/row.inc';
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

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
