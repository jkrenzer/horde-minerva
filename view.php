<?php
/**
 * Wrapper for files like logos and so on 
 *
 * $Horde: incubator/minerva/view.php,v 1.11 2009/06/10 05:24:21 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

define('AUTH_HANDLER', true);
$session_control = 'none';
$no_compress = true;

define('MINERVA_BASE', dirname(__FILE__));
require_once MINERVA_BASE . '/lib/base.php';

if (($file = Horde_Util::getGet('file')) == null) {
    exit;
}

if (empty($conf['convert']['template_path'])) {
    $dir = MINERVA_TEMPLATES;
} else {
    $dir = $conf['convert']['template_path'];
}

$path = $dir . '/' . $file;
if (!file_exists($path)) {
    header('HTTP/1.0 404 Not Found');
    echo '404 Not Found';
    if (Minerva::isAdmin()) {
        echo "<br />" . $path;
    }
    exit;
} elseif (!is_readable($path)) {
    header('HTTP/1.0 403 Forbidden');
    echo '403 Forbidden';
    exit;
}

$browser->downloadHeaders($file, null, false, filesize($path));

readfile($path);
