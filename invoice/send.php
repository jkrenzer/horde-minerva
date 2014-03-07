<?php
/**
 * Send invoice by email
 *
 * $Horde: incubator/minerva/invoice/send.php,v 1.47 2009/11/09 19:58:36 duck Exp $
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

// Get and check if the invoice exists
$invoice_id = Horde_Util::getFormData('invoice_id', 0);
$invoice = $minerva_invoices->getOne($invoice_id);
if ($invoice instanceof PEAR_Error) {
    $notification->push(sprintf(_("Invoice id %s dosen't exists."), $invoice_id), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Check types premission
$types = Minerva::getTypes();
if (!isset($types[$invoice['invoice']['type']])) {
    $notification->push(sprintf(_("You don't have permisson to access invoice type %s."), $invoice['invoice']['type']), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$title = sprintf(_("Send %s: %s"), $types[$invoice['invoice']['type']], $invoice['invoice']['name']);
$vars = Horde_Variables::getDefaultVariables();
$formats = Horde_Array::valuesToKeys($conf['convert']['types']);
$form = new Horde_Form($vars, $title, 'send_invoice');
$form->setButtons($title);

$drafts = new Minerva_EmailMapper();
$mail_drafts = $drafts->getEnum();
if (!empty($mail_drafts)) {
    $action_submit = Horde_Form_Action::factory('submit');
    $v = &$form->addVariable(_("Draft"), 'draft', 'enum', false, false, false, array('enum' => $mail_drafts, 'prompt' => true));
    $v->setAction($action_submit);
    $v->setOption('trackchange', true);
}

$form->addHidden('', 'invoice_id', 'int', $invoice_id);
$form->addVariable(_("From"), 'from', 'email', true);
$form->addVariable(_("To"), 'to', 'email', true);
$form->addVariable(_("Subject"), 'subject', 'text', true);
$form->addVariable(_("Attach fromat"), 'formats', 'set', true, false, false, array('enum' => $formats));
$form->addVariable(_("Comment"), 'body', 'longtext', true);

if ($form->validate()) {

    $form->getInfo(null, $info);

    // Convert invoce to the needed formats
    $info['files'] = array();
    $convert = Minerva_Convert::factory();
    foreach ($info['formats'] as $id => $format) {
        $info['files'][$id] = $convert->convert($invoice_id, array(), null, $format);
        if ($info['files'][$id] instanceof PEAR_Error) {
            $notification->push($info['files'][$id]);
            header('Location: ' . Horde::applicationUrl('list/list.php'));
            exit;
        }
    }

    // Try to send it
    try {
        $mail = Minerva::sendMail($info['from'], $info['to'], $info['subject'], $info['body'], $info['files']);

        // add a comment and redirect him back to invoice
        $message = sprintf(_("%s mail send to %s."), $types[$invoice['invoice']['type']], $info['to']);
        $notification->push($message, 'horde.success');
        $minerva_invoices->log($invoice_id, 'send', $info);

        header('Location: ' . Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'invoice_id', $invoice_id));
        exit;
    } catch (Horde_Exception $e) {
        $notification->push($e);
    }

} elseif (!$form->isSubmitted()) {

    // We use a draft?
    if ($vars->get('draft')) {
        $draft = $drafts->find($vars->get('draft'));
        $body = $draft->content;
        $subject = $draft->subject;
    } else {
        $body = '';
        $subject = _("%s no. %s");
    }

    $subject = sprintf($subject, $types[$invoice['invoice']['type']], $invoice['invoice']['name']);
    $body .= "\n\n" . $prefs->getValue('invoice_signature') . "\n\n";

    // Try to find the client email
    $clients_ui = new Horde_UI_Clients();
    $client = $clients_ui->getOne($invoice['client']['id']);
    if ($client instanceof PEAR_Error) {
        $notification->push($client);
    } elseif (!empty($client['email'])) {
        $vars->set('to', $client['email']);
    }

    $vars->set('from', Minerva::getFromAddress());
    $vars->set('formats', $formats);
    $vars->set('body', $body);
    $vars->set('subject', $subject);
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

$form->renderActive(null, null, Horde::applicationUrl('invoice/send.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
