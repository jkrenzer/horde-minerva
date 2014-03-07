<?php
/**
 * $Horde: incubator/minerva/lib/UI/VarRenderer/invoices_xhtml.php,v 1.7 2009/12/10 17:42:34 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Horde_UI_VarRenderer_invoices_xhtml extends Horde_Ui_VarRenderer_TablesetHtml {

    function _renderVarInput_tableset($form, &$var, &$vars)
    {
        $header = $var->type->getHeader();
        $name   = $var->getVarName();
        $values = $var->getValues();
        $form_name = $form->getName();
        $var_name = $var->getVarName() . '[]';
        $checkedValues = $var->getValue($vars);
        $actions = $this->_getActionScripts($form, $var);
        $function_name = 'select'  . $form_name . $var->getVarName();
        $enable = _("Select all");
        $disable = _("Select none");
        $invert = _("Invert selection");
        $view_url = Horde::applicationUrl('invoice/print.php');

        Horde::addScriptFile('tables.js', 'horde');

        $html = <<<EOT
<script type="text/javascript">
function $function_name()
{
    for (var i = 0; i < document.$form_name.elements.length; i++) {
        f = document.$form_name.elements[i];
        if (f.name != '$var_name') {
            continue;
        }
        if (arguments.length) {
            f.checked = arguments[0];
        } else {
            f.checked = !f.checked;
        }
    }
}
</script>
<a href="#" onclick="$function_name(true); return false;">$enable</a>, 
<a href="#" onclick="$function_name(false); return false;">$disable</a>, 
<a href="#" onclick="$function_name(); return false;">$invert</a>
<table style="width: 100%" class="sortable striped" id="tableset_' . $name . '"><thead><tr>
<th>&nbsp;</th>
EOT;

        foreach ($header as $col_title) {
            $html .= sprintf('<th class="leftAlign">%s</th>', $col_title);
        }
        $html .= '</tr></thead>';

        if (!is_array($checkedValues)) {
            $checkedValues = array();
        }
        $i = 0;
        foreach ($values as $value => $displays) {
            $checked = (in_array($value, $checkedValues)) ? ' checked="checked"' : '';
            $html .= '<tr>' .
                sprintf('<td style="text-align: center"><input id="%s[]" type="checkbox" name="%s[]" value="%s"%s%s /></td>',
                        $name,
                        $name,
                        $value,
                        $checked,
                        $actions);
            foreach ($displays as $key => $col) {
                if ($key == 'name') {
                    $col = Horde::link(Horde_Util::addParameter($view_url, array('invoice_id' =>  $value, 'noprint' => 1)), _("View invoice"), '', '_blank')
                         . $col . '</a>';
                }
                $html .= sprintf('<td>&nbsp;%s</td>', $col);
            }
            $html .= '</tr>' . "\n";
            $i++;
        }

        $html .= '</table>'
              . '<a href="#" onclick="' . $function_name . '(true); return false;">' . $enable . '</a>, '
              . '<a href="#" onclick="' . $function_name . '(false); return false;">' . $disable . '</a>, '
              . '<a href="#" onclick="' . $function_name . '(); return false;">' . $invert . '</a>';

        return $html;
    }

}
