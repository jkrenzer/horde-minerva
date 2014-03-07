<?php
/**
 * Minerva implementation for HTMLDOC document conversion
 * Can dirctly export to pdf
 *
 * $Horde: incubator/minerva/lib/Convert/htmldoc.php,v 1.9 2009/07/09 08:18:14 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert_htmldoc extends Minerva_Convert_Invoice {

    /**
     * Hash containing HTMLDOC call parameters
     *
     * @var $call
     */
    private $_call = '%s --webpage --jpeg --no-title --format pdf14 --embedfonts --fontsize 8 --size %s --charset %s -f ';

    /**
     * Constructs a new Abiword object.
     *
     * @param array $params  A hash containing connection parameters.
     */
    public function __construct($params)
    {
        $this->_params = $params;
        $this->_call = sprintf($this->_call, $params['program_path'], $params['page_size'], $params['charset']);
    }

    /**
    * Convert to PDF
    */
    public function pdf($file)
    {
        // Load document
        $data = file_get_contents($file);

        // Replace view link with local paths
        $viewurl = Horde_Util::addParameter(Horde::applicationUrl('view.php', true), 'file', null);
        $data = str_replace($viewurl, $this->_params['template_path'], $data);

        // convert to charset if needed
        if ($this->_params['charset'] != Horde_Nls::getCharset()) {
            $data = Horde_String::convertCharset($data, 'utf-8', str_replace('-', '', $this->_params['charset']));
        }

        // Save to tmp file
        $tmp = $file . '.toPDF';
        file_put_contents($tmp, $data);
        unset($data);

        // invoke htmldoc to convert to pdf
        $pdf_file = substr($file, 0, -4) . 'pdf';
        $macro = $this->_call . $pdf_file . ' ' . $tmp;
        $output = shell_exec($macro);

        if (file_exists($pdf_file)) {
            unlink($tmp);
            return $pdf_file;
        } else {
            return PEAR::raiseError(sprintf(_("Cannot convert type %s."), 'pdf'), null, null, null, $output);
        }
    }
}
