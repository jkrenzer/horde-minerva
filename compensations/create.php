<?php
/**
 * Create compensations
 *
 * $Horde: incubator/minerva/compensations/create.php,v 1.23 2009/12/01 12:52:45 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/Outcome.php';

// Check types premission
if (!Minerva::hasTypePermission('compensation', Horde_Perms::EDIT)) {
    $notification->push(sprintf(_("You don't have permisson to access type %s."), Minerva::getTypeName('compensation')), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Check statuses premission
if (!Minerva::hasStatusPermission(null, Horde_Perms::EDIT, 'compensation')) {
    $notification->push(sprintf(_("You don't have permisson to access status %s."), 'any'), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$title = _("Compensations");
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'invoice');

$client_url = sprintf("<a href=\"#\" onclick=\"Horde.popup({ url: '%s' })\">%s</a>", Horde::applicationUrl('client_selection.php'), _("Select client"));
$form->addVariable(_("Vat"), 'client_vat', 'text', true, false, $client_url, array('', 10, 10));
$form->setButtons(array(_("Search")));

if ($vars->get('client_vat')) {

    $form->setButtons(array(_("Create comensation")));

    // Get invoice published to client
    $criteria = array('invoice' => array('type' => 'invoice', 'status' => array('pending')));
    $income_list = Minerva_Form_List::getList($criteria);
    if (empty($income_list)) {
        $notification->push(sprintf(_("No invoices published to client with vat %s exists."), $vars->get('client_vat')), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('compensations/create.php'));
        exit;
    }

    $form->addVariable(_("Income"), 'income', 'tableset', true, false, false, array('values' => $income_list, 'headers' => Minerva_Invoices::getListHeaders()));

    // Get invoices recived from client
    $outcomes = new Minerva_OutcomeMapper();
    $criteria = array('tests' => array(array('field' => 'client_vat', 'test' => '=', 'value' => $vars->get('client_vat'))));
    $outcome_rawlist = $outcomes->getAll($criteria);
    if (empty($outcome_rawlist)) {
        $notification->push(sprintf(_("No outcomes recived for client with vat %s exists."), $vars->get('client_vat')), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('compensations/create.php'));
        exit;
    }

    // Format outcomes
    $use_keys = array();
    $outcome_list = array();
    foreach ($outcome_rawlist as $id => $values) {
        $outcome_list[$id]['id'] = $values['id'];
        $outcome_list[$id]['client_name'] = nl2br($values['client_name']);
        $outcome_list[$id]['intend'] = nl2br($values['intend']);
        $outcome_list[$id]['due'] = Minerva::format_date($values['due']) . ' (' . Minerva::expireDate($values['due']) . ')';
        $outcome_list[$id]['total'] = Minerva::format_price($values['total']);
        $outcome_list[$id]['total_tax'] = Minerva::format_price($values['total_tax']);
        $outcome_list[$id]['total_bare'] = Minerva::format_price($values['total'] - $values['total_tax']);
    }
    $form->addVariable(_("Outcome"), 'outcome', 'tableset', true, false, false, array('values' => $outcome_list, 'headers' => Minerva_Invoices::getListHeaders()));

    // Validate form
    if (Horde_Util::getFormData('submitbutton') == _("Create comensation") && $form->validate()) {
        $form->getInfo($vars, $info);

        if (empty($info['income'])) {
            $notification->push(_("No invoices published to client are selected."), 'horde.warning');
            header('Location: ' . Horde::applicationUrl('compensations/create.php'));
            exit;
        }

        if (empty($info['outcome'])) {
            $notification->push(_("No outcomes recived for client are selected."), 'horde.warning');
            header('Location: ' . Horde::applicationUrl('compensations/create.php'));
            exit;
        }

        // Get out client data
        $oldinvoice = $minerva_invoices->getOne(current($info['income']));
        if ($oldinvoice instanceof PEAR_Error) {
            $notification->push($oldinvoice);
            header('Location: ' . Horde::applicationUrl('compensations/create.php'));
            exit;
        }

        // Prepare invoice data
        $statuses = Minerva::getStatuses(Horde_Perms::EDIT, 'compensation');

        $invoice = array();
        $invoice['articles'] = array();
        $invoice['client'] = $oldinvoice['client'];
        $invoice['invoice'] = array('date' => date('Y-m-d'),
                                    'service' => strftime($prefs->getValue('date_format')),
                                    'status' => key($statuses),
                                    'place' => Minerva::getInvoicePlace(),
                                    'type' => 'compensation');

        // Add publised invoices
        foreach ($info['income'] as $id) {
            $invoice['articles'][] = array('id'  => 'income/' . $id,
                                           'name' => $income_list[$id]['name'],
                                           'price' => $income_list[$id]['total_bare'],
                                           'qt' => 1,
                                           'discount' => 0,
                                           'tax' => 0,
                                           'total' => $income_list[$id]['total']);
        }

        // Add recived invoices
        foreach ($info['outcome'] as $id) {
            $invoice['articles'][] = array('id'  => 'outcome/' . $id,
                                           'name' => $id,
                                           'price' => $outcome_list[$id]['total_bare'],
                                           'qt' => 1,
                                           'discount' => 0,
                                           'tax' => 0,
                                           'total' => $outcome_list[$id]['total']);
        }

        $compensations = new Minerva_Compensations();
        $inovice_id = $compensations->save($invoice);
        if ($inovice_id instanceof PEAR_Error) {
            $notification->push($inovice_id);
        } else {
            $notification->push(sprintf(_("%s successfuly saved."), _("Compensation")), 'horde.success');
            header('Location: ' . Horde_Util::addParameter(Horde::applicationUrl('list/list.php'), 'type', 'compensation', false));
            exit;
        }
    }

}

Horde::addScriptFile('popup.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(new Horde_Form_Renderer(array('varrenderer_driver' => 'tableset_html')),
                    $vars, Horde::applicationUrl('compensations/create.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
