<?php

$block_name = _("Outcome expected");

/**
 * Horde_Block_Minerva_outcome:: Implementation of the Horde_Block API
 * to show the current user's queries.
 *
 * $Horde: incubator/minerva/lib/Block/outcome.php,v 1.19 2009/10/17 11:16:23 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Horde_Block_minerva_outcome extends Horde_Block {

    var $_app = 'minerva';

    /**
     * Set default block params
     *
     * @return string   The title text.
     */
    function _params()
    {
        require_once dirname(__FILE__) . '/../base.php';

        $params = array('limit' => array('name' => _("How many outcomes to show"),
                                         'type' => 'int',
                                         'default' => 0));

        return $params;
    }

    /**
     * The title to go in this block.
     *
     * @return string   The title text.
     */
    function _title()
    {
        $img_dir = $GLOBALS['registry']->getImageDir('horde');

        $title = _("Outcome expected") . ' <small>'
               . Horde::link(Horde::applicationUrl('outcome/edit.php'), _("New Invoice"))
               . Horde::img('edit.png', '', '', $img_dir) . '</a> '
               . Horde::link(Horde::applicationUrl('outcome/topay.php'), _("Opened outcomes"))
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
        require_once MINERVA_BASE . '/lib/Outcome.php';

        $outcomes = new Minerva_OutcomeMapper();
        $criteria = array('fields' => array('id', 'client_name', 'total', 'due'),
                          'tests' => array(array('field' => 'paid', 'test' => 'IS', 'value' => null)));

        if ($this->_params['limit']>0) {
            $criteria['limit'] = array('from' => 0, 'count' => $this->_params['limit']);
        }

        $list = $outcomes->getAll($criteria);
        if ($list instanceof PEAR_Error) {
            return $list->getMessage();
        }

        $total = 0;
        $url = Horde::applicationUrl('outcome/invoice.php');
        Horde::addScriptFile('stripe.js', 'horde');
        $html = '<table class="striped" style="width: 100%">';

        foreach ($list as $i) {
            $total += $i['total'];
            $html .= '<tr style="text-align: right;">';
            $html .= '<td>' . Horde::link(Horde_Util::addParameter($url, 'invoice_id', $i['id']), $i['id']) . $i['id'] . '</td>';
            $html .= '<td>' . strftime($GLOBALS['prefs']->getValue('date_format'), strtotime($i['due'])) . '</td>';
            $html .= '<td>' . $i['client_name']  . '</td>';
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
