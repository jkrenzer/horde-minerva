<?php
/**
 * Invoice datails
 *
 * $Horde: incubator/minerva/outcome/invoice.php,v 1.18 2009/12/01 12:52:45 jan Exp $
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

if (!Minerva::hasOutcomePermission(Horde_Perms::READ)) {
    $notification->push(_("You don't have permission to read outcomes"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('index.php'));
    exit;
}

$id = Horde_Util::getFormData('id');
$title = sprintf(_("Outcome: %s"), $id);
$outcomes = new Minerva_OutcomeMapper();
$invoice = iterator_to_array($outcomes->findOne($id));

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

require_once MINERVA_TEMPLATES . '/outcome/invoice.inc';

if ($registry->hasMethod('forums/doComments')) {

    $comment_url = Horde_Util::addParameter(Horde::applicationUrl('outcome/invoice.php'), 'id', $id, false);
    $comments = $registry->call('forums/doComments', array('minerva', $id, 'commentCallback', true, $comment_url));

    if (!empty($comments['threads'])) {
        echo $comments['threads'];
    }

    if (!empty($comments['comments'])) {
        echo $comments['comments'];
    }
}

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
