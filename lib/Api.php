<?php
/**
 * Minerva external API interface.
 *
 * $Horde: incubator/minerva/lib/Api.php,v 1.5 2009/12/01 12:52:44 jan Exp $
 *
 * This file defines Minerva's external API interface. Other applications can
 * interact with Minerva through this API.
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Api extends Horde_Registry_Api
{
    /**
     * Links.
     *
     * @var array
     */
    public $links = array(
        'show' => '%application%/show.php?invoice_id=|invoice_id|&type=|type|'
    );

    /**
     * Check if invoice exists
     *
     * @param int $id invoice id
     */
    public function exists($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        return $GLOBALS['minerva_invoices']->exists($id);
    }

    /**
     * Gets invoice data
     *
     * @param int $id invoice id
     */
    public function getOne($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        return $GLOBALS['minerva_invoices']->getOne($id);
    }

    /**
     * Gets invoice data
     *
     * @param int $id invoice id
     */
    public function getName($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        return $GLOBALS['minerva_invoices']->getName($id);
    }

    /**
     * Gets invoice status
     *
     * @param int $id invoice id
     */
    public function getStatus($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        return $GLOBALS['minerva_invoices']->getStatus($id);
    }

    /**
     * Save or update invoice
     *
     * @param array $data invoice data
     * @param int   $id   invoice id
     */
    public function save($data, $id = 0)
    {
        require_once dirname(__FILE__) . '/base.php';

        // Append type if needed
        $types = Minerva::getTypes(Horde_Perms::EDIT);
        if (!isset($data['invoice']['type']) && !empty($types)) {
            $data['invoice']['type'] = key($types);
        }

        // Check permission on type
        if (empty($types[$data['invoice']['type']])) {
            return PEAR::raiseError(sprintf(_("You don't have permission to access type %s."),
                                            Minerva::getTypeName($data['invoice']['type'])));
        }

        // Append status
        $statuses = Minerva::getStatuses(Horde_Perms::EDIT, $data['invoice']['type']);
        if (!isset($data['invoice']['status']) && !empty($statuses)) {
            $data['invoice']['status'] = key($statuses);
        }

        // Check permission on status
        if (empty($statuses[$data['invoice']['status']])) {
            return PEAR::raiseError(sprintf(_("You don't have permission to access status %s."),
                                            Minerva::getStatusName($data['invoice']['status'])));
        }

        // Check if the invoice is locked
        if ($id && ($user = $GLOBALS['minerva_invoices']->isLocked($invoice_id)) !== false) {
            return PEAR::raiseError(sprintf(_("Invoice id %s is already being edited by user %s."), $id, $user));
        }

        // Append invoice date
        if (empty($data['invoice']['date'])) {
            $data['invoice']['date'] = date('Y-m-d');
        }

        // Append place
        if (!isset($data['invoice']['place'])) {
            $data['invoice']['place'] = Minerva::getInvoicePlace();
        }

        // Append service time
        if (!empty($data['invoice']['service'])) {
            $data['invoice']['service'] = strftime($GLOBALS['prefs']->getValue('date_format'));
        }

        // Set expire date
        if (!isset($data['invoice']['expire'])) {
            $data['invoice']['expire'] = $GLOBALS['prefs']->getValue('invoice_expire');
        }

        // Save it
        return $GLOBALS['minerva_invoices']->save($data, $id);
    }

    /**
     * Convert invoice to a desiderated format
     *
     * @param int    $id         invoice id
     * @param mixed  $formats    format(s) to convert invoice to
     * @param string $template   template to use
     */
    public function convertInvoice($id, $formats = array(), $template = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        $convert = Minerva_Convert::factory();

        // Get allowed formats
        if (empty($formats)) {
            $formats = $GLOBALS['conf']['convert']['types'];
        } else {
            $formats = array_intersect($formats, $GLOBALS['conf']['convert']['types']);
        }

        $result = array();

        foreach ($formats as $format) {
            $result[$format] = $convert->convert($id, array(), null, $format);
            if ($result[$format] instanceof PEAR_Error) {
                return $result[$format];
            }
        }

        return $result;
    }

    /**
     * Fast send an invoice
     *
     * @param int    $id invoice id
     * @param string $to   format to convert to
     * @param string $subject   subject of message
     * @param string $body   body of message
     * @param mixed  $formats    format(s) to convert invoice to
     * @param string $template   template to use
     */
    public function sendInvoice($id, $to, $subject, $body, $formats = array(), $template = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        $attaches = $this->convertInvoice($id, $formats, $template);

        return Minerva::sendMail(Minerva::getFromAddress(), $to, $subject, $body, $attaches);
    }

    /**
     * Get invoice draft
     *
     * @param int   $id draft id
     */
    public function getDraft($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        require_once MINERVA_BASE . '/lib/Email.php';

        $drafts = new Minerva_EmailMapper();
        return $drafts->find($id);
    }

    /**
     * Callback for Minerva comments.
     *
     * @param int   $id invoice id
     */
    public function commentCallback($id)
    {
        require_once dirname(__FILE__) . '/base.php';
        return $GLOBALS['minerva_invoices']->exists($id);
    }

    /**
     * Return available types
     *
     * @param int   $perm Permission level
     */
    public function getTypes($perm = Horde_Perms::SHOW)
    {
        require_once dirname(__FILE__) . '/base.php';
        return Minerva::getTypes($perm);
    }

    /**
     * Return available statuses
     *
     * @param int   $perm Permission level
     * @param string   $type Type id
     */
    public function getStatuses($perm = Horde_Perms::SHOW, $type = null)
    {
        require_once dirname(__FILE__) . '/base.php';
        return Minerva::getStatuses($perm, $type);
    }

    /**
     * Do authorization response. Sends email on authorization status change.
     *
     * @param int   $id Application internal id
     * @param array   $params Response parameters
     */
    public function authorizationResponse($id, $params)
    {
        require_once dirname(__FILE__) . '/base.php';

        $invoice = $GLOBALS['minerva_invoices']->getOne($id);
        if ($invoice instanceof PEAR_Error) {
            return $invoice;
        }

        $type_name = Minerva::getTypeName($invoice['invoice']['type']);

        $body = sprintf(_("Changed status to %s for %s %s"),
                        $params['status_name'], $type_name, $invoice['invoice']['name']);

        $subject = sprintf(_("Authorization response for %s name %"),
                        $type_name, $invoice['invoice']['name']);

        Minerva::sendMail(Minerva::getFromAddress(),
                        Minerva::getFromAddress(),
                        $subject,
                        $body);

        return true;
    }

    /**
     * Lists alarms for a given moment.
     *
     * @param integer $time  The time to retrieve alarms for.
     * @param string $user   The user to retrieve alarms for. All users if null.
     *
     * @return array  An array of UIDs
     */
    public function listAlarms($time, $user = null)
    {
        require_once dirname(__FILE__) . '/base.php';

        if (!Minerva::hasRecurrencePermission(Horde_Perms::SHOW)) {
            return array();
        }

        $recurrances = new Minerva_Recurrences();
        $invoices = $recurrances->getAll();
        if (empty($invoices) || $invoices instanceof PEAR_Error) {
            return $invoices;
        }

        $day = $_SERVER['REQUEST_TIME'] + 86400 * $GLOBALS['prefs']->getValue('recurrence_notify_before');
        $dfm = $GLOBALS['prefs']->getValue('date_format');

        $methods = array();
        $methods['notify']['show'] = array('__app' => 'minerva');

        $alarm_list = array();
        foreach ($invoices as $invoice) {
            $next = $recurrances->getNext($invoice['rstart'], $invoice['rend'], $invoice['rinterval']);
            if ($next < $_SERVER['REQUEST_TIME'] || $next > $day) {
                continue;
            }

            $title = sprintf(_("On %s you must create an invoice for client %s."), strftime($dfm, $next), $invoice['name']);
            $alarm_list[] = array('id' => $invoice['invoice_id'],
                                'user' => Horde_Auth::getAuth(),
                                'start' => $_SERVER['REQUEST_TIME'],
                                'end' => $next,
                                'methods' => array_keys($methods),
                                'params' => $methods,
                                'title' => $title);
        }

        return $alarm_list;
    }

    /**
     * List available time categories
     */
    public function listTimeObjectCategories()
    {
        require_once dirname(__FILE__) . '/base.php';

        $categories = array();

        if (Minerva::hasOutcomePermission(Horde_Perms::SHOW)) {
            $categories['outcome'] = _("Outcome");
        }

        if (Minerva::hasRecurrencePermission(Horde_Perms::SHOW)) {
            $categories['recurrence'] = _("Recurrence");
        }

        if (Minerva::hasTypePermission('invoice', Horde_Perms::SHOW)) {
            $categories['invoice'] = _("Invoice");
        }

        return $categories;
    }

    /**
     * Lists invoices as time objects.
     *
     * @param array $categories  The time categories (from listTimeObjectCategories) to list.
     * @param mixed $start       The start date of the period.
     * @param mixed $end         The end date of the period.
     */
    public function listTimeObjects($categories, $start, $end)
    {
        require_once dirname(__FILE__) . '/base.php';
        $data = array();


        if (in_array('recurrence', $categories) && Minerva::hasRecurrencePermission(Horde_Perms::SHOW)) {

            $from = $start->timestamp();
            $to = $end->timestamp();

            $recurrances = new Minerva_Recurrences();
            foreach ($recurrances->getAll() as $invoice) {
                $next = $recurrances->getNext($invoice['rstart'], $invoice['rend'], $invoice['rinterval']);
                if ($next == 0 || $next < $from || ($next+1) > $to) {
                    continue;
                }
                $data[] = array('title' => _("Recurrence: ") . $invoice['name'],
                                'id' => $invoice['invoice_id'],
                                'start' => date('Y-m-d\TH:i:s', $next),
                                'end' => date('Y-m-d\TH:i:s', $next + 1),
                                'params' => array('invoice_id' => $invoice['invoice_id'],
                                                'type' => 'recurrence'),
                                'link' => Horde_Util::addParameter(Horde::applicationUrl('show.php', true), array('invoice_id' => $invoice['invoice_id'], 'type' => 'recurrence')));
            }

        }

        if (in_array('outcome', $categories) && Minerva::hasOutcomePermission(Horde_Perms::SHOW)) {

            require_once MINERVA_BASE . '/lib/Outcome.php';
            $outcomes = new Minerva_OutcomeMapper();
            $from = $start->year . '-' . $start->month . '-' . $start->mday;
            $to = $end->year . '-' . $end->month . '-' . $end->mday;
            $criteria = array('fields' => array('id', 'client_name', 'due'),
                            'tests' => array(array('field' => 'paid', 'test' => 'IS', 'value' => null),
                                            array('field' => 'due', 'test' => '>', 'value' => $from),
                                            array('field' => 'due', 'test' => '<', 'value' => $to)));

            $list = $outcomes->getAll($criteria);
            foreach ($list as $invoice) {
                $data[] = array('title' => _("Outcome") . ': ' . $invoice['client_name'],
                                'id' => $invoice['id'],
                                'start' => date('Y-m-d\TH:i:s', strtotime($invoice['due'])),
                                'end' => date('Y-m-d\TH:i:s', strtotime($invoice['due']) + 1),
                                'params' => array('invoice_id' => $invoice['id'],
                                                'type' => 'outcome'));
            }

        }

        if (in_array('invoice', $categories) && Minerva::hasTypePermission(Horde_Perms::SHOW)) {

            $from = $start->timestamp();
            $to = $end->timestamp();
            $criteria = array('invoice' => array('type' => 'invoice',
                                                'status' => array('pending')));

            foreach ($GLOBALS['minerva_invoices']->getList($criteria) as $invoice) {
                $istart = strtotime($invoice['date']) + $invoice['expire'] * 86400;
                if ($istart < $from || ($istart+1) > $to) {
                    continue;
                }

                $data[] = array('title' => _("Invoice past due") . ': ' . $invoice['company'],
                                'id' => $invoice['invoice_id'],
                                'start' => date('Y-m-d\TH:i:s', $istart),
                                'end' => date('Y-m-d\TH:i:s', $istart + 1),
                                'params' => array('invoice_id' => $invoice['invoice_id'],
                                                'type' => 'income'));
            }
        }

        return $data;
    }

    /**
     * List items
     *
     * @param array $criteria Filter criteria
     */
    public function listCostObjects($criteria = array())
    {
        require_once dirname(__FILE__) . '/base.php';
        require_once dirname(__FILE__) . '/Item.php';

        $results = array();
        $items = new Minerva_ItemMapper();
        foreach ($items->find() as $item) {
            $results[] = iterator_to_array($item);
        }

        return array(array('category' => _("Items"),
                        'objects'  => $results));
    }
}