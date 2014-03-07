<?php
/**
 * The Minerva_Unit: and Minerva_TypeMapper:: classes provide Rdo
 * extension used in for handling item units.
 *
 * $Horde: incubator/minerva/lib/Unit.php,v 1.7 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Unit extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_UnitMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_units';

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
            'humanName' => _("Id"),
            'required' => false,
            'hidden' => true
        );

        $data['name'] = array(
            'humanName' => _("Name"),
            'required' => true
        );

        $data['sort'] = array(
            'humanName' => _("Sort"),
            'type' => 'int'
        );

        return $data;
    }

    /**
     * Returns cached units
     *
     * @return array
     */
    static public function getUnits()
    {
        static $units;

        if ($units) {
            return $units;
        }

        if (($units = Minerva::getCache('units'))) {
            return $units;
        }

        $mapper = new Minerva_UnitMapper();
        $query = new Horde_Rdo_Query($mapper);
        $query->sortBy('sort DESC, name DESC');

        $units = array();
        foreach (new Horde_Rdo_List($query) as $unit) {
            $units[$unit->id] = $unit->name;
        }

        Minerva::setCache('units', $units);

        return $units;
    }

    /**
     * Wrap to expire cache
     */
    public function create($fields)
    {
        parent::create($fields);
        Minerva::expireCache('units');
    }

    public function update($object, $fields = null)
    {
        parent::update($object, $fields);
        Minerva::expireCache('units');
    }

    public function delete($object)
    {
        parent::delete($object);
        Minerva::expireCache('units');
    }

}