<script type="text/javascript">

var listMenuItems = [
  {
    name: '<?php echo _("Delete") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.location = '<?php echo Horde::applicationUrl('invoice/delete.php') ?>?invoice_id=' + row.id;
        }
    }
  },{
    name: '<?php echo _("Edit") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.location = '<?php echo Horde::applicationUrl('invoice/invoice.php') ?>?invoice_id=' + row.id;
        }
    }
  },{
    name: '<?php echo _("Dupliate") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.location = '<?php echo Horde::applicationUrl('invoice/invoice.php') ?>?type=invoice&clone_id=' + row.id;
        }
    }
  },{
    name: '<?php echo _("Print") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.open('<?php echo Horde::applicationUrl('invoice/print.php') ?>?invoice_id=' + row.id);
        }
    }
  },{
    name: '<?php echo _("Change status") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.location = '<?php echo Horde::applicationUrl('list/changestatus.php') ?>?status=paid&invoices[]=' + row.id;
        }
    }
  },{
    name: '<?php echo _("Export") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.open('<?php echo Horde::applicationUrl('invoice/export.php') ?>?invoice_id=' + row.id);
        }
    }
<?php foreach ($conf['convert']['types'] as $convert_type) { ?>
  },{
    name: '<?php echo sprintf(_("Convert to %s"), $convert_type) ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.open('<?php echo Horde::applicationUrl('invoice/convert.php') ?>?type=<?php echo $convert_type ?>&invoice_id=' + row.id);
        }
    }
<?php } ?>
  },{
    name: '<?php echo _("Send") ?>',
    callback: function(e) {
        var row = Event.findElement(e,'tr');
        if (row.id) {
            window.location = '<?php echo Horde::applicationUrl('invoice/send.php') ?>?invoice_id=' + row.id;
        }
    }
   }

];

/* Stuff to do immediately when page is ready. */
document.observe('dom:loaded', function() {
    new Proto.Menu({
        selector: '#invoices_lists',
        className: 'context firefox',
        menuItems: listMenuItems
    })
});

</script>


<div class="header">&nbsp;</div>
<table id="invoices_lists" class="striped sortable" style="width: 100%;">
<thead>
<tr style="text-align: right;">
<th class="nosort">&nbsp;</th>
<th><?php echo _("No.") ?></th>
<th><?php echo _("Company") ?></th>
<th><?php echo _("Total bare") ?></th>
<th class="nosort"><?php echo _("Total tax") ?></th>
<th class="nosort"><?php echo _("Total") ?></th>
<th class="nosort"><?php echo _("Status") ?></th>
<th class="nosort"><?php echo _("Date") ?></th>
<th class="nosort"><?php echo _("Expire") ?></th>
</thead>
</tr>
<tbody>
