<?php
/**
 * Minerva_Recurrence manages invoice recurrences
 *
 * $Horde: incubator/minerva/lib/Recurrences.php,v 1.1 2009/12/01 12:53:52 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Recurrences {

    /**
     * Handle for the driver
     *
     * @var driver
     */
    private $driver;

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->driver = &Minerva_Driver::singleton();
    }

    /**
     * Get all recurrences
     */
    public function getAll()
    {
        $query = 'SELECT r.invoice_id, r.invoice_name, r.horde_uid, r.description, r.created, '
               . ' r.articles, r.client, r.rstart, r.rend, r.rlast, c.name, r.rinterval,'
               . ' i.total, i.tax, (i.total-i.tax) as total_bare'
               . ' FROM minerva_recurrences r, minerva_clients c, minerva_invoices i'
               . ' WHERE r.invoice_id = c.invoice_id AND r.invoice_id = i.invoice_id'
               . ' ORDER BY invoice_id';
        return $this->driver->db->getAll($query, array(), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get a single recurrence clone data
     */
    public function getOne($id, $full = false)
    {
        $query = 'SELECT invoice_id, invoice_name, horde_uid, description, created,'
               . ' articles, client, draft, sendto, rstatus, rstart, rend, rinterval,'
               . ' roccurred, rlast FROM minerva_recurrences '
               . ' WHERE invoice_id = ? ORDER BY invoice_id';
        return $this->driver->db->getRow($query, array($id), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get the timestamp of the next occurance
     */
    public function getNext($start, $end, $interval)
    {
        if (!$interval) {
            return 0;
        }

        $when = strtotime($start);
        $interval = (int)$interval * 86400;
        $end = (int)$end;

        if ($end == 0) {

            // never ends
            while ($when < $_SERVER['REQUEST_TIME']) {
                $when = $when + $interval;
            }
            $when = $when - $interval;

        } elseif ($end<1000) {

            // specific number of times
            for ($i = 0; $i < $end && $when < $_SERVER['REQUEST_TIME']; $i++) {
                $when = $when + $interval;
            }

       } else {

            // till a date
            for ($i = 0; $when < $end && $when < $_SERVER['REQUEST_TIME']; $i++) {
                $when = $when + $interval;
            }
        }

        return $when;
    }

    /**
     * Get the timestamp of the last occurance
     */
    public function getLast($start, $end, $interval)
    {
        if (!$interval) {
            return;
        }

        $end = (int)$end;
        if ($end == 0) {

            // never ends
            return;

        } elseif ($end<1000) {

            // specific number of times
            return strtotime($start) + (int)$interval * 86400 * $end;

        } else {

            // till a date
            return $end;
        }
    }

    /**
     * Check if the invoice must be published today
     */
    public function recurrsOn($timestamp, $day = null)
    {
        static $dates;

        if (is_null($day)) {
            $day = $_SERVER['REQUEST_TIME'];
        }

        if (!isset($dates[$day])) {
            $today = new Horde_Date(date('Y-m-d 00:00:00', $day));
        }

        if (!isset($dates[$timestamp])) {
            $date = new Horde_Date(date('Y-m-d 00:00:00', $timestamp));
        } else {
            $date = $dates[$timestamp];
        }

        return $today->compareDate($date) == 0;
    }

    /**
     * Delete a recurrence
     */
    public function delete($id)
    {
        $query = 'DELETE FROM minerva_recurrences WHERE invoice_id=?';
        return $this->driver->write_db->query($query, array($id));
    }

    /**
     * Save an recurrence data
     *
     * TODO: Create a separate method for update, to not delete and then update
     */
    public function save($info)
    {
        // Delete old values
        $this->delete($info['invoice_id']);

        // insert now ones
        $params = array('invoice_id' => $info['invoice_id'],
                        'invoice_name' => $info['invoice_name'],
                        'horde_uid' => Horde_Auth::getAuth(),
                        'description' => $info['description'],
                        'articles' => (int)$info['articles'],
                        'client' => (int)$info['client'],
                        'draft' => @(int)$info['draft'],
                        'sendto' => @$info['sendto'],
                        'rstatus' => @$info['rstatus'],
                        'rstart' => @date('Y-m-d', $info['rstart']),
                        'rend' => @(int)$info['rend'],
                        'rinterval' => @(int)$info['rinterval'],
                        'roccurred' => 0,
                        'rlast' => 0,
                        'created' => $_SERVER['REQUEST_TIME']);

        $cols = implode(',', array_keys($params));
        $fields = str_repeat(', ?', count($params)-1);
        $query = 'INSERT INTO minerva_recurrences (' . $cols . ') VALUES (?' . $fields . ')';

        return $this->driver->write_db->query($query, $params);
    }

    /**
     * Get recurrences, that are still not ended.
     * So start is lower than now, end if higher
     * than now and is not occurred all the times
     */
    public function getRecurrences()
    {
        $query = 'SELECT invoice_id, rinterval, rstatus, draft, sendto, rstart, rend FROM minerva_recurrences'
               . ' WHERE rstart <= ' . $this->driver->db->quote(date('Y-m-d'))
               . ' AND ((rend < 10000 AND rend <> roccurred) OR (rend >= ' . $_SERVER['REQUEST_TIME'] . '))'
               . ' ORDER BY invoice_name';

        return $this->driver->db->getAssoc($query, false, array(), DB_FETCHMODE_ASSOC);
    }

    /**
     * Update successful renderer status
     */
    public function updateRecurrence($id)
    {
        $query = 'UPDATE minerva_recurrences SET roccurred = roccurred + 1, rlast = ? WHERE invoice_id = ?';
        $params = array($_SERVER['REQUEST_TIME'], $id);
        return $this->driver->write_db->query($query, $params);
    }

    /**
     * Export this invoice in iCalendar format.
     *
     * TODO export recurrence not just next instance
     *
     * @param array $data                 Event data
     * @param Horde_iCalendar &$calendar  A Horde_iCalendar object that acts as
     *                                    a container.
     *
     * @return Horde_iCalendar_vinvoice  The vEvent object for this invoice.
     */
    public function toiCalendar($invoice, &$iCal)
    {
        static $url;

        if (is_null($url)) {
            $url = Horde::applicationUrl('invoice/invoice.php', true, -1);
        }

        $vEvent = &Horde_iCalendar::newComponent('vevent', $iCal);
        $next = $this->getNext($invoice['rstart'], $invoice['rend'], $invoice['rinterval']);
        $vEvent->setAttribute('DTSTART', $next);
        $vEvent->setAttribute('DTEND', $next + 1);
        $vEvent->setAttribute('DTSTAMP', $_SERVER['REQUEST_TIME']);
        $vEvent->setAttribute('UID', $invoice['invoice_id']);
        $vEvent->setAttribute('SUMMARY', _("Recurrence: ") . ': ' . $invoice['name']);
        $vEvent->setAttribute('TRANSP', 'OPAQUE');
        $vEvent->setAttribute('DESCRIPTION', $invoice['description']);
        $vEvent->setAttribute('URL', Horde_Util::addParameter($url, 'clone_id', $invoice['invoice_id']));

        return $vEvent;
    }
}

