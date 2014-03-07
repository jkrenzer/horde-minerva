<?php

$block_name = _("Income expected");

/**
 * Horde_Block_Minerva_invoices:: Implementation of the Horde_Block API
 * to show the current user's queries.
 *
 * $Horde: incubator/minerva/lib/Block/invoices.php,v 1.20 2009/10/17 11:16:23 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Horde_Block_minerva_invoices extends Horde_Block {

    var $_app = 'minerva';

    /**
     * Set default block params
     *
     * @return string   The title text.
     */
    function _params()
    {
        require_once dirname(__FILE__) . '/../base.php';

        $statuses = Minerva::getStatuses();
        $types = Minerva::getTypes();

        $params = array('limit' => array('name' => _("How many invoices to show"),
                                         'type' => 'int',
                                         'default' => 5),
                        'status' => array('name' => _("Status"),
                                            'type' => 'enum',
                                            'values' => $statuses,
                                            'default' => 'pending'),
                        'type' => array('name' => _("Type"),
                                        'type' => 'enum',
                                        'values' => $types,
                                        'default' => 'invoice'));

        return $params;
    }

    /**
     * The title to go in this block.
     *
     * @return string   The title text.
     */
    function _title()
    {
        require_once dirname(__FILE__) . '/../base.php';

        $types = Minerva::getTypes();
        $type = $this->_params['type'] ? $this->_params['type'] : 'invoice';

        $img_dir = $GLOBALS['registry']->getImageDir('horde');
        $title = sprintf(_("%s expected"), $types[$type]) . ' <small>'
               . Horde::link(Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'type', $type), sprintf(_("New %s"), Minerva::getTypeName($type)))
               . Horde::img('edit.png', '', '', $img_dir) . '</a> '
               . Horde::link(Horde_Util::addParameter(Horde::applicationUrl('list/changestatus.php'), 'type', $type), _("Mark as paid"))
               . Horde::img('tick.png', '', '', $img_dir) . '</a> '
               . ' </small>';

        return $title;
    }

    /**
     * The content to go in this block.
     *
     * @return string   The content
     */
    function _content()
    {
        require_once dirname(__FILE__) . '/../base.php';

        $criteria = array();

        if ($this->_params['status']) {
            $criteria['invoice']['status'] = array($this->_params['status']);
        } else {
            $this->_params['status'] = 'pending';
        }

        if ($this->_params['type']) {
            $criteria['invoice']['type'] = $this->_params['type'];
        } else {
            $this->_params['type'] = 'invoice';
        }

        if ($this->_params['limit']) {
            $criteria['limit'] = array('from' => 0, 'count' => $this->_params['limit']);
        }

        $list = $GLOBALS['minerva_invoices']->getList($criteria);
        if ($list instanceof PEAR_Error) {
            return $list->getMessage();
        }

        $url = Horde::applicationUrl('invoice/invoice.php');
        Horde::addScriptFile('stripe.js', 'horde');

        $total = 0;
        $html = '<table class="striped" style="width: 100%">';
        foreach ($list as $i) {
            $total += $i['total'];
            $html .= '<tr style="text-align: right;">';
            $html .= '<td>' . Horde::link(Horde_Util::addParameter($url, 'invoice_id', $i['invoice_id']), $i['name']) . $i['name'] . '</td>';
            $html .= '<td>' . strftime($GLOBALS['prefs']->getValue('date_format'), strtotime($i['date'])) . '</td>';
            $html .= '<td>' . $i['company']  . '</td>';
            $html .= '<td>' . Minerva::format_price($i['total'], $i['currency'])  . '</td>';
            $html .= '</tr>';
        }
        $html .= '<tr style="text-align: right;">';
        $html .= '<td colspan="4">' . _("Total") . ': ' . Minerva::format_price($total)  . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

}
