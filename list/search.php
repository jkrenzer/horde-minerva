<?php
/**
 * Document search
 *
 * $Horde: incubator/minerva/list/search.php,v 1.9 2009/12/02 00:05:57 duck Exp $
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
require_once MINERVA_BASE . '/list/tabs.php';

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('search');

$form = new Minerva_Form_List();
$form->render($list_url);

require $registry->get('templates', 'horde') . '/common-footer.inc';
