<?php
/**
 * $Horde: incubator/minerva/templates/prefs/outcome_sortdir.inc,v 1.4 2009/09/15 15:11:00 duck Exp $
 */

require_once 'Horde/RDO.php';
if (($default_sortdir = $prefs->getValue('outcome_sortdir')) == null) {
    $default_sortdir = Horde_RDO::SORT_DESC;
}

?>

<?php echo _("Direction of outcome sorting") ?><br />
<select name="outcome_sortdir" class="fixed">
<option value="<?php echo Horde_RDO::SORT_DESC; ?>"<?php if ($default_sortdir == Horde_RDO::SORT_DESC) echo ' selected="selected"' ?>><?php echo _("Descending") ?></option>
<option value="<?php echo Horde_RDO::SORT_ASC; ?>"<?php if ($default_sortdir == Horde_RDO::SORT_ASC) echo ' selected="selected"' ?>><?php echo _("Ascending") ?></option>
</select>
<br />
<br />
