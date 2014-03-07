<?php
/**
 * Rdo data managment form
 *
 * $Horde: incubator/minerva/lib/Crud.php,v 1.11 2009/09/17 15:28:21 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

$action = Horde_Util::getFormData('action');
$self_url = Horde::selfUrl();
if (!isset($filter)) {
    $filter = Horde_Util::getFormData('filter', array());
}

$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Rdo_Form_Helper($vars, $title, null, $tm);

switch ($action) {
case 'delete':
    $result = $form->delete();
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Deleted"), 'horde.success');
    }
    header('Location: ' . $self_url);
    exit;

case 'create':
    if (!$form->validate()) {
        break;
    }
    $form->getInfo(null, $info);
    unset($info['action']);
    $result = $form->create($info);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Created"), 'horde.success');
    }
    header('Location: ' . $self_url);
    exit;

case 'update':
    $test = $form->getSelected();
    if ($test instanceof PEAR_Error) {
        $notification->push($test->getMessage(), 'horde.error');
        header('Location: ' . $self_url);
        exit;
    } elseif (!$form->isSubmitted()) {
        foreach ($test as $key => $value) {
            $vars->set($key, $value);
        }
    } elseif ($form->validate()) {
        $form->getInfo(null, $info);
        unset($info['action']);
        $result = $form->update($info);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(_("Updated"), 'horde.success');
        }
        header('Location: ' . $self_url);
        exit;
    }
    break;

case 'search':
    $form->getInfo(null, $filter);
    unset($filter['action']);
    foreach ($filter as $key => $value) {
        if (empty($value)) {
            unset($filter[$key]);
        }
    }
    $self_url = Horde_Util::addParameter($self_url, 'action', 'search');
    break;
}