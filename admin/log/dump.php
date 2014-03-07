<?php
/**
 * Email drafts
 *
 * $Horde: incubator/minerva/admin/log/dump.php,v 1.3 2009/06/10 05:24:21 slusarz Exp $
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

$title = _("Dump");
$tm = new Minerva_LogMapper();

$log = $tm->find(Horde_Rdo::FIND_FIRST, Horde_Util::getGet('log_id'));
$data = unserialize($log->log_data);

echo '<pre>';
var_export($data);