<?php
/**
 * Edito or create outcomes
 *
 * $Horde: incubator/minerva/outcome/edit.php,v 1.18 2009/06/10 05:24:26 slusarz Exp $
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
require_once MINERVA_BASE . '/outcome/tabs.php';

$title = _("Create outcome");
$tm = new Minerva_OutcomeMapper();
require_once MINERVA_BASE . '/lib/Crud.php';

$action = Horde_Util::getFormData('action', 'create');

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde_Util::addParameter(Horde::applicationUrl('outcome/edit.php'), 'action', $action), 'post');

require_once $registry->get('templates', 'horde') . '/common-footer.inc';