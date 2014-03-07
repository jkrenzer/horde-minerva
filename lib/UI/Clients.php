<?php
/**
 * Clients UI
 *
 * This class is used to manage clients data, and provide an
 * central client selection logic
 *
 * Clientmap is used to combine clients data into a new field.
 *
 * If an attribute foo is requested, it will check if there
 * is a mapping from foo key to foobar. If the mapped key is an
 * array, it will combine the fields by the delimiter.
 *
 * Example 1
 *  $clientmap['foo'] => $clientmap['foobar']
 *  $clientmap['foo'] gets the value of $clientmap['foobar']
 *
 * Example 2 - combine a postal address
 *  $clientmap['postal_address'] =>
 *      array('workAddress', "\n", 'workCity', ' - '
 *            'workPostalCode', "\n", 'workCountry');
 *
 *  $clientmap['postal_address'] becomses
 *      workAddress
 *      workPostalCode - workCity
 *      workCountry
 *
 * $Horde: incubator/minerva/lib/UI/Clients.php,v 1.24 2010/02/01 10:32:06 jan Exp $
 *
 * TODO Build search form in client selection
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Horde_UI_Clients {

    /**
     * Build clients lists
     *
     * @param char $letter Starting letter of client name
     *
     * @return  array   client list
     */
    public function getAll($letter = null)
    {
        static $data;

        if ($letter == null && isset($data[0])) {
            return $data[0];
        } elseif (isset($data[$letter])) {
            return $data[$letter];
        }

        if (!$GLOBALS['registry']->hasMethod('clients/searchClients')) {
            $data[$letter] = array();
            return array();
        }

        try {
            $result = $GLOBALS['registry']->call('clients/searchClients',
                                                    array('names' => array(''),
                                                        'fields' => array('name')));
        } catch (Horde_Exception $e) {
            return array();
        }

        $result = current($result);
        if (is_null($letter)) {
            $data[$letter] = $result;
            return $result;
        }

        $clients = array();
        foreach ($result as $id => $client) {
            if ((isset($client['name']) && strtoupper(substr($client['name'], 0, 1)) == $letter) ||
                (isset($client['company']) && strtoupper(substr($client['company'], 0, 1)) == $letter)) {
                $clients[$client['__key']] = $client;
            }
        }

        $data[$letter] = $clients;
        return $clients;
    }
    /**
    * Get avaiable client fields
    *
    * @return  array   attribute list
    */
    public function getFields()
    {
        if (!$GLOBALS['registry']->hasMethod('clients/clientFields')) {
            return array();
        }

        $cache = $GLOBALS['injector']->getInstance('Horde_Cache');
        $fields = $cache->get('cui_fields', $GLOBALS['conf']['cache']['driver']);
        if ($fields) {
            return unserialize($fields);
        }

        try {
            $result = $GLOBALS['registry']->call('clients/clientFields');
        } catch (Horde_Exception $e) {
            return array();
        }

        $fields = array();
        foreach ($result as $attribute) {
            $fields[$attribute['name']] = $attribute['label'];
        }

        $cache->set('cui_fields', serialize($fields));
        return $fields;
    }

    /**
     * Get client formated data
     *
     * @param string $id Client id
     *
     * @return  array   Client data
     */
    public function getOne($id)
    {
        if (!$GLOBALS['registry']->hasMethod('clients/getClient')) {
            return array();
        }

        try {
            $client = $GLOBALS['registry']->call('clients/getClient', array($id));
        } catch (Horde_Exception $e) {
            return array();
        }

        $this->_format($client);

        return $client;
    }

    /**
     * Get fromated clients list
     *
     * @param array $ids IDs of the clients to get
     * @param string $letter Letter we are retreiving
     *
     * @return  array   client list
     */
    public function getAllFormatted($ids, $letter = null)
    {
        $clients = $this->getAll($letter);

        foreach ($clients as $id => $client) {
            if (!in_array($id, $ids)) {
                unset($clients[$id]);
                continue;
            }
            $this->_format($clients[$id]);
        }

        return $clients;
    }

    /**
     * Remap client data
     */
    private function _format(&$client)
    {
        static $clientmap;

        if ($clientmap === null) {
            $clientmap = Horde::loadConfiguration('clientmap.php', 'clientmap');
            if ($clientmap instanceof PEAR_Error) {
                $clientmap = array();
            }
        }

        if (empty($client['company'])) {
            $client['company'] = $client['name'];
        }

        // remap clients data
        foreach ($clientmap as $orig => $map) {
            if (is_array($map)) {
                $client[$orig] = '';
                foreach ($map as $part) {
                    if (isset($client[$part])) {
                        $client[$orig] .= $client[$part];
                    } elseif ($part == "\n" || $part == "\n\n" || $part == ' ') {
                        $client[$orig] .= $part;
                    }
                }
            } elseif (isset($client[$map]) && !empty($client[$map])) {
                $client[$orig] = $client[$map];
            }
        }

        // Set name from new turba attributes
        if (empty($client['name']) && isset($client['firstname'])) {
            $client['name'] = $client['firstname'] . ' ' . $client['lastname'];
        }

        // recmove internal data
        foreach ($client as $key => $val) {
            if (substr($key, 0, 2) == '__') {
                unset($client[$key]);
            }
            if ($key == 'vat' && strpos($val, ' ')) {
                $client[$key] = str_replace(' ', '', $val);
            }
        }
    }

    /**
     * Search clients
     *
     * @param string $search  Search string
     *
     * @return array of client data
     */
    public function search($search, $fields = array())
    {
        if (empty($search)) {
            return array();
        }

        if (empty($fields)) {
            $fields = array('name', 'company');
        }

        if (!$GLOBALS['registry']->hasMethod('clients/searchClients')) {
            return array();
        }

        try {
            $results = $GLOBALS['registry']->call('clients/searchClients',
                                                    array('names' => array($search),
                                                            'fields' => $fields,
                                                            'matchBegin' => true));
        } catch (Horde_Exception $e) {
            return array();
        }

        foreach ($results[$search] as $i => $client) {
            $this->_format($results[$search][$i]);
        }

        return $results[$search];
    }

    /**
     * Get selection window link
     */
    static public function getSelectionLink($container = '')
    {
        Horde::addScriptFile('popup.js', 'horde');
        $openwin = '<a href="javascript:void(0)" onclick="javascript:Horde.popup({url: \'%s\'})">%s</a>';
        return sprintf($openwin, Horde::applicationUrl('client_selection.php'), _("Select client from address book"));
    }

    /**
     * Get autocompletion script
     *
     * @param string $container Result handler
     * @param string $field  Client data to search
     */
    static public function getAutocompleter($input, $field = 'name')
    {
        self::_jsAutcomplete();

        $GLOBALS['notification']->push('observeAutcomplete("' . $input . '", "' . $field . '");', 'javascript');

        $html = '<span id="' . $input . '_loading_img" style="display:none;">'
            . Horde::img('loading.gif', _("Loading..."), '', $GLOBALS['registry']->getImageDir('horde')) . '</span>'
            . '<div id="' . $input . '_results" class="autocomplete"></div>';

        return $html;
    }

    /**
     * Render client selection
     *
     * @param array $clients  Form name to copy data to
     */
    public function renderAutocomplete($clients, $field = 'name')
    {
        if (empty($clients)) {
            return '';
        }

        if ($clients instanceof PEAR_Error) {
            $clients = array(array($field => $clients->getMessage()));
        }

        $html = '<ul>';
        foreach ($clients as $client) {
            $id = Horde_Serialize::serialize($client, Horde_Serialize::JSON, Horde_Nls::getCharset());
            $html .= '<li id=\'' . $id . '\'>' . $client[$field] . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Render client selection
     *
     * @param string $formname  Form name to copy data to
     * @param string $focus Filed to focus after client selection
     */
    public function render($formname, $focus = 'name')
    {
        Horde::addScriptFile('tables.js', 'horde');
        $this->js($formname, $focus);

        $show = Horde_Util::getGet('show');
        $page = Horde_Util::getGet('page');
        $clients = $this->getAll($show);

        $headers = $this->getFields();
        if ($headers instanceof PEAR_Error) {
            echo $headers->getMessage();
            return;
        }

        $fields = unserialize($GLOBALS['prefs']->getValue('clients_fields'));

        if (!empty($fields)) {
            // Show only user defined fields
            foreach ($headers as $key => $value) {
                if (!in_array($key, $fields)) {
                    unset($headers[$key]);
                }
            }
        }

        $this->_header($headers);
        $this->_pager($clients, $show);
        $this->_letters($show, Horde::selfUrl());

        $clients = array_slice($clients, $page * 20, 20);
        $clients = $this->getAllFormatted(array_keys($clients), $show);

        foreach ($clients as $id => $client) {
            $link = Horde::link('#', '', '', '', "clientUpdate('" . $id . "')");
            echo '<tr valign="top">';
            foreach ($headers as $key => $value) {
                echo '<td>' . $link . $client[$key] . '</a></td>' . "\n";
            }
            echo '</tr>';
        }
        echo '</table>';

        echo $this->_serialize($clients);
    }

    private function _serialize($clients)
    {
        $html = '<script type="text/javascript">' . "\n"
            . 'clients = ' . Horde_Serialize::serialize($clients, Horde_Serialize::JSON, Horde_Nls::getCharset())
            . "\n" . '</script>';

        return $html;
    }

    /**
     * Selection pager
     */
    private function _pager($clients, $show)
    {
        if (count($clients) < 20) {
            return;
        }

        $pager = new Horde_Ui_Pager('page',
                                    Horde_Variables::getDefaultVariables(),
                                    array('num' => count($clients),
                                          'url' => Horde::selfUrl(),
                                          'page_count' => 10,
                                          'perpage' => 20));

        $pager->preserve('show', $show);
        echo $pager->render();
    }

    /**
     * Selection letters filter
     */
    private function _letters($show, $viewurl)
    {
        echo '<div class="pager">';
        if (is_null($show)) {
            echo '<strong>(' . _("All") . ')</strong>&nbsp;';
        } else {
            echo Horde::link($viewurl) . _("All") . '</a>&nbsp;' . "\n";
        }
        for ($i = 65; $i < 91; $i++) {
            $a = chr($i);
            if ($show == $a) {
                echo '<strong>(' . $a . ')</strong>&nbsp;';
            } else {
                echo Horde::link(Horde_Util::addParameter($viewurl, 'show', $a)) . $a . '</a>&nbsp;' . "\n";
            }
        }
        echo '</div>';
    }

    /**
     * Selection table header
     */
    private function _header($headers)
    {
        echo '<table class="striped sortable" style="width: 100%;"><thead><tr>';
        foreach ($headers as $key => $value) {
            echo '<th class="control">' . $value . '</th>' . "\n";
        }
        echo '</tr></thead>';
    }

    /**
     * Get JS to update fields
     */
    public function js($formname, $focus)
    {
        echo <<<EOT
<script type="text/javascript">
function clientUpdate(id) {

    var data = clients[id];

    elements = opener.document.$formname.elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.substr(0, 7) != 'client_') {
            continue;
        }

        index = elements[i].name.substr(7);
        if (data[index]) {
            if (index == 'name') {
                elements[i].value = data['company'];
            } else {
                elements[i].value = data[index];
            }
        }
    }

    if (opener.document.getElementById('$focus')) {
        opener.document.getElementById('$focus').focus();
    }

    window.close();
}
</script>
EOT;
    }

    /**
     * Get JS to autocomplete fields
     */
    private function _jsAutcomplete()
    {
        static $js;

        if ($js) {
            return;
        }

        Horde::addScriptFile('prototype.js', 'horde');
        Horde::addScriptFile('builder.js', 'horde');
        Horde::addScriptFile('effects.js', 'horde');
        Horde::addScriptFile('controls.js', 'horde');

        $url = Horde::applicationUrl('client_selection.php');
        $url_auto = Horde_Util::addParameter($url, 'method', 'auto');

        $js = <<<EOT
function updateClient(text, li) {
    client_data = li.id.evalJSON(li.id);
    for (data in client_data) {
        if ($('client_' + data)) {
            $('client_' + data).value = client_data[data];
        }
    }
}

function observeAutcomplete(input, field) {
    Event.observe(window, "load", function() {
        new Ajax.Autocompleter(input, input + "_results", "$url_auto" + "&field=" + field,
                               { afterUpdateElement: updateClient} ); }
    );
}
EOT;

        $GLOBALS['notification'] = &Horde_Notification::singleton();
        $GLOBALS['notification']->push($js, 'javascript');
    }

}
