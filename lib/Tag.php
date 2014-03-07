<?php
/**
 * The Minerva_Groups:: class and Minerva_GroupsMapper:: provides Rdo extension
 * used in for handling Companys.
 *
 * $Horde: incubator/minerva/lib/Tag.php,v 1.14 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Tag extends Horde_Rdo_Base {
}

class Minerva_TagMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_tags';

    /**
     */
    public function getAdapter()
    {
        $config = Horde::getDriverConfig('storage');
        $config['adapter'] = $config['phptype'];

        return Horde_Db_Adapter::factory($config);
    }

    /**
     * Load fields metadata
     */
    public function formMeta()
    {
        $data['id'] = array(
            'humanName' => _("Id"),
            'type' => 'int'
        );

        $data['name'] = array(
            'humanName' => _("Name")
        );

        $data['created'] = array(
            'humanName' => _("Created")
        );

        $data['updated'] = array(
            'humanName' => _("Updated")
        );

        return $data;
    }

    /**
     * Returns cached tags
     *
     * @return array
     */
    static public function getTags()
    {
        static $tags;

        if ($tags) {
            return $tags;
        }

        if (($tags = Minerva::getCache('tags'))) {
            return $tags;
        }

        $mapper = new Minerva_TagMapper();
        $query = new Horde_Rdo_Query($mapper);
        $query->sortBy('name DESC');

        $tags = array(1 => _("Other"));
        foreach (new Horde_Rdo_List($query) as $tag) {
            $tags[$tag->id] = $tag->name;
        }

        Minerva::setCache('tags', $tags);

        return $tags;
    }

    /**
     * Wrap to expire cache
     */
    public function create($fields)
    {
        parent::create($fields);
        Minerva::expireCache('tags');
    }

    public function update($object, $fields = null)
    {
        parent::update($object, $fields);
        Minerva::expireCache('tags');
    }

    public function delete($object)
    {
        parent::delete($object);
        Minerva::expireCache('tags');
    }
}
