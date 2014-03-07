<?php
/**
 * Notifies editing tabs
 *
 * $Horde: incubator/minerva/notifies/tabs.php,v 1.12 2009/12/10 17:42:34 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

if (!$conf['finance']['payment_notifies']) {
    $notification->push(_("Last payment notifies are disabled."), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('invoices/list.php'));
    exit;
}

$list_url = Horde::applicationUrl('notifies/list.php');
$vars = Horde_Variables::getDefaultVariables();
$tabs = new Horde_Ui_Tabs('notifies', $vars);

$tabs->addTab(_("Notifies"), $list_url, 'list');
$tabs->addTab(_("Create new notify"), Horde::applicationUrl('notifies/create.php'), 'create');
$tabs->addTab(_("Search"), Horde::applicationUrl('notifies/search.php'), 'search');

Horde::addScriptFile('tables.js', 'horde');

$notifies = new Minerva_Notifies();
