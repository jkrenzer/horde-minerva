<?php
/**
 * $Horde: incubator/minerva/lib/prefs.php,v 1.10 2009/06/10 05:24:23 slusarz Exp $
 */

function handle_outcome_sortby($updated)
{
    $value = Horde_Util::getFormData('outcome_sortby', 'id');
    return $GLOBALS['prefs']->setValue('outcome_sortby', $value);
}

function handle_clients_fields($updated)
{
    $fields = Horde_Util::getFormData('clients_fields');
    if (empty($fields)) {
        return $GLOBALS['prefs']->setValue('clients_fields', serialize(array()));
    } else {
        return $GLOBALS['prefs']->setValue('clients_fields', serialize($fields));
    }
}