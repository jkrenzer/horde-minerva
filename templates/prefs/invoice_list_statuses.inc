<?php
/**
 * $Horde: incubator/minerva/templates/prefs/invoice_list_statuses.inc,v 1.4 2009/09/15 15:11:00 duck Exp $
 */

@define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/Minerva.php';

$statuses = Minerva::getStatuses();
if (($default_status = $prefs->getValue('invoice_list_statuses')) == null) {
    $default_status = array('pending');
} else {
    $default_status = unserialize($default_status);
}

?>

<?php echo _("Default invoice status listings.") ?><br />
<select name="invoice_list_statuses[]" class="fixed" multiple="multiple" size="<?php echo sizeof($statuses) ?>">
<?php foreach ($statuses as $id => $status): ?>
    <option value="<?php echo $id ?>"<?php if (in_array($id, $default_status)) echo ' selected="selected"' ?>><?php echo htmlspecialchars($status) ?></option>
<?php endforeach; ?>
</select>
<br />
<br />
