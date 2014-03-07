<?php
/**
 * Delete outcome
 *
 * $Horde: incubator/minerva/outcome/delete.php,v 1.17 2009/12/01 12:52:45 jan Exp $
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

if (!Minerva::hasOutcomePermission(Horde_Perms::DELETE)) {
    $notification->push(_("You don't have permission to delete outcomes"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('index.php'));
    exit;
}

$title = _("Delete");
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, _("Do you relay like to delete this outcome?"));

$id = Horde_Util::getFormData('id');
$form->addHidden('', 'id', 'int', $id);
$form->setButtons(array(_("Delete"), _("Cancel")));

if ($form->validate()) {
    if (Horde_Util::getFormData('submitbutton') == _("Delete")) {
        $outcomes = new Minerva_OutcomeMapper();
        $object = $outcomes->findOne(array('id' => $id));
        $result = $object->delete();
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(_("Outcome deleted"), 'horde.success');
        }
    } else {
        $notification->push(_("Outcome was not deleted"), 'horde.warning');
    }
    header('Location: ' . Horde::applicationUrl('outcome/list.php'));
    exit;
}

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
echo $form->renderActive(null, $vars, Horde::applicationUrl('outcome/delete.php'), 'post');

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
