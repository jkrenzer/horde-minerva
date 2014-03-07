<?php
/**
 * Outcome export class
 *
 * $Horde: incubator/minerva/lib/Outcome/Export.php,v 1.15 2009/04/13 17:29:16 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Outcome_Export {

    /**
     * Account to use with forms
     *
     * @var string
     */
    protected $_account = array();

    /**
     * Supported export formats
     */
    static function formats()
    {
        $formats = array('APP' => _("APP (TXT)"),
                         'SWIFT' => _("SWIFT (TXT)"),
                         'OFX' => _("Open Financial Exchange (OXF)"),
                         'CSV' => _("Comma separated values (CSV)"),
                         'TSV' => _("Tab separated values (TSV)"),
                         'XML' => _("Extensible Markup Language (XML)"));

        if (empty($GLOBALS['conf']['finance']['export_formats'])) {
            return $formats;
        }

        $allow = array_flip($GLOBALS['conf']['finance']['export_formats']);
        foreach ($formats as $key => $val) {
            if (!array_key_exists($key, $allow)) {
                unset($formats[$key]);
            }
        }

        return $formats;
    }

    /**
     * Load bank account details.
     */
    public function setBankAccount($account)
    {
        require_once MINERVA_BASE . '/lib/Bank.php';

        $mapper = new Minerva_BankMapper();
        $this->_account = array_merge(iterator_to_array($mapper->findOne($account)),
                                        iterator_to_array(Minerva::getCompany()));
    }

    /**
     * Create exporter.
     */
    static function factory($driver = 'XML', $params = array())
    {
        $class = 'Outcome_Export_' . $driver;

        if (!class_exists($class)) {
             include dirname(__FILE__) . '/Export/' . $driver . '.php';
        }

        if (class_exists($class)) {
            return new $class($params);
        } else {
             return PEAR::raiseError("Driver not found");
        }
    }

}