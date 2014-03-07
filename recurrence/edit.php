<?php
/**
 * Edit a recurrance
 *
 * $Horde: incubator/minerva/recurrence/edit.php,v 1.30 2009/12/01 12:52:45 jan Exp $
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

// Check permissions
if (!Minerva::hasRecurrencePermission(Horde_Perms::EDIT)) {
    $notification->push(_("You don't have permission to perform this action"), 'horde.waring');
    header('Location: ' . Horde::applicationUrl('recurrence/list.php'));
    exit;
}

$title = _("Edit");

$recurrances = new Minerva_Recurrences();

$invoice_id = Horde_Util::getFormData('invoice_id', 0);
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'edit');
$form->setButtons(_("Add to recurrence"));

// Load recurrance
// Autofill invoice name if id is passed
if ($invoice_id) {
    $recurrance = $recurrances->getOne($invoice_id);
    if (!empty($recurrance)) {
        if (substr($recurrance['rstart'], 0, 4) != '0000') {
            $vars->set('recurrence_select', 2);
        }
        if ($recurrance['rend'] == 0) {
            $vars->set('end_select', 3);
        } elseif (strlen($recurrance['rend']) == 1) {
            $vars->set('end_select', 2);
        } else {
            $vars->set('end_select', 1);
        }
        foreach ($recurrance as $key => $value) {
            if (!empty($value) && $vars->exists($key)) {
                $vars->set($key, $value);
            }
        }
    }
    if ($vars->get('invoice_name') === null) {
        $vars->set('invoice_name', $minerva_invoices->getName($invoice_id));
    }
}

// Invoice data
$action_submit = Horde_Form_Action::factory('submit');
$form->addHidden('', 'invoice_id', 'int', $invoice_id);
$form->addVariable(_("Invoice data"), 'invoice_header', 'header', false);
$form->addVariable(_("Name"), 'invoice_name', 'text', true, Horde_Util::getGet('invoice_id'));
$form->addVariable(_("Description"), 'description', 'text', true, false, _("A descriptive invoice title which will be shown in the recurrence list."));
$v = &$form->addVariable(_("Copy articles"), 'articles', 'boolean', false);
$v->setDefault(true);
$v = &$form->addVariable(_("Copy client"), 'client', 'boolean', false);
$v->setDefault(true);

// This is just a manual recurrence or we should set up an automatic one
$form->addVariable(_("Recurrence"), 'recurrence_header', 'header', false);
$v = &$form->addVariable(_("Recurrence"), 'recurrence_select', 'radio', true, false, null,
                        array(array(1 => _("Has no recurrence"), 2 => _("Has recurrence")), true));
$v->setAction($action_submit);
$v->setOption('trackchange', true);

if ($vars->get('recurrence_select') == 2) {

    // Status of the new generated invoice
    $enum = Minerva::getStatuses(Horde_Perms::EDIT);
    $v = &$form->addVariable(_("Status"), 'rstatus', 'enum', true, false, _("New status"), array($enum));
    $v->setDefault('pending');

    // Mail settings
    $drafts = new Minerva_EmailMapper();
    $enum = $drafts->getEnum();
    if (!empty($enum)) {
        $form->addVariable(_("Send to"), 'sendto', 'email', false);
        $form->addVariable(_("Draft"), 'draft', 'enum', false, false, null, array($enum, true));
    }
    // When to start
    $v = &$form->addVariable(_("Start recurrence on"), 'rstart', 'monthdayyear', true, false, _("Day when the invoice will be duplicated frist."));
    $v->setDefault($_SERVER['REQUEST_TIME']);

    // Interval
    $v = &$form->addVariable(_("Interval"), 'rinterval', 'int', true, false, _("Number of days between recurrence."));
    $v->setDefault(30);

    // When to end
    $enum = array(1 => _("on a specific date"),
                  2 => _("after a number of recurrences"),
                  3 => _("never ends"));
    $v = &$form->addVariable(_("End recurrence"), 'end_select', 'enum', true, false, null, array($enum, true));
    $v->setAction($action_submit);
    $v->setOption('trackchange', true);

    switch ($vars->get('end_select')) {
        case 1:
            $v = &$form->addVariable(_("Date to end"), 'rend', 'monthdayyear', true);
            $rstart = $vars->get('rstart');
            $rend = $vars->get('rend');
            if (!empty($rstart['year']) && empty($rstart['rend'])) {
                $date_in = sprintf('%04d-%02d-%02d', $rstart['year'] + 1, $rstart['month'], $rstart['day']);
                $vars->set('rend', $date_in);
            }
            break;
        case 2:
            $v = &$form->addVariable(_("Number of recurrences"), 'rend', 'int', true);
            $v->setDefault(12);
            break;
        case 3:
            $form->addHidden('', 'rend', 'int', 0);
            break;
    }
}

if ($form->validate()) {
    $form->getInfo(null, $info);

    $result = $recurrances->save($info);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Recurrence successfully saved"), 'horde.sucess');
        header('Location: ' . Horde::applicationUrl('recurrence/list.php'));
        exit;
    }
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
