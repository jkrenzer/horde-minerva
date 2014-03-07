<?php
/**
 * Preview invoice tempalte
 *
 * $Horde: incubator/minerva/admin/drafts/preview.php,v 1.14 2009/11/09 19:58:36 duck Exp $
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

$template = Horde_Util::getGet('template');
$convert = Minerva_Convert::factory($template);
$convert->preview();
