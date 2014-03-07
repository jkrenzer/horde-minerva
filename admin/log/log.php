<?php
/**
 * Email drafts
 *
 * $Horde: incubator/minerva/admin/log/log.php,v 1.7 2009/10/17 11:16:21 duck Exp $
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
require_once MINERVA_BASE . '/lib/Log.php';
require '../tabs.php';

$title = _("Log");
$tm = new Minerva_LogMapper();
require_once MINERVA_BASE . '/lib/Crud.php';
require_once MINERVA_BASE . '/lib/Table.php';

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('log');

$template = new Minerva_Table_Helper(array('filter' => $filter,
                                            'title' => $title,
                                            'delete' => false,
                                            'update' => false,
                                            'create' => false,
                                            'page' => Horde_Util::getFormData('page_minerva_log', 0),
                                            'decorator' => 'Minerva_Log_Lens',
                                            'sort' => array(array('log_time DESC')),
                                            'url' => $self_url),
                                        $tm);
$template->fill();
echo $template->fetch();
// file_put_contents('/tmp/a', $template->getTemplate());

if ($action == 'search') {
    $form->renderActive();
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
