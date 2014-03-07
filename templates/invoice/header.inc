<div id="postError" name="postError" class="notice" style="display: none;"><?php echo _("Submitting") ?></div>

<form id="invoice" name="invoice" action="<?php echo Horde::applicationUrl('invoice/post.php'); ?>" method="post" accept-charset="utf-8">

<input id="invoice_id" name="invoice_id" type="hidden" value="<?php echo $invoice_id ?>" />
<input id="client_id" name="client_id" type="hidden" value="0" />
<input id="invoice_type" name="invoice_type" type="hidden" value="<?php echo $type ?>" />

<div class="header"><?php echo _("Edit") . ' ' . $types[$type] ?></div>

<div class="control">

<div style="float: left; <?php if (!$invoice_id) { echo 'display: none;'; }  ?>" id="invoice_controls" name="invoice_controls">

<?php if ($registry->hasMethod('forums/doComments')) { ?>
&#160;<a href="javascript:void(0)" onclick="Horde.popup({ url: '<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/comments.php'), 'invoice_id', $invoice_id, false); ?>'});" title="<?php echo _("Comment invoice") ?>">
<img src="<?php echo $imagedir ?>/add_group.png" alt="<?php echo _("Comments") ?>" /> <?php echo _("Comments") ?></a>
<?php } ?>

&#160;<a href="javascript:void(0)" onclick="Horde.popup({ url: '<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/log.php'), 'invoice_id', $invoice_id, false); ?>'});" title="<?php echo _("Log") ?>">
<img src="<?php echo $imagedir ?>/calendar.png" alt="<?php echo _("Log") ?>" /> <?php echo _("Log") ?></a>

&#160;<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/currency.php'), 'invoice_id', $invoice_id, false); ?>" title="<?php echo _("Menage currencies") ?>">
<img src="<?php echo $registry->getImageDir() ?>/taxes.png" alt="<?php echo _("Currency") ?>" /> <?php echo _("Currency") ?></a>

&#160;<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/send.php'), 'invoice_id', $invoice_id, false); ?>" title="<?php echo _("Send by email") ?>">
<img src="<?php echo $imagedir ?>/mail.png" alt="<?php echo _("Send") ?>" /> <?php echo _("Send") ?></a>

&#160;<a href="javascript:void(0)" onclick="Horde.popup({ url: '<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/export.php'), 'invoice_id', $invoice_id, false); ?>'});" title="<?php echo _("Export to XML") ?>">
<img src="<?php echo $imagedir ?>/mime/xml.png" alt="<?php echo _("Export") ?>" /> <?php echo _("Export") ?></a>

<?php
$convert_url =  Horde_Util::addParameter(Horde::applicationUrl('invoice/convert.php'), 'invoice_id', $invoice_id);
foreach ($conf['convert']['types'] as $convert_type) {
    echo '&#160;<a href="javascript:void(0)" onclick="Horde.popup({ url: \'' . Horde_Util::addParameter($convert_url, 'type', $convert_type) . '\'});" title="' . sprintf(_("Convert to %s"), $convert_type) . '">';
    echo '<img src="' . $imagedir . '/mime/' . $convert_type .'.png" alt="' . _("Convert") . '" />' .  strtoupper($convert_type) . '</a> ';
}
?>

&#160;<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/delete.php'), 'invoice_id', $invoice_id, false); ?>" title="<?php echo _("Delete") ?>">
<img src="<?php echo $imagedir ?>/delete.png" alt="<?php echo _("Delete") ?>" /> <?php echo _("Delete") ?></a>

<?php if ($registry->hasMethod('payment/authorizationRequest')) { ?>
&#160;<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/addtopayment.php'), 'invoice_id', $invoice_id, false); ?>" title="<?php echo _("Add to payment") ?>">
<img src="<?php echo $imagedir ?>/organizing.png" alt="<?php echo _("Add to payment") ?>" /> <?php echo _("Payment") ?></a>
<?php } ?>

<?php

if ($type != 'invoice') {

    // Clone
    $clone_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), array('clone_id' => $invoice_id));
    foreach ($types as $id => $name) {
        if ($id == $type) {
            continue;
        }
        echo '&#160;<a href="'. Horde_Util::addParameter($clone_url, 'type', $id) . '" title="' . sprintf(_("Clone to %s"), $name) . '">';
        echo '<img src="' . $imagedir . '/edit.png" alt="' . sprintf(_("Clone to %s"), $name) . '" /> ' . $name . '</a>';

    }

} else {

    // Mark as reoccurance
    $recurrence_url = Horde_Util::addParameter(Horde::applicationUrl('recurrence/edit.php'), array('invoice_id' => $invoice_id));
    echo '&#160;<a href="'. $recurrence_url . '" title="' . _("Add to recurrence") . '">';
    echo '<img src="' . $imagedir . '/mime/quicktime.png" alt="' . _("Add to recurrence") . '" /> ' . _("Recurrence") . '</a>';

}

?>

</div>

&#160;<a href="#" onclick="MinervaInvoice.save();"><img src="<?php echo $imagedir ?>/save.png" title="<?php echo _("Save") ?>" /> <?php echo _("Save") ?></a>

&#160;<a href="<?php echo Horde_Util::addParameter(Horde::applicationUrl('invoice/print.php'), 'invoice_id', $invoice_id, false); ?>" title="<?php echo _("Print") ?>" onclick="return MinervaInvoice.checkPrint();" target="_blank">
<img src="<?php echo $imagedir ?>/print.png" alt="<?php echo _("Print") ?>" /> <?php echo _("Print") ?></a>

</div>

<script type="text/javascript">

var articlesMenuItems = [
  {
    name: '<?php echo _("Delete") ?>',
    callback: MinervaInvoice.deleteArticle
  },{
    name: '<?php echo _("Dupliate") ?>',
    callback: MinervaInvoice.duplicateArticle
  },{
    name: '<?php echo _("Move up") ?>',
    callback: function(e) {
        MinervaInvoice.moveArticle(e, "up")
    }
  },{
    name: '<?php echo _("Move down") ?>',
    callback: function(e) {
        MinervaInvoice.moveArticle(e, "down")
    }
  }

]

</script>

