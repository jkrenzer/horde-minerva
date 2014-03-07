<?php
/**
 * Minerva Expired invocies list.
 *
 * $Horde: incubator/minerva/statistics/expired.php,v 1.32 2009/11/09 19:58:38 duck Exp $
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
require_once MINERVA_BASE . '/lib/UI/Clients.php';

$title = _("Expired");
$self = Horde::applicationUrl('statistics/expired.php');
$drafts = new Minerva_EmailMapper();

$criteria = array();
$criteria['invoice']['type'] = 'invoice';
$criteria['invoice']['expire'] = 0;
$criteria['invoice']['status'] = array('pending');
$list = Minerva::getList($criteria);
if (empty($list)) {
    $notification->push(_("No past due invoices"));
    header('Location: ' . Horde::applicationUrl('statistics/statistics.php'));
    exit;
}

$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, _("Expired invoces"), 'expired');
$form->setButtons(_("Send"));

$form->addVariable(_("Subject"), 'subject', 'text', true);
$form->addVariable(_("From"), 'from', 'email', true);
$form->addVariable(_("Draft"), 'draft', 'enum', true, false, false, array('enum' => $drafts->getEnum()));
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));

if ($form->validate()) {

    $form->getInfo($vars, $info);

    $clients_ui = new Horde_UI_Clients();
    $clients = $clients_ui->getAll();

    $draft = $drafts->find($info['draft']);
    $mail = array();
    $mail['body'] = $draft['content'] . "\n\n" . $prefs->getValue('invoice_signature');

    foreach ($info['invoices'] as $invoice) {
        if (!isset($clients[$invoice['company_id']]) ||
            !isset($clients[$invoice['company_id']]['email'])) {
            $notification->push(sprintf(_("Email not found for client %s"), $invoice['company']), 'horde.warning');
            continue;
        }

        $mail['to'] = $clients[$invoice['company_id']]['email'];
        $mail['subject'] = sprintf($info['subject'], $invoice['invoice_id']);

        try {
            Minerva::sendMail($info['from'], $mail['to'], $mail['subject'], $mail['body']);
            $notification->push(sprintf(_("Expired mail sent to %s."), $to), 'horde.success');
            $minerva_invoices->log($invoice['invoice_id'], 'send_expire', $mail);
        } catch (Horde_Exception $e) {
            $notification->push(sprintf(_("Error sending to %s: %s"), $to, $e->getMessage()), 'horde.warning');
        }
    }

    header('Location: ' . $self);
    exit;

} elseif (!$form->isSubmitted()) {

    $vars->set('from', Minerva::getFromAddress());
    $vars->set('subject', _("Invoice no. %s"));
    $vars->set('invoices', array_keys($list));

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(null, null, $self, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
