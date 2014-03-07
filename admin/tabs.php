<?php
/**
 * Tabs for admin pages
 *
 * $Horde: incubator/minerva/admin/tabs.php,v 1.24 2009/12/10 17:42:33 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

if (!Minerva::isAdmin()) {
    $notification->push(_("You don't have permisson to access to administration."), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$admin = Horde_Util::getFormData('admin', 'locked');

$vars = Horde_Variables::getDefaultVariables();
$tabs = new Horde_Ui_Tabs('admin', $vars);

$tabs->addTab(_("Items"), Horde::applicationUrl('admin/items.php'), 'items');
$tabs->addTab(_("Units"), Horde::applicationUrl('admin/units.php'), 'units');
$tabs->addTab(_("Locked"), Horde::applicationUrl('admin/locked.php'), 'locked');
$tabs->addTab(_("Resellers"), Horde::applicationUrl('admin/resellers.php'), 'resellers');
$tabs->addTab(_("Statuses"), Horde::applicationUrl('admin/statuses.php'), 'statuses');
$tabs->addTab(_("Types"), Horde::applicationUrl('admin/types.php'), 'types');
$tabs->addTab(_("Email drafts"), Horde::applicationUrl('admin/drafts/email.php'), 'email');
$tabs->addTab(_("Invoice drafts"), Horde::applicationUrl('admin/drafts/invoice.php'), 'invoice');
$tabs->addTab(_("Banks"), Horde::applicationUrl('admin/banks.php'), 'banks');
$tabs->addTab(_("Company"), Horde::applicationUrl('admin/company.php'), 'company');
$tabs->addTab(_("Tags"), Horde::applicationUrl('admin/tags.php'), 'tags');
$tabs->addTab(_("Taxes"), Horde::applicationUrl('admin/taxes.php'), 'taxes');
$tabs->addTab(_("Currencies"), Horde::applicationUrl('admin/currencies.php'), 'currencies');

if (Horde_Auth::isAdmin()) {
    $tabs->addTab(_("Log"), Horde::applicationUrl('admin/log/log.php'), 'log');
    $tabs->addTab(_("Permissions"), Horde::url($registry->get('webroot', 'horde') . '/admin/perms/'), 'perms');
}
