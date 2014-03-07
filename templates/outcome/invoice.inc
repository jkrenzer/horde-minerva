<?php echo $tabs->render('topay'); ?>
<div class="header" style="float: right">

<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('outcome/edit.php'), array('action' => 'update', 'id' => $id)) ?>">
<?php echo _("Edit") ?></a>

</div>
<div class="header"><?php echo $title ?></div>

<table>
<tr valign="top">

<?php

$meta = $outcomes->formMeta();
$date_fromat = $prefs->getValue('date_format');
foreach ($meta as $key => $column) {
    if (!isset($column['section'])) {
        $meta[$key]['section'] = '__base';
    }
    if (!isset($column['type'])) {
        $meta[$key]['type'] = 'text';
    }
}

foreach ($meta['__sections'] as $section_key => $section) {

    if ($section_key == 'section' || $section_key == 'type') {
        continue;
    }

    ?>

    <td class="box">
    <table class="striped">
    <tr>
    <td class="control" colspan="2"><strong><?php echo $section['desc'] ?></strong></td>
    <?php
    foreach ($meta as $key => $column) {
        if ($key == '__sections' || $column['section'] != $section_key || !isset($invoice[$key])) {
            continue;
        }
        echo '<tr><td>' . $column['humanName'] . '</td><td>';
        switch ($column['type']) {
        case 'monthdayyear':
            echo strftime($date_fromat, strtotime($invoice[$key]));
            break;
        case 'currency':
        case 'number':
            echo Minerva::format_price($invoice[$key], $invoice['currency']);
            break;
        default:
            echo $invoice[$key];
            break;
        }
        echo '</td></tr>' . "\n";
    }
    ?>
    </tr>
    </table>
    </td>

<? } ?>

</tr>
</table>
