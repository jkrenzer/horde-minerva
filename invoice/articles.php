<?php
/**
 * Minerva intractive article list
 *
 * $Horde: incubator/minerva/invoice/articles.php,v 1.35 2009/12/10 17:42:33 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

require_once dirname(__FILE__) . '/../lib/base.php';

$title = _("Articles");
$vars = Horde_Variables::getDefaultVariables();
$listurl = Horde::applicationUrl('invoice/articles.php');
$tabs = new Horde_Ui_Tabs('api', $vars);
$taxes = Minerva::getTaxes();
$units = Minerva::getUnits();

// get avaibale input modules
foreach ($registry->listAPIs() as $api) {
    if ($registry->hasMethod($api . '/listCostObjects')) {
        $name = $registry->get('name', $registry->hasInterface($api));
        $tabs->addTab($name, $listurl, $api);
    }
}
$api = Horde_Util::getFormData('api', 'invoices');

// get avaibale categories
if ($registry->hasMethod($api . '/listCostCategories')) {

    $cats = $registry->call($api . '/listCostCategories');
    $form = new Horde_Form($vars, '', 'category');

    $v = &$form->addVariable(_("Cateogry"), 'category', 'enum', false, false, false, array('enum' => $cats, 'prompt' => true));
    $v->setAction(Horde_Form_Action::factory('submit'));
    $v->setOption('trackchange', true);

    if (($cat = Horde_Util::getFormData('category')) !== null) {
        $items = $registry->call($api . '/listCostObjects', array(array($cat)));
    } else {
        $items = array();
    }

} elseif (empty($api)) {

    $items = PEAR::raiseError(_("There is no application to retrieve data from."));

} else {

    $items = $registry->call("$api/listCostObjects", array(array()));

}

Horde::addScriptFile('article.js', 'minerva', true);
Horde::addScriptFile('stripe.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';

echo $tabs->render($api);

if ($items instanceof PEAR_Error) {
    echo '<div class="notice">' . $items->getMessage() . '</div>';
} elseif (!empty($items[0]['objects'])) {
    $items = $items[0]['objects'];
    require MINERVA_TEMPLATES . '/invoice/articles/header.inc';
    foreach ($items as $id => &$item) {
        if (isset($item['price'])) {
            $item['price'] = Minerva::format_price($item['price'], null, false);
        }
        require MINERVA_TEMPLATES . '/invoice/articles/row.inc';
    }
    require MINERVA_TEMPLATES . '/invoice/articles/footer.inc';
} elseif (!isset($cats)) {
    echo '<div class="header">' . _("No items") . '</div>';
}

if (isset($cats)) {
    $form->renderActive(null, $vars, $listurl, 'post');
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
