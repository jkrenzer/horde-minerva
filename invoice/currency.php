<?php
/**
 * Managa invoce currency
 *
 * $Horde: incubator/minerva/invoice/currency.php,v 1.21 2009/12/01 12:52:46 jan Exp $
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

require_once HORDE_BASE . '/incubator/Horde_Currencies/Form/Type/currency.php';
require_once HORDE_BASE . '/incubator/Horde_Currencies/UI/VarRenderer/currency_xhtml.php';

$invoice_id = (int)Horde_Util::getFormData('invoice_id', 0);
$invoice = $minerva_invoices->getOne($invoice_id);
if ($invoice instanceof PEAR_Error) {
    $notification->push($invoice);
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$types = Minerva::getTypes(Horde_Perms::DELETE);
$type = $invoice['invoice']['type'];
if (!isset($types[$type])) {
    $notification->push(sprintf(_("You don't have permisson to access invoice type %s."), $type), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$url = Horde_Util::addParameter(Horde::applicationUrl('invoice/currency.php'), 'invoice_id', $invoice_id);
$title = sprintf(_("Menage currencies for %s %s"), $types[$type], $invoice['invoice']['name']);
$action_submit = Horde_Form_Action::factory('submit');
$default_curerrencies = Minerva::getCurrencies();

// know actions
$actions = array('set_default' => _("Set default currency"),
                 'update' => _("Update currency data from central currency storage"),
                 'add' => _("Add a new currency from central storage"),
                 'custom' => _("Add a custom currency"),
                 'edit' => _("Edit a currency of an invoice"),
                 'delete' => _("Delete currency from this invoice"));

$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'manage_currency');
$form->addHidden('', 'invoice_id', 'int', $invoice_id);
$form->setButtons(_("Continue"));

$desc = _("Please not that wrongly performed action can corrupt your invoice. So please, before continue consult your administrator.");
$form->addVariable($desc, 'description', 'description', false);

$v = $form->addVariable(_("Action"), 'action', 'enum', true, false, null, array($actions, true));
$v->setAction($action_submit);
$v->setOption('trackchange', true);

/* Add action attributes */
switch ($vars->get('action')) {
case 'delete':

    $currencies = array();
    foreach ($invoice['currencies'] as $key => $value) {
        if ($value['exchange_rate'] != 1) {
            $currencies[$key] = $value['currency_symbol'];
        }
    }

    if (empty($currencies)) {
        $notification->push(_("No currencies to delete."), 'horde.warning');
        header('Location: ' . $url);
        exit;
    }

    $form->addVariable(_("Currency"), 'currency', 'radio', true, false, false, array($currencies));
    break;

case 'add':

    $currencies = array();
    foreach ($default_curerrencies as $key => $value) {
        if (!isset($invoice['currencies'][$key])) {
            $currencies[$key] = $value['currency_symbol'];
        }
    }

    if (empty($currencies)) {
        $notification->push(_("No currencies to add."), 'horde.warning');
        header('Location: ' . $url);
        exit;
    }

    $form->addVariable(_("Currency"), 'currency', 'radio', true, false, false, array($currencies));
    break;

case 'custom':

    $currencies = new Horde_CurrenciesMapper();
    $fields = $currencies->model->listFields();

    $required = array('exchange_rate', 'int_curr_symbol', 'currency_symbol');
    foreach ($fields as $key) {
        if ($key == 'created' || $key == 'updated') {
            continue;
        }
        $form->addVariable($key, "custom[$key]", 'text', in_array($key, $required), false, false);
    }

    break;

case 'edit':

    $currencies = array();
    foreach ($invoice['currencies'] as $key => $value) {
        $currencies[$key] = $value['currency_symbol'];
    }

    $v = $form->addVariable(_("Currency"), 'currency', 'enum', true, false, false, array($currencies, true));
    $v->setAction($action_submit);
    $v->setOption('trackchange', true);

    if ($vars->get('currency')) {
        $currencies = new Horde_CurrenciesMapper();
        $fields = $currencies->model->listFields();
        $required = array('exchange_rate', 'int_curr_symbol');
        $old_values = $invoice['currencies'][$vars->get('currency')];
        foreach ($fields as $key) {
            if ($key == 'created' || $key == 'updated') {
                continue;
            }
            $v = $form->addVariable($key, "edit[$key]", 'text', in_array($key, $required), false, false);
            if (isset($old_values[$key])) {
                $v->setDefault($old_values[$key]);
            }
        }
    }

    break;

case 'update':

    $currencies = array();
    foreach ($invoice['currencies'] as $key => $value) {
        if (isset($invoice['currencies'][$key])) {
            $currencies[$key] = $value['currency_symbol'];
        }
    }

    if (empty($currencies)) {
        $notification->push(_("No currencies to update."), 'horde.warning');
        header('Location: ' . $url);
        exit;
    }

    $form->addVariable(_("Currency"), 'currency', 'radio', true, false, false, array($currencies));
    break;

case 'set_default':

    $currencies = array();
    foreach ($invoice['currencies'] as $key => $value) {
        if ($value['exchange_rate'] != 1) {
            $currencies[$key] = $value['currency_symbol'];
        }
    }

    $v = $form->addVariable(_("Currency"), 'currency', 'enum', true, false, false, array($currencies, true));
    $v->setAction($action_submit);
    $v->setOption('trackchange', true);

    if ($vars->get('currency')) {
        foreach ($invoice['currencies'] as $key => $value) {
            if ($key != $vars->get('currency')) {
                $form->addVariable(_("Exchange"), 'currency_' . $key, 'currency', true);
            }
        }
    }

    $v = $form->addVariable(_("Recalculate prices"), 'recalculate', 'enum', true, false, false, array(array(_("No"), _("Yes")), true));
    $v->setAction($action_submit);
    $v->setOption('trackchange', true);

    if ($vars->get('recalculate')) {
        $form->addVariable(_("Exchange"), 'exchange', 'currency', true);
        $form->addVariable(_("Exchange operation"), 'exchange_operation', 'enum', true, false, false, array(array(_("Divide"), _("Multiply"))));
    }

    break;

}

/* Process the various actions */
if ($form->validate()) {
    $form->getInfo($vars, $info);

    switch ($info['action']) {

    case 'delete':

        unset($invoice['currencies'][$info['currency']]);
        break;

    case 'add':
        $invoice['currencies'][$info['currency']] = $default_curerrencies[$info['currency']];
        break;

    case 'custom':
        $new = $info['custom']['int_curr_symbol'];
        if (isset($invoice['currencies'][$new]) || isset($default_curerrencies[$new])) {
            $notification->push(sprintf("Currency %s already exits.", $new), 'horde.warning');
            header('Location: ' . $url);
            exit;
        }
        if ($info['custom']['exchange_rate'] == 1) {
            $notification->push(_("Cannot add custom currency directly as default."), 'horde.warning');
            header('Location: ' . $url);
            exit;
        }
        $invoice['currencies'][$new] = $info['custom'];
        break;

    case 'edit':

        $invoice['currencies'][$info['currency']] = $info['edit'];

        break;

    case 'update':

        // If updateing default currency, be sure to not overwrite the exhange rate
        if ($invoice['currencies'][$info['currency']]['exchange_rate'] == 1) {
            $default_curerrencies[$info['currency']]['exchange_rate'] = 1;
        }
        $invoice['currencies'][$info['currency']] = $default_curerrencies[$info['currency']];
        break;

    case 'set_default':

        // Remmber old default
        $old_default = '';
        foreach ($invoice['currencies'] as $key => $value) {
            if ($invoice['currencies'][$key]['exchange_rate'] == 1) {
                $old_default = $key;
                break;
            }
        }

        // Update exchange values
        foreach ($invoice['currencies'] as $key => $value) {
            if ($key == $info['currency']) {
                $invoice['currencies'][$key]['exchange_rate'] = 1;
            } else {
                $invoice['currencies'][$key]['exchange_rate'] = (string) $info['currency_' . $key];
            }
        }

        // Recalculate articles values?
        if (!isset($info['exchange'])) {
            break;
        }

        foreach ($invoice['articles'] as $key => $value) {
            if ($info['exchange_operation']) {
                $invoice['articles'][$key]['price'] = (string) $value['price'] / $info['exchange'];
            } else {
                $invoice['articles'][$key]['price'] = (string) $value['price'] * $info['exchange'];
            }
        }

        break;

    default:
        $notification->push(_("Unknown action"), 'horde.warning');
        header('Location: ' . $url);
        exit;
        break;
    }

    $result = $minerva_invoices->save($invoice, $invoice_id);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
        header('Location: ' . $url);
    } else {
        $notification->push(sprintf(_("%s successfuly saved."), Minerva::getTypeName($data['invoice']['type'])), 'horde.success');
        header('Location: ' . Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'invoice_id', $invoice_id));
    }

    exit;
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(new Horde_Form_Renderer(array('varrenderer_driver' => 'currency_xhtml')),
                    null, $url, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';

