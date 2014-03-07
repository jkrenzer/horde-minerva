<?php
/**
 * Cobmine many invoices in once.
 * For example when you would like to make in invoice from many profromas.
 *
 * $Horde: incubator/minerva/list/combine.php,v 1.19 2009/12/01 12:52:44 jan Exp $
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

// Get selected invoices data
$list = Minerva::getList();

// Prepare the send from
$title = _("Create new document from several others");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'printall');
$form->setButtons(_("Create"));

// Fill from
$main = array();
foreach ($list as $invoice_id => $invoice_data) {
    $main[$invoice_id] = $invoice_data['name'];
}

$v = $form->addVariable(_("New type"), 'new_type', 'enum', true, false, false, array(Minerva::getTypes(Horde_Perms::EDIT)));
$v->setDefault('invoice');

$form->addVariable(_("New status"), 'new_status', 'enum', true, false, false, array(Minerva::getStatuses(Horde_Perms::EDIT)));
$form->addVariable(_("Get main data from"), 'main', 'enum', true, false, false, array($main));
$v = $form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array($list, Minerva_Invoices::getListHeaders()));
$v->setDefault(array_keys($list));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/combine.php'));
        exit;
    }

    // Get invoces data
    $invoices = $minerva_invoices->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('list/combine.php'));
        exit;
    }

    // Any selected?
    if (empty($invoices)) {
        $notification->push(_("No invoices are selected"), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/combine.php'));
        exit;
    }

    $invoice = $minerva_invoices->getOne($info['main']);
    if ($invoice instanceof PEAR_Error) {
        $notification->push($invoice);
        header('Location: ' . Horde::applicationUrl('list/combine.php'));
        exit;
    }

    // unset uneeded
    unset($invoice['invoice']['id'],
          $invoice['invoice']['name'],
          $invoice['invoice']['without_tax'],
          $invoice['taxes'],
          $invoice['currencies']);

    // Set invoice data
    $invoice['invoice']['date'] = date('Y-m-d');
    $invoice['invoice']['type'] = $info['new_type'];
    $invoice['invoice']['status'] = $info['new_status'];

    $invoice['articles'] = array();
    foreach ($invoices as $invoice_id => $invoice_data) {
        $invoice['articles'] = array_merge($invoice['articles'], $invoice_data['articles']);
    }

    $invoice_id = $minerva_invoices->save($invoice);
    if ($invoice_id instanceof PEAR_Error) {
        $notification->push($invoice_id);
    } else {
        $msg = sprintf(_("%s successfuly saved."), Minerva::getTypeName($info['new_type']));
        $notification->push($msg, 'horde.success');
        $url = Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'),
                                  array('invoice_id' => $invoice_id, 'type' => $info['new_type']), null, false);
        header('Location: ' . $url);
        exit;
    }
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
