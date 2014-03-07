<?php
/**
 * Insert invoice to online payment
 *
 * $Horde: incubator/minerva/invoice/addtopayment.php,v 1.14 2009/09/15 15:10:58 duck Exp $
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

$invoice_id = Horde_Util::getFormData('invoice_id', 0);
$invoice_url  = Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'invoice_id', $invoice_id, false);

if (!$registry->hasMethod('payment/authorizationRequest')) {
    $notification->push(_("Payments application not available"), 'horde.warning');
    header('Location: ' . $invoice_url);
    exit;
}

if (!$minerva_invoices->exists($invoice_id)) {
    $notification->push(sprintf(_("Invoice id %s dosen't exists."), $invoice_id), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$invoice = $minerva_invoices->getOne($invoice_id);
if ($invoice instanceof PEAR_Error) {
    $notification->push($invoice);
    header('Location: ' . $invoice_url);
    exit;
}

// Add invoice to the payment mechanisem
$authorizationID = $registry->call('payment/authorizationRequest',
                                   array('minerva', $invoice_id, $invoice['invoice']['total']));

if ($authorizationID instanceof PEAR_Error) {
    $notification->push($authorizationID);
} else {
    $link = Horde::applicationUrl($registry->link('payment/show', array('id' => $authorizationID)), true, -1);
    $msg = sprintf(_("Invoice %s successfully added to the payment system. For on-line payment redirect your customer to %s"), $invoice['invoice']['name'], $link);
    $notification->push($msg, 'horde.success');
}

header('Location: ' . $invoice_url);
exit;
