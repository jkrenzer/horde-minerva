<?php
/**
 * Minerva external Application interface.
 *
 * $Horde: incubator/minerva/lib/Application.php,v 1.2 2009/09/15 15:10:58 duck Exp $
 *
 * This file defines Minerva's external API interface. Other applications can
 * interact with Minerva through this API.
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Application extends Horde_Registry_Application
{
    /**
     * Build permission array
     */
    public function perms()
    {
        static $perms = array();
        if (!empty($perms)) {
            return $perms;
        }

        $perms['tree']['minerva']['admin'] = true;
        $perms['title']['minerva:admin'] = _("Admin");

        require_once dirname(__FILE__) . '/Type.php';
        require_once dirname(__FILE__) . '/Status.php';

        $data = new Minerva_TypeMapper();
        $perms['tree']['minerva']['types'] = false;
        $perms['title']['minerva:types'] = _("Types");
        foreach ($data->find() as $type) {
            $perms['tree']['minerva']['types'][$type->id] = false;
            $perms['title']['minerva:types:' . $type->id] = $type->name;
        }

        $data = new Minerva_StatusMapper();
        $perms['tree']['minerva']['statuses'] = false;
        $perms['title']['minerva:statuses'] = _("Statuses");
        foreach ($data->find() as $type) {
            $perms['tree']['minerva']['statuses'][$type->id] = false;
            $perms['title']['minerva:statuses:' . $type->id] = $type->name;
        }

        $perms['tree']['minerva']['outcome'] = false;
        $perms['title']['minerva:outcome'] = _("Outcome");

        $perms['tree']['minerva']['recurrence'] = false;
        $perms['title']['minerva:recurrence'] = _("Recurrence");

        return $perms;
    }

    /**
     * Generate the menu to use on the prefs page.
     *
     * @return Horde_Menu  A Horde_Menu object.
     */
    public function prefsMenu()
    {
        return Minerva::getMenu();
    }
}