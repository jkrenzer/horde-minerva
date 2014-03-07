<?php
/**
 * The Minerva_Status:: and Minerva_StatusMapper:: classes provide Rdo
 * extension used in for handling statuses.
 *
 * $Horde: incubator/minerva/lib/Status.php,v 1.14 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Status extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_StatusMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_statuses';

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
        $data['id'] = array(
            'humanName' => _("Id")
        );

        $data['sort'] = array(
            'humanName' => _("Order Number"),
            'type' => 'int'
        );

        $data['name'] = array(
            'humanName' => _("Name")
        );

        return $data;
    }

    /**
     * Returns cached statuses
     *
     * @return array
     */
    static public function getStatuses()
    {
        static $statuses;

        if ($statuses) {
            return $statuses;
        }

        if (($statuses = Minerva::getCache('statuses'))) {
            return $statuses;
        }

        $statuses = array();
        $mapper = new Minerva_StatusMapper();
        $query = new Horde_Rdo_Query($mapper);
        $query->sortBy('sort DESC');

        foreach (new Horde_Rdo_List($query) as $status) {
            $statuses[$status->id] = $status->name;
        }

        Minerva::setCache('statuses', $statuses);

        return $statuses;
    }

    /**
     * Wrap to expire cache
     */
    public function create($fields)
    {
        parent::create($fields);
        Minerva::expireCache('statuses');
        Minerva::expireCache('types');
    }

    public function update($object, $fields = null)
    {
        parent::update($object, $fields);
        Minerva::expireCache('statuses');
        Minerva::expireCache('types');
    }

    public function delete($object)
    {
        parent::delete($object);
        Minerva::expireCache('statuses');
        Minerva::expireCache('types');
    }
}
