<?php
/**
 * Groups
 *
 * $Horde: incubator/minerva/admin/tags.php,v 1.10 2009/11/09 19:58:35 duck Exp $
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
require_once MINERVA_BASE . '/lib/Tag.php';
require dirname(__FILE__) . '/tabs.php';

$title = _("Tags");
$tm = new Minerva_TagMapper();
$vars = Horde_Variables::getDefaultVariables();
require_once MINERVA_BASE . '/lib/Crud.php';
require_once MINERVA_BASE . '/lib/Table.php';

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('tags');

if (!in_array($action, array('create', 'update'))) {
    $template = new Minerva_Table_Helper(array('filter' => $filter,
                                               'title' => $title,
                                               'url' => $self_url,
                                               'decorator' => 'Horde_Rdo_Table_Helper_Lens',
                                               'sort' => 'name'),
                                         $tm);
    $template->fill();
    echo $template->fetch();
}

if (in_array($action, array('create', 'update', 'search'))) {
    $form->renderActive();
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
