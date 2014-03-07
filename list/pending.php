<?php
/**
 * Print pending invoices list per client
 *
 * $Horde: incubator/minerva/list/pending.php,v 1.19 2009/12/01 12:52:44 jan Exp $
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

// Check statuses premission
if (!Minerva::hasStatusPermission('pending', Horde_Perms::SHOW, 'invoice')) {
    $notification->push(sprintf(_("You don't have permisson to access status %s."), 'any'), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Get selected invoices data
$title = _("Pending invoices list per customer");
$pending_url = Horde::applicationUrl('list/pending.php');

// Prepare the send from
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'penging');
$form->setButtons(_("Print reminder"));

$action_submit = Horde_Form_Action::factory('submit');
$dparam = array('start_year' => $minerva_invoices->getMinYear(),
                'end_year' => date('Y'),
                'picker' => false,
                'format_in' => '%Y-%m-%d');

$today = date('Y-m-d');
$v = &$form->addVariable(_("Date"), 'date', 'monthdayyear', true, false, false, $dparam);
$v->setDefault($today);
$v->setAction($action_submit);
$v->setOption('trackchange', true);

$criteria = array();
$criteria['invoice']['status'] = array('pending');
$criteria['invoice']['type'] = 'invoice';

if ($vars->get('date')) {
    $date = $vars->get('date');
    $criteria['invoice']['dateto'] = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
} else {
    $vars->set('date', $today);
    $criteria['invoice']['dateto'] = $today;
}

$list = array();
foreach ($minerva_invoices->getClients($criteria) as $vat => $client) {
    $list[$vat] = $client['name'] . ' - ' . Minerva::format_price($client['total']) . ' ( ' . $client['invoices'] . ' ) ';
}

$form->addVariable(_("Client"), 'client_vat', 'radio', true, false, false, array($list, true));

if ($form->validate()) {

    $form->getInfo($vars, $info);

    $criteria['clients']['vat'] = $info['client_vat'];
    $invoices = $minerva_invoices->getList($criteria);
    if (empty($invoices)) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . $pending_url);
        exit;
    }

    $invoice = $minerva_invoices->getOne($invoices[0]['invoice_id']);

    $convert = Minerva_Convert::factory('Pending');
    $filename = $convert->convert($invoice['client'], $invoices, $info['date']);
    if ($filename instanceof PEAR_Error) {
        echo $filename->getMessage();
        exit;
    }

    readfile($filename);
    exit;
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, $pending_url, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
