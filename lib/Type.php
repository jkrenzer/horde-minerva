<?php
/**
 * The Minerva_Type:: and Minerva_TypeMapper:: classes provide Rdo
 * extension used in for handling Types.
 *
 * $Horde: incubator/minerva/lib/Type.php,v 1.14 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Type extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_TypeMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_types';

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

        $data['offset'] = array(
            'humanName' => _("Offset"),
            'type' => 'int'
        );

        $data['statuses'] = array(
            'humanName' => _("Statuses"),
            'type' => 'set',
            'params' => array(Minerva::getStatuses())
        );

        return $data;
    }

    /**
     * Returns cached types
     *
     * @return array
     */
    static public function getTypes()
    {
        static $types;

        if ($types) {
            return $types;
        }

        if (($types = Minerva::getCache('types'))) {
            return $types;
        }

        $types = array();
        $statuses = Minerva::getStatuses();

        $mapper = new Minerva_TypeMapper();
        $query = new Horde_Rdo_Query($mapper);
        $query->sortBy('sort DESC');

        foreach (new Horde_Rdo_List($query) as $type) {
            $s = trim($type->statuses);
            $types[$type->id] = array(
                'name' => $type->name,
                'offset' => (int)$type->offset,
                'statuses' => empty($s) ? array_keys($statuses) : explode('|', $type->statuses));
        }

        Minerva::setCache('types', $types);

        return $types;
    }

    /**
     * Wrap to expire cache
     */
    public function create($fields)
    {
        parent::create($fields);
        Minerva::expireCache('types');
    }

    public function update($object, $fields = null)
    {
        parent::update($object, $fields);
        Minerva::expireCache('types');
    }

    public function delete($object)
    {
        parent::delete($object);
        Minerva::expireCache('types');
    }
}
