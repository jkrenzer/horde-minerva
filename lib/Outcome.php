<?php
/**
 * The Minerva_Outcome:: class and Minerva_OutcomeMapper:: provides Rdo extension
 * used in for handling Outcomes.
 *
 * $Horde: incubator/minerva/lib/Outcome.php,v 1.8 2009/06/10 05:24:23 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Outcome extends Horde_Rdo_Base {

    /**
     * Export this invoice in iCalendar format.
     *
     * @param array $data                 Event data
     * @param Horde_iCalendar &$calendar  A Horde_iCalendar object that acts as
     *                                    a container.
     *
     * @return Horde_iCalendar_vinvoice  The vEvent object for this invoice.
     */
    static public function toiCalendar($invoice, &$iCal)
    {
        static $url;

        if (is_null($url)) {
            $url = Horde::applicationUrl('outcome/invoice.php', true, -1);
        }

        $vEvent = &Horde_iCalendar::newComponent('vevent', $iCal);

        $time = $invoice['due'];
        $vEvent->setAttribute('DTSTART', $time);
        $vEvent->setAttribute('DTEND', $time + 1);
        $vEvent->setAttribute('DTSTAMP', $_SERVER['REQUEST_TIME']);
        $vEvent->setAttribute('UID', $invoice['id']);
        $vEvent->setAttribute('SUMMARY', _("Invoice past due") . ': ' . $invoice['client_name']);
        $vEvent->setAttribute('TRANSP', 'OPAQUE');
        $vEvent->setAttribute('URL', Horde_Util::addParameter($url, 'id', $invoice['id']));

        return $vEvent;
    }
}

class Minerva_OutcomeMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_outcome';

    /**
     */
    public function getAdapter()
    {
        $config = Horde::getDriverConfig('storage');
        $config['adapter'] = $config['phptype'];

        return Horde_Db_Adapter::factory($config);
    }

    /**
     * Get oucome metadata
     */
    public function formMeta()
    {
        return Horde::loadConfiguration('outcome.php', 'data', 'minerva');
    }

    /**
     * Get query
     *
     * @param $criteria array Filter
     */
    public function getQuery($criteria = array())
    {
        $query = new Horde_Rdo_Query($this);

        // Get selected fields
        if (!empty($criteria['fields'])) {
            $query->setFields($criteria['fields']);
        }

        // Tests
        if (!empty($criteria['tests'])) {
            foreach ($criteria['tests'] as $test) {
                if (!is_array($test)) {
                    continue;
                }
                $query->addTest($test['field'], $test['test'], $test['value']);
            }
        }

        // Sorter
        $query->sortBy('due DESC');

        // Limit results
        if (isset($criteria['limit'])) {
            $query->limit($criteria['limit']['count'], $criteria['limit']['from']);
        }

        return $query;
    }

    /**
     * Get query results in an array
     *
     * @param $criteria array Filter
     */
    public function getAll($criteria = array())
    {
        $list = array();
        $query = $this->getQuery($criteria);
        foreach (new Horde_Rdo_List($query) as $row) {
            $list[$row->id] = iterator_to_array($row);
        }

        return $list;
    }

    /**
     * Get the the most old year record
     */
    public function getMinYear()
    {
        $query = new Horde_Rdo_Query($this);
        $query->setFields('YEAR(MIN(recived))');
        list($sql, $bindParams) = $query->getQuery();
        return $this->adapter->selectValue($sql, $bindParams);
    }

    /**
     * Save query
     *
     * @param $criteria array Filter
     */
    public function saveQuery($criteria)
    {
        unset($criteria['limit']);

        $_SESSION['minerva']['form_outcome'] = $criteria;
    }

    /**
     * Load query
     */
    public function loadQuery()
    {
        if (empty($_SESSION['minerva']['form_outcome'])) {
            return array();
        } else {
            return $_SESSION['minerva']['form_outcome'];
        }
    }
}