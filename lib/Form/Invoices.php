<?php
/**
 * Object processing invoices selection
 *
 * $Horde: incubator/minerva/lib/Form/Invoices.php,v 1.1 2009/11/09 20:00:04 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Form_Invoices extends Horde_Form {

    /**
     * Get renderer
     */
    public function getRenderer()
    {
        return new Horde_Form_Renderer(array('varrenderer_driver' => array('minerva', 'invoices_xhtml')));
    }

}
