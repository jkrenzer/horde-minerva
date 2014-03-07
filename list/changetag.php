<?php
/**
 * Change invoice status of invoices
 *
 * $Horde: incubator/minerva/list/changetag.php,v 1.5 2009/12/01 12:52:44 jan Exp $
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
require_once MINERVA_BASE . '/list/tabs.php';

// Check statuses premission
$criteria = Minerva::getCriteria();
$type_name = Minerva::getTypeName($criteria['invoice']['type']);
if (!Minerva::hasTypePermission($criteria['invoice']['type'], Horde_Perms::EDIT)) {
    $notification->push(sprintf(_("You don't have permission to access type %s."), $type_name), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Get selected invoices data
$list = Minerva::getList();
$tags = Minerva::getTags();
$title = sprintf(_("Change %s tag"), $type_name);

// Prepare the send from
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'changetag');

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));
$form->addVariable(_("Tag"), 'tag', 'enum', true, false, false, array($tags, true));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
    } else {
        $result = $minerva_invoices->setTag($info['invoices'], $info['tag']);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $msg = sprintf(_("Changed tag to %s for %s %s"), $tags[$info['tag']], count($info['invoices']), $type_name);
            $notification->push($msg, 'horde.success');
            header('Location: ' . Horde::applicationUrl('list/list.php'));
            exit;
        }
    }
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('list/changetag.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
