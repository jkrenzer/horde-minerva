<?php
/**
 * $Horde: incubator/minerva/lib/Table.php,v 1.8 2009/09/17 15:28:21 duck Exp $
 *
 * @package Minerva
 */
class Minerva_Table_Helper extends Horde_Rdo_Table_Helper {

    /**
     * Constructor
     *
     * @param Horde_Rdo_Mapper $object Mapper instance.
     * @param array $params Template defaults.
     */
    public function __construct($params = array(), $object)
    {
        $defaults = array('delete' => true,
                          'update' => true,
                          'export' => false,
                          'search' => true,
                          'create' => true,
                          'columns' => null,
                          'name' => '',
                          'id' => '',
                          'url' => Horde::selfUrl(),
                          'img_dir' => $GLOBALS['registry']->getImageDir('horde'),
                          'sort' => array(),
                          'filter' => array(),
                          'page_count' => 10,
                          'perpage' => 10,
                          'page' => 0);

        $params = array_merge($defaults, $params);
        $defaults['page'] = Horde_Util::getGet('page_' . $params['id'], 0);

        parent::__construct($params, $object);
    }
}
