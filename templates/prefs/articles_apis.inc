<?php
/**
 * $Horde: incubator/minerva/templates/prefs/articles_apis.inc,v 1.4 2009/09/15 15:11:00 duck Exp $
 */

@define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/Minerva.php';

$apis = array();
foreach ($GLOBALS['registry']->listAPIs() as $api) {
    $name = $GLOBALS['registry']->hasInterface($api);
    if ($GLOBALS['registry']->hasMethod('listCostObjects', $name)) {
        $apis[$api] = $GLOBALS['registry']->get('name', $name);
    }
}

if (($default_articlesApis = $prefs->getValue('articles_apis')) == null) {
    $default_articlesApis = array_keys($apis);
} else {
    $default_articlesApis = unserialize($default_articlesApis);
}

?>

<?php echo _("Select modules from which link articles.") ?><br />
<select name="articles_apis[]" class="fixed" multiple="multiple" size="<?php sizeof($apis) ?>">
<?php foreach ($apis as $id => $name): ?>
    <option value="<?php echo $id ?>"<?php if (in_array($id, $default_articlesApis)) echo ' selected="selected"' ?>><?php echo htmlspecialchars($name) ?></option>
<?php endforeach; ?>
</select>
<br />
<br />
