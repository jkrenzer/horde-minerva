<?php
/**
 * Minerva_Driver:: defines an API for implementing storage backends for Minerva.
 *
 * $Horde: incubator/minerva/lib/Driver.php,v 1.14 2009/01/06 17:50:58 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Driver {

    /**
     * Hash containing connection parameters.
     *
     * @var array
     */
    static private $instance;

    /**
     * Hash containing connection parameters.
     *
     * @var array
     */
    private $params = array();

    /**
     * Handle for the current database connection.
     *
     * @var DB
     */
    public $db;

    /**
     * Handle for the current database connection, used for writing. Defaults
     * to the same handle as $db if a separate write database is not required.
     *
     * @var DB
     */
    public $write_db;

    /**
     * Constructs a new SQL storage object.
     *
     * @param array $params  A hash containing connection parameters.
     */
    public function __construct($params)
    {
        $this->params = $params;
        $this->connect();
    }

    /**
     * Attempts to return a reference to a concrete Minerva_Driver instance based
     * on $driver.
     *
     * It will only create a new instance if no Minerva_Driver instance with the
     * same parameters currently exists.
     *
     * This should be used if multiple storage sources are required.
     *
     * This method must be invoked as: $var = &Minerva_Driver::singleton()
     *
     * @return mixed  The created concrete Minerva_Driver instance, or false on
     *                error.
     */
    static public function singleton()
    {
        if (!self::$instance) {
            $params = Horde::getDriverConfig('storage', 'sql');
            self::$instance = new Minerva_Driver($params);
        }

        return self::$instance;
    }

    /**
     * Returns the database internal charset
     */
    public function getCharset()
    {
        return $this->params['charset'];
    }

    /**
     * Attempts to open a persistent connection to the SQL server.
     *
     * @return boolean  True on success; exits (Horde::fatal()) on error.
     */
    private function connect()
    {
        Horde::assertDriverConfig($this->params, 'storage',
                                  array('phptype', 'charset'));

        if (!isset($this->params['database'])) {
            $this->params['database'] = '';
        }
        if (!isset($this->params['username'])) {
            $this->params['username'] = '';
        }
        if (!isset($this->params['hostspec'])) {
            $this->params['hostspec'] = '';
        }

        /* Connect to the SQL server using the supplied parameters. */
        require_once 'DB.php';
        $this->write_db = DB::connect($this->params,
                                        array('persistent' => !empty($this->params['persistent'])));
        if ($this->write_db instanceof PEAR_Error) {
            Horde::fatal($this->write_db, __FILE__, __LINE__);
        }

        // Set DB portability options.
        switch ($this->write_db->phptype) {
        case 'mssql':
            $this->write_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS | DB_PORTABILITY_RTRIM);
            break;
        default:
            $this->write_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS);
        }

        /* Check if we need to set up the read DB connection seperately. */
        if (!empty($this->params['splitread'])) {
            $params = array_merge($this->params, $this->params['read']);
            $this->db = DB::connect($params,
                                      array('persistent' => !empty($params['persistent'])));
            if ($this->db instanceof PEAR_Error) {
                Horde::fatal($this->db, __FILE__, __LINE__);
            }

            // Set DB portability options.
            switch ($this->db->phptype) {
            case 'mssql':
                $this->db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS | DB_PORTABILITY_RTRIM);
                break;
            default:
                $this->db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS);
            }

        } else {
            /* Default to the same DB handle for the writer too. */
            $this->db =& $this->write_db;
        }
    }
}