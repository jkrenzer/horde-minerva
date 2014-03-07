<?php $link = Horde::link('javascript:void(0)',_("Add article"), '', '', "articleAdd('" . $item['id'] . "')"); ?>
<tr valign="top">
<td><?php echo $link . $item['id'] ?></a></td>
<?php if (isset($item['model'])): ?>
<td><?php echo $link . $item['model'] ?></a></td>
<?php endif; ?>
<td><?php echo $link . $item['name'] ?></a></td>
<?php if (isset($item['desc'])): ?>
<td><?php echo $link . $item['desc'] ?></a></td>
<?php endif; ?>
<?php if (isset($item['price'])): ?>
<td><?php echo $link . $item['price'] ?></a></td>
<?php endif; ?>
<?php if (isset($item['discount'])): ?>
<td><?php echo $link . $item['discount'] ?></a></td>
<?php endif; ?>
<?php if (isset($item['tax'])): ?>
<td>
<?php
echo $link ;
if (empty($item['tax'])) {
    echo '';
} elseif (isset($taxes[$item['tax']])) {
    echo $taxes[$item['tax']]['name'];
} else {
    echo $item['tax'];
}
?>
</a>
</td>
<?php endif; ?>
<?php if (isset($item['unit'])): ?>
<td>
<?php
echo $link ;
if (isset($units[$item['unit']])) {
    echo $units[$item['unit']];
} else {
    echo '';
}
?>
</a>
</td>
<?php endif; ?>
</tr>
