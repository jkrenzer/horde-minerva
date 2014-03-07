<?php
/**
 * XML Outcome export
 *
 * $Horde: incubator/minerva/lib/Outcome/Export/XML.php,v 1.8 2009/01/06 17:51:00 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Outcome_Export_XML extends Outcome_Export {

    public $contentType = 'text/xml';
    public $extension = 'xml';
    public $charset = 'utf-8';

    public function process($invoices)
    {
        $serializer = new XML_Serializer(array(XML_SERIALIZER_OPTION_INDENT => '    ',
                                               XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
                                               XML_SERIALIZER_OPTION_XML_ENCODING => $this->charset,
                                               XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => false,
                                               XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML,
                                               XML_SERIALIZER_OPTION_ROOT_NAME => 'outcomes',
                                               XML_SERIALIZER_OPTION_RETURN_RESULT => true,
                                               XML_SERIALIZER_OPTION_CDATA_SECTIONS => true,
                                               XML_SERIALIZER_OPTION_DEFAULT_TAG => 'outcomes'));

        return $serializer->serialize(array('outcome' => $invoices));
    }
}