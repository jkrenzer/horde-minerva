<?php
/**
 * $Horde: incubator/minerva/templates/prefs/clients_fields.inc,v 1.5 2008/01/07 15:59:45 duck Exp $
 */

define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/UI/Clients.php';

$clients = new Horde_UI_Clients();
$fields = $clients->getFields();

if (($default_clientsFields = $prefs->getValue('clients_fields')) === null) {
    $default_clientsFields = $fields;
} else {
    $default_clientsFields = unserialize($default_clientsFields);
}

?>

<?php echo _("Select client fields to display.") ?><br />
<select name="clients_fields[]" class="fixed" multiple="multiple" size="<?php sizeof($fields) ?>">
<?php foreach ($fields as $id => $name): ?>
    <option value="<?php echo $id ?>"<?php if (in_array($id, $default_clientsFields)) echo ' selected="selected"' ?>><?php echo htmlspecialchars($name) ?></option>
<?php endforeach; ?>
</select>
<br />
<br />
