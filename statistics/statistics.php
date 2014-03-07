<?php
/**
 * Minerva statistics informations
 *
 * $Horde: incubator/minerva/statistics/statistics.php,v 1.8 2009/11/09 19:58:38 duck Exp $
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

$title = _("Statistics");

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

require MINERVA_TEMPLATES . '/statistics/statistics.inc';

require $registry->get('templates', 'horde') . '/common-footer.inc';
