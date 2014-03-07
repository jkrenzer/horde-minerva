<?php
/**
 * Delete and recurrance
 *
 * $Horde: incubator/minerva/recurrence/delete.php,v 1.17 2009/12/01 12:52:45 jan Exp $
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

// check permissions
if (!Minerva::hasRecurrencePermission(Horde_Perms::DELETE)) {
    $notification->push(_("You don't have permission to perform this action"), 'horde.waring');
    header('Location: ' . Horde::applicationUrl('recurrence/list.php'));
    exit;
}

$title = _("Are you sure you want to delete this recurrance?");
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'delete');
$form->setButtons(array(_("Delete"), _("Cancel")));

$recurrances = new Minerva_Recurrences();
$invoice_id = Horde_Util::getFormData('invoice_id');
$form->addHidden('', 'invoice_id', 'text', $invoice_id);

if ($form->validate()) {
    if (Horde_Util::getFormData('submitbutton') == _("Delete")) {
        $result = $recurrances->delete($invoice_id);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(_("Recurrance succesfuly deleted."), 'horde.success');
        }
    } else {
        $notification->push(_("Recurrance not deleted."));
    }

    header('Location: ' . Horde::applicationUrl('recurrence/list.php'));
    exit;
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(null, $vars, 'delete.php', 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
