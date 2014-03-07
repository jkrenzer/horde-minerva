<?php
/**
 * The Minerva_Email:: and Minerva_EmailMapper:: classes provide Rdo
 * extension used in for handling email drafts.
 *
 * $Horde: incubator/minerva/lib/Email.php,v 1.12 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Email extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_EmailMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_emails';

    /**
     */
    public function getAdapter()
    {
        $config = Horde::getDriverConfig('storage');
        $config['adapter'] = $config['phptype'];

        return Horde_Db_Adapter::factory($config);
    }

    /**
     * Return field names.
     */
    public function formMeta()
    {
        $data['id'] = array('humanName' => _("Id"));
        $data['name'] = array('humanName' => _("Name"));
        $data['subject'] = array('humanName' => _("Subject"));
        $data['content'] = array('humanName' => _("Content"),
                                 'type' => 'longtext');
        return $data;
    }

    /**
     * Return all drafts
     */
    public function getAll()
    {
        return $this->find();
    }

    /**
     * Get indexed array used in forms etc
     */
    public function getEnum()
    {
        $data = array();
        foreach ($this->find() as $value) {
            $data[$value->id] = $value->name . ' (' . $value->subject . ')';
        }

        return $data;
    }

}