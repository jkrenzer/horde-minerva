<?php
/**
 * Minerva Invoices list
 *
 * $Horde: incubator/minerva/notifies/send.php,v 1.14 2009/11/09 19:58:37 duck Exp $
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
require_once MINERVA_BASE . '/lib/Email.php';
require_once MINERVA_BASE . '/notifies/tabs.php';

// Get selected invoices data
$criteria = Minerva::getCriteria();
$criteria['invoice']['type'] = 'notify';
$list = Minerva::getList($criteria);
$title = sprintf(_("Send %d invoces"), count($list));

// Prepare the send from
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'send');
$form->setButtons(_("Send"), true);

// Fill from
$form->addVariable(_("Subject"), 'subject', 'text', true);
$form->addVariable(_("From"), 'from', 'email', true);
$form->addVariable(_("To"), 'to', 'email', true);
$form->addVariable(_("Body"), 'body', 'longtext', true);
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('notifies/send.php'));
        exit;
    }

    // Get invoces data
    $invoices = $notifies->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('notifies/send.php'));
        exit;
    }

    // Convert invocies
    $convert = Minerva_Convert::factory('notify');
    $files = array();
    foreach ($invoices as $invoice_id => $invoice_data) {
        $files[$invoice_id] = $convert->convert($invoice_id, $invoice_data);
        if ($files[$invoice_id] instanceof PEAR_Error) {
            $notification->push($files[$invoice_id]->getMessage(), 'horde.warning');
            header('Location: ' . Horde::applicationUrl('notifies/send.php'));
            exit;
        }
    }

    try {
        Minerva::sendMail($info['from'], $info['to'], $info['subject'], $info['body'], $files);
        $notification->push(sprintf(_("%s invoices send to %s"), sizeof($invoices), $info['to']), 'horde.success');
    } catch (Horde_Exception $e) {
        $notification->push(sprintf(_("Error sending to %s: %s"), $info['to'], $e->getMessage()), 'horde.warning');
    }

    header('Location: ' . Horde::applicationUrl('notifies/send.php'));
    exit;

} elseif (!$form->isSubmitted()) {

    $vars->set('from', Minerva::getFromAddress());
    $vars->set('subject', $title);
    $vars->set('body', $prefs->getValue('invoice_signature'));

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('notifies/send.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
