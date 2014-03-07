<?php
/**
 * Create new notifies
 *
 * $Horde: incubator/minerva/notifies/create.php,v 1.23 2009/12/01 12:52:44 jan Exp $
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
if (!Minerva::hasNotifiesPermission(Horde_Perms::EDIT)) {
    $notification->push(_("You don't have permisson to view notifies."), 'horde.warning');
    Horde::authenticationFailureRedirect();
}

$title = _("Create late payment notification");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'create_notify');

$days = (int)$vars->get('days');
$v = $form->addVariable(_("Expired by days"), 'days', 'int', false);
$v->setDefault($days);
$v->setAction(Horde_Form_Action::factory('submit'));

$invoices = $notifies->getOverdues($days);
if ($invoices instanceof PEAR_Error) {
    $notification->push($invoices);
    header('Location: ' . Horde::applicationUrl('notifies/list.php'));
    exit;
}

$v = $form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array($invoices, $notifies->getListHeaders()));
$v->setDefault(array_keys($invoices));

if ($form->validate()) {
    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('notifies/create.php'));
        exit;
    }

    /* Find different clients */
    $clients = array();
    foreach ($info['invoices'] as $invoice_id) {
        $invoice_data = $invoices[$invoice_id];
        $id = $minerva_invoices->findClientOwner($invoice_data['vat'], $invoice_data['client'], $clients);

        if ($id !== false) {
            $clients[$id]['invoices'][] = $invoice_id;
        } else {
            $clients[] = array('vat' => $invoice_data['vat'],
                               'name' => $invoice_data['client'],
                               'invoices' => array($invoice_id));
        }
    }

    /* Create notify */
    foreach ($clients as $client_data) {
        $result = $notifies->create($client_data['invoices']);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $minerva_invoices->log($client_data['invoices'], 'notify');
            $notification->push(sprintf(_("Late payment notification created for client %s"), $client_data['name']), 'horde.success');
        }
    }

    header('Location: ' . Horde::applicationUrl('notifies/list.php'));
    exit;
}

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('create');
$form->renderActive(null, null, null, 'post');

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
