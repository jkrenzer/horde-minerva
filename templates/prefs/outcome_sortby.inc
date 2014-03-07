<?php
/**
 * $Horde: incubator/minerva/templates/prefs/outcome_sortby.inc,v 1.5 2009/09/15 15:11:00 duck Exp $
 */

@define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/Minerva.php';
require_once MINERVA_BASE . '/lib/RDO/Outcome.php';

$meta = Minerva_OutcomeMapper::formMeta();
foreach ($meta as $key => $value) {
    if (!isset($value['humanName'])) {
        unset($meta[$key]);
        continue;
    }
    $meta[$key] = $value['humanName'];
}

if (($default_sortby = $prefs->getValue('outcome_sortby')) == null) {
    $default_sortby = key($meta);
}
?>

<?php echo _("Column of outcome sorting") ?><br />
<select name="outcome_sortby" class="fixed">
<?php foreach ($meta as $id => $name): ?>
    <option value="<?php echo $id ?>"<?php if ($id == $default_sortby) echo ' selected="selected"' ?>><?php echo htmlspecialchars($name) ?></option>
<?php endforeach; ?>
</select>
<br />
<br />
