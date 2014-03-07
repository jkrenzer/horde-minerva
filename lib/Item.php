<?php
/**
 * The Minerva_Item: and Minerva_TypeMapper:: classes provide Rdo
 * extension used in for handling items.
 *
 * $Horde: incubator/minerva/lib/Item.php,v 1.14 2009/06/12 21:25:12 duck Exp $
 *
 * TODO Add categories
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Item extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_ItemMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_items';

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

        $data['model'] = array(
            'humanName' => _("Model")
        );

        $data['name'] = array(
            'humanName' => _("Name"),
            'required' => true
        );

        $data['price'] = array(
            'humanName' => _("Price"),
            'type' => 'number'
        );

        $data['discount'] = array(
            'humanName' => _("Discount"),
            'type' => 'number'
        );

        $taxes = array();
        foreach (Minerva::getTaxes() as $value) {
            $taxes[$value['id']] = $value['name'];
        }

        $data['tax'] = array(
            'humanName' => _("Tax"),
            'type' => 'enum',
            'params' => array($taxes)
        );

        $units = Minerva::getUnits();
        $data['unit'] = array(
            'humanName' => _("Unit"),
            'required' => false,
            'readonly' => empty($units),
            'type' => 'enum',
            'params' => array($units, true)
        );

        return $data;
    }

}

class Minerva_Item_Lens extends Horde_Lens implements IteratorAggregate {

    /**
     */
    private $_taxes = array();

    /**
     */
    private $_units = array();

    /**
     */
    function __construct()
    {
        $this->_taxes = Minerva::getTaxes();
        $this->_units = Minerva::getUnits();
    }

    /**
     * Implement the IteratorAggregate pattern. When a single Rdo
     * object is iterated over, we return an iterator that loops over
     * each property of the object.
     *
     * @return ArrayIterator The Iterator instance.
     */
    public function getIterator()
    {
        return new Horde_Rdo_Iterator($this);
    }

    /**
     */
    public function __get($key)
    {
        switch ($key) {

        case 'price':
            return Minerva::format_price($this->_target->$key);
            break;

        case 'tax':
            return $this->_taxes[$this->_target->$key]['name']
                    . ' (' . number_format($this->_taxes[$this->_target->$key]['value'],2) . '%)';
            break;

        case 'unit':
            if ($this->_target->$key > 0 && isset($this->_units[$this->_target->$key])) {
                return $this->_units[$this->_target->$key];
            } else {
                return '';
            }
            break;

        default:
            return $this->_target->$key;
            break;

        }

    }
}
