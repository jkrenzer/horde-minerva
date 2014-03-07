<?php
/**
 * Email drafts
 *
 * $Horde: incubator/minerva/admin/drafts/email.php,v 1.28 2009/10/17 11:16:21 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

define('MINERVA_BASE', dirname(__FILE__) . '/../../');
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/Email.php';
require '../tabs.php';

$title = _("Emails");
$tm = new Minerva_EmailMapper();
require_once MINERVA_BASE . '/lib/Crud.php';
require_once MINERVA_BASE . '/lib/Table.php';

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('email');

if (!in_array($action, array('create', 'update'))) {
    $template = new Minerva_Table_Helper(array('filter' => $filter,
                                               'title' => $title,
                                               'decorator' => 'Horde_Rdo_Table_Helper_Lens',
                                               'url' => $self_url),
                                         $tm);
    $template->fill();
    echo $template->fetch();
}

if (in_array($action, array('create', 'update', 'search'))) {
    $form->renderActive();
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
