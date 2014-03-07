<?php
/**
 * The Minerva_Log:: and Minerva_LogMapper:: classes provide Rdo
 * extension used in for handling Logs.
 *
 * $Horde: incubator/minerva/lib/Log.php,v 1.6 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Log extends Horde_Rdo_Base {
}

/**
 * @package Minerva
 */
class Minerva_LogMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_log';

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
        $data['invoice_id'] = array(
            'humanName' => _("Invoice")
        );

        $data['log_time'] = array(
            'humanName' => _("Date")
        );

        $data['horde_uid'] = array(
            'humanName' => _("User")
        );

        $data['log_type'] = array(
            'humanName' => _("Type")
        );

        $data['log_host'] = array(
            'humanName' => _("Host")
        );

        $data['log_data'] = array(
            'humanName' => _("Data"),
        );

        return $data;
    }
}

require_once HORDE_BASE . '/incubator/Horde_Rdo/Lens.php';
class Minerva_Log_Lens extends Horde_Rdo_Lens {

    /**
     */
    private $_date_format = '%x';

    /**
     */
    private $_time_format = 'G:i';

    /**
     */
    public function __construct()
    {
        $this->_date_format = $GLOBALS['prefs']->getValue('date_format');
        $this->_time_format = $GLOBALS['prefs']->getValue('twentyFour') ? 'G:i' : 'g:ia';
    }

    /**
     */
    public function __get($key)
    {
        $value = parent::__get($key);

        switch ($key) {

        case 'log_time';
            return strftime($this->_date_format, $value) .
                    ' ' . date($this->_time_format , $value);
        break;

        case 'log_data';
            $value = unserialize($value);
            if (empty($value)) {
                return '';
            }
            return implode(', ', $value['invoice']);
        break;

        case 'log_data';
            return wordwrap(substr($value, 0, 150), 70, ' ', true);
        break;

        }

        return $value;
    }
}