<div class="smallheader" style="float: right; margin: 2px;">
<form id="list_actions" name="list_actions" style="display: inline;">
<?php echo _("Action") ?>:
<select onchange="window.location = this.value">
<option>- - - - - - - </option>
<option value="<?php echo Horde::applicationUrl('notifies/changestatus.php'); ?>"><?php echo _("Changes status") ?></option>
<option value="<?php echo Horde::applicationUrl('notifies/printall.php'); ?>"><?php echo _("Print all") ?></option>
<option value="<?php echo Horde::applicationUrl('notifies/send.php'); ?>"><?php echo _("Send by email") ?></option>
<option value="<?php echo Horde::applicationUrl('notifies/download.php'); ?>"><?php echo _("Download in a zipfile") ?></option>
</select>
</form>
</div>