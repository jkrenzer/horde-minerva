<div class="smallheader" style="float: right; margin: 2px;">

<?php
echo Horde::link('#', _("Print invoice list"), 'smallheader', '', 'Horde.popup({url: \'' . Horde::applicationUrl('list/print.php') . '\' })') . _("Print") . '</a>' . " | \n";
?>

<form id="list_actions" name="list_actions" style="display: inline;">
<?php echo _("Action") ?>:
<select onchange="window.location = this.value">
<option>- - - - - - - </option>
<option value="<?php echo Horde::applicationUrl('list/changestatus.php'); ?>"><?php echo _("Change status") ?></option>
<option value="<?php echo Horde::applicationUrl('list/changetag.php'); ?>"><?php echo _("Change tag") ?></option>
<option value="<?php echo Horde::applicationUrl('list/printall.php'); ?>"><?php echo _("Print all") ?></option>
<option value="<?php echo Horde::applicationUrl('list/custom.php'); ?>"><?php echo _("Custom content") ?></option>
<option value="<?php echo Horde::applicationUrl('list/taxes.php'); ?>"><?php echo _("Calculate taxes") ?></option>
<option value="<?php echo Horde::applicationUrl('list/send.php'); ?>"><?php echo _("Send by email") ?></option>
<option value="<?php echo Horde::applicationUrl('list/download.php'); ?>"><?php echo _("Download in a zipfile") ?></option>
<option value="<?php echo Horde::applicationUrl('list/pending.php'); ?>"><?php echo _("Pending list per customer") ?></option>
<option value="<?php echo Horde::applicationUrl('list/combine.php'); ?>"><?php echo _("Combine as new") ?></option>
<option value="<?php echo Horde::applicationUrl('list/clients.php'); ?>"><?php echo _("Export clients") ?></option>
<option value="<?php echo Horde::applicationUrl('list/data.php'); ?>"><?php echo _("Export list in a datafile (CSV)") ?></option>
<option value="<?php echo Horde::applicationUrl('list/export.php'); ?>"><?php echo _("Export in XML") ?></option>
<?php

if ($registry->hasMethod('spread/import')) {
    $name = sprintf(_("Export invoice list into %s"), $registry->get('name', 'spread'));
    echo '<option value="' . Horde::applicationUrl('list/spread.php') . '">' . $name . '</option>';
}

if (!empty($conf['finance']['resellers'])) {
    echo '<option value="' . Horde::applicationUrl('list/resellers.php') . '">' . _("Resellers") . '</option>';
}

?>
</select>
</form>

</div>
