<?php
/**
 * Chagne invoice status of invoices
 *
 * $Horde: incubator/minerva/notifies/changestatus.php,v 1.16 2009/12/01 12:52:44 jan Exp $
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
require_once MINERVA_BASE . '/notifies/tabs.php';

$statuses = Minerva::getStatuses(Horde_Perms::EDIT);
if (empty($statuses)) {
    $notification->push(sprintf(_("You don't have permisson to access status %s."), 'any'), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('notifies/list.php'));
    exit;
}

// Get selected invoices data
$criteria = Minerva::getCriteria();
$criteria['invoice']['type'] = 'notify';
$list = Minerva::getList($criteria);
$type_name = _("Notifies");
$title = _("Changes status");

// Prepare the send from
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'changestatus');

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));
$form->addVariable(_("Status"), 'status', 'enum', true, false, false, array($statuses, true));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('notifies/changestatus.php'));
    } else {
        $result = $minerva_invoices->setStatus($info['invoices'], $info['status']);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $minerva_invoices->log($info['invoices'], 'download');
            $msg = sprintf(_("Changed status to %s for %s %s"), $statuses[$info['status']], count($info['invoices']), $type_name);
            $notification->push($msg, 'horde.success');
            header('Location: ' . Horde::applicationUrl('notifies/list.php'));
            exit;
        }
    }

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();

$form->renderActive(null, null, Horde::applicationUrl('notifies/changestatus.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
