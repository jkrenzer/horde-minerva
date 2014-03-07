<?php
/**
 * Minerva_Convert_Notify:: defines an API for creating
 * printable late payment notification document.
 *
 * $Horde: incubator/minerva/lib/Convert/Custom.php,v 1.3 2009/01/06 17:50:59 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert_Custom extends Minerva_Convert {

    /**
     * Template we are currently using
     *
     * @var template
     */
    protected $_template = 'custom';

    /**
    * Convert invoice
    *
    * @return Pathe wehere the file was saved or PEAR_Error on failure
    */
    public function convert($id, $data)
    {
        $result = parent::_getTemplate();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $this->client_postal_address = nl2br($data['postal_address']);
        $this->subject = trim($data['custom']['subject']);
        $this->body = strpos($data['custom']['body'], '<') ? $data['custom']['body'] : nl2br($data['custom']['body']);

        return $this->_saveContent();
    }

    /**
     * Applies default formatting
     *
     * @param mixed  &$value Value to format
     * @param string $key    Index key in the array
     */
    private function formatCurrency(&$value, $key)
    {
        $value = Minerva::format_price($value, $key);
    }
}
