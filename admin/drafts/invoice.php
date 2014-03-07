<?php
/**
 * Minerva Invoice Drafts list
 *
 * $Horde: incubator/minerva/admin/drafts/invoice.php,v 1.26 2009/11/09 19:58:36 duck Exp $
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

$title = _("Drafts");
$preview_url = Horde::applicationUrl('admin/drafts/preview.php');
$expunge_url = Horde_Util::addParameter(Horde::applicationUrl('admin/drafts/invoice.php'), 'action', 'expunge');
$expunge_img = Horde::img('delete.png', '', '', $registry->getImageDir('horde'));

$convert = Minerva_Convert::factory();
$templates = $convert->getTemplates();
if ($templates instanceof PEAR_Error) {
    $notification->push($templates);
    $templates = array();
}

$template = Horde_Util::getGet('template');
if (Horde_Util::getGet('action') == 'expunge' && in_array($template, array_keys($templates))) {
    $result = $convert->expungeCache($template);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(sprintf(_("Expunged cache for template %s."), $template), 'horde.success');
    }
}

Horde::addScriptFile('tables.js', 'horde');
require '../tabs.php';
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('invoice');

require MINERVA_TEMPLATES . '/admin/drafts/invoice/header.inc';

foreach ($templates as $id => $data) {
    $parts = $convert->parts($id);
    if ($parts instanceof PEAR_Error) {
        echo $parts->getMessage();
        continue;
    }
    $mtime = filemtime($parts['body']);

    // Has header
    if (isset($parts['header'])) {
        $mtime = min($mtime, filemtime($parts['header']));
    }

    // Has footer
    if (isset($parts['footer'])) {
        $mtime = min($mtime, filemtime($parts['footer']));
    }

    require MINERVA_TEMPLATES . '/admin/drafts/invoice/row.inc';
}

require MINERVA_TEMPLATES . '/admin/drafts/invoice/footer.inc';
require $registry->get('templates', 'horde') . '/common-footer.inc';
