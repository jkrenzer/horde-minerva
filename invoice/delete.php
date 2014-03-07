<?php
/**
 * Delete Invoce
 *
 * $Horde: incubator/minerva/invoice/delete.php,v 1.23 2009/12/01 12:52:46 jan Exp $
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

// Check if invoice exits
$invoice_id = (int)Horde_Util::getFormData('invoice_id', 0);
if (!$minerva_invoices->exists($invoice_id)) {
    $notification->push(sprintf(_("Invoice id %s dosen't exists."), $invoice_id), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Check if we have permission on it
$types = Minerva::getTypes(Horde_Perms::DELETE);
$invoice = $minerva_invoices->getOne($invoice_id);
if ($result instanceof PEAR_Error) {
    $notification->push($result);
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$type = $invoice['invoice']['type'];
if (!isset($types[$type])) {
    $notification->push(sprintf(_("You don't have permisson to access invoice type %s."), $type), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Process
$vars = Horde_Variables::getDefaultVariables();
$title = sprintf(_("Do you really want to delete %s %s?"), $types[$type], $invoice['invoice']['name']);
$form = new Horde_Form($vars, _("Delete"), 'delete');
$form->addVariable($title, 'description', 'description', true);
$form->setButtons(array(_("Continue"), _("Cancel")));

if ($form->validate()) {
    if (Horde_Util::getFormData('submitbutton') == _("Continue")) {
        $result = $minerva_invoices->delete($invoice_id);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(sprintf(_("%s %s deleted"), $types[$type], $invoice['invoice']['name']), 'horde.success');
        }
    }
    header('Location: ' . Horde_Util::addParameter(Horde::applicationUrl('list/list.php'), 'type', $type));
    exit;
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$invoice_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/delete.php'), 'invoice_id', $invoice_id, false);
$form->renderActive(null, null, $invoice_url, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
