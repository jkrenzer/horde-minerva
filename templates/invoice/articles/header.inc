<table class="striped sortable" style="width: 100%;" id="articles_list">
<thead>
<tr>
<th><?php echo _("Id") ?></th>
<?php if (isset($items[0]['model'])): ?>
<th><?php echo _("Model") ?></th>
<?php endif; ?>
<th><?php echo _("Name") ?></th>
<?php if (isset($items[0]['desc'])): ?>
<th><?php echo _("Description") ?></th>
<?php endif; ?>
<?php if (isset($items[0]['price'])): ?>
<th><?php echo _("Price") ?></th>
<?php endif; ?>
<?php if (isset($items[0]['discount'])): ?>
<th><?php echo _("Discount") ?></th>
<?php endif; ?>
<?php if (isset($items[0]['tax'])): ?>
<th><?php echo _("Tax") ?></th>
<?php endif; ?>
<?php if (isset($items[0]['unit'])): ?>
<th><?php echo _("Unit") ?></th>
<?php endif; ?>
</tr>
</thead>
