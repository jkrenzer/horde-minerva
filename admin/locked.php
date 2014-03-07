<?php
/**
 * Minerva Drafts
 *
 * $Horde: incubator/minerva/admin/locked.php,v 1.17 2009/11/09 19:58:35 duck Exp $
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

$title = _("Locked");
$types = Minerva::getTypes();
$locked = $minerva_invoices->getLocked();
if ($locked instanceof PEAR_Error) {
    $notification->push($locked);
    $locked = array();
}

Horde::addScriptFile('tables.js', 'horde');
require 'tabs.php';
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('locked');

require MINERVA_TEMPLATES . '/admin/locked/header.inc';
foreach ($locked as $invoice) {
    require MINERVA_TEMPLATES . '/admin/locked/row.inc';
}
require MINERVA_TEMPLATES . '/admin/locked/footer.inc';
require $registry->get('templates', 'horde') . '/common-footer.inc';
