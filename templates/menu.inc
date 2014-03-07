<div id="menu">
<div class="menu" style="float: right;">
<form action="<?php echo Horde::applicationUrl('list/list.php') ?>" method="get" id="menu_type" name="menu_type">
<?php echo _("Select") ?>:
<select id="type" name="type" onchange="document.menu_type.submit()">
<option>- - - - - - - </option>;
<?php
    $types = Minerva::getTypes();
    foreach ($types as $key => $val) {
        echo '<option value="' . $key . '">' . $val . "</option>";
    }
?>
</select>
</form>
</div>
 <?php echo Minerva::getMenu('string') ?>
</div>
<?php $GLOBALS['notification']->notify(array('listeners' => 'status')) ?>
