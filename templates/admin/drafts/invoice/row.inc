<tr>
<td><?php echo Horde::link(Horde_Util::addParameter($expunge_url, 'template', $id), _("Expunge"), '', '_blank') . $expunge_img ?></a></td>
<td><?php echo Horde::link(Horde_Util::addParameter($preview_url, 'template', $id), _("Preview"), '', '_blank') . $id ?></a></td>
<td><?php echo $data['name'] ?></td>
<td><?php echo Minerva::format_date($mtime) ?></td>
</tr>