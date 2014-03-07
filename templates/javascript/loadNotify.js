<?php
$notify_id=Util::getGet('notify_id');$old_invoice=$GLOBALS['minerva']->getOne($notify_id);$notfy_data=array();$notfy_data['client']=$old_invoice['client'];$notfy_data['articles']=array();$notfy_data['articles'][]=array('id'=>$notify_id,'name'=>$old_invoice['invoice']['name'],'price'=>$old_invoice['invoice']['total'],'total'=>$old_invoice['invoice']['total']);$notfy_data['articles'][]=array('id'=>'','name'=>_("Notification costs"),'price'=>(string)$prefs->getValue('notification_cost'),'total'=>(string)$prefs->getValue('notification_cost'));?><script type="text/javascript">function loadNotfy(){var old_invoice=<?php echo Horde_Serialize::serialize($notfy_data,Horde_Serialize::JSON,Horde_Nls::getCharset());?>;for(group in old_invoice){if(group=='articles'){count=old_invoice[group].length;for(var article=0;article<count;article++){for(attribute in old_invoice[group][article]){name=group+'_data_'+(article+1)+'_'+attribute;if(document.invoice[name]){if(attribute!='price'&&attribute!='discount'&&attribute!='qt'){document.invoice[name].value=old_invoice[group][article][attribute];}
value=old_invoice[group][article][attribute];value=value.replace(/\./g,currencies[defaultCurrency]['mon_decimal_point']);document.invoice[name].value=value;}}
articleAddFields()}
continue;}
for(field in old_invoice[group]){name=group+'_'+field;if(document.invoice[name]){document.invoice[name].value=old_invoice[group][field];}}}
if(old_invoice['client']['obligated']==1){document.invoice['client_obligated'].checked=true;}
calcPrices();}
window.onload=loadNotfy();</script>