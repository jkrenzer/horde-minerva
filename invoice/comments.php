<?php
/**
 * Invoice comments invoice
 *
 * $Horde: incubator/minerva/invoice/comments.php,v 1.15 2009/09/15 15:10:58 duck Exp $
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
if (!$minerva_invoices->exists($invoice_id)) {
    $notification->push(sprintf(_("Invoice id %s dosen't exists."), $invoice_id), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

if (!$registry->hasMethod('forums/doComments')) {
    $notification->push(_("Comments are disabled"), 'horde.warning');
    header('Location: ' . Horde_Util::addParameters(Horde::applicationUrl('invoice/invoice.php'), 'invoice_id', $invoice_id));
    exit;
}

$comments = $registry->call('forums/doComments', array('minerva', $invoice_id, 'commentCallback'));

require MINERVA_TEMPLATES . '/common-header.inc';

if (!empty($comments['threads'])) {
    echo $comments['threads'];
}

if (!empty($comments['comments'])) {
    echo $comments['comments'];
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
