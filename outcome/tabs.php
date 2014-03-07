<?php
/**
 * Outcome editing tabs
 *
 * $Horde: incubator/minerva/outcome/tabs.php,v 1.18 2009/12/10 17:42:34 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

require_once MINERVA_BASE . '/lib/Bank.php';
require_once MINERVA_BASE . '/lib/Outcome.php';

$vars = Horde_Variables::getDefaultVariables();
$tabs = new Horde_Ui_Tabs('outcome', $vars);

$tabs->addTab(_("Outcome"), Horde::applicationUrl('outcome/list.php'), 'list');
$tabs->addTab(_("To pay"), Horde::applicationUrl('outcome/topay.php'), 'topay');
$tabs->addTab(_("Create"), Horde::applicationUrl('outcome/edit.php'), 'create');
$tabs->addTab(_("Export"), Horde::applicationUrl('outcome/export.php'), 'export');

Horde::addScriptFile('tables.js', 'horde');
