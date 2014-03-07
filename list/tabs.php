<?php
/**
 * Invoices list tabs
 *
 * $Horde: incubator/minerva/list/tabs.php,v 1.11 2009/12/10 17:42:34 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
$list_url = Horde::applicationUrl('list/list.php');
$vars = Horde_Variables::getDefaultVariables();

$types = Minerva::getTypes();
$tabs = new Horde_Ui_Tabs('type', $vars);
foreach ($types as $key => $value) {
    $tabs->addTab($value, $list_url, $key);
}
$tabs->addTab(_("Search"), Horde::applicationUrl('list/search.php'), 'search');
if ($conf['finance']['payment_notifies']) {
    $tabs->addTab(_("Notifies"), Horde::applicationUrl('notifies/list.php'), 'notifies');
}
$prefs_url = Horde::url($GLOBALS['registry']->get('webroot', 'horde') . '/services/prefs.php?app=minerva&group=list');
$tabs->addTab(_("Set defaults"), $prefs_url, 'prefs');
