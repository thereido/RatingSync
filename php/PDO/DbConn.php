<?php

namespace RatingSync;

use Exception;
use PDO;

class DbConn
{
//*RT*    private SshTunnel|null $tunnel = null;
    private PDO|null $conn = null;

    /**
     * @throws Exception
     */
/*RT*
    public function __construct( bool $ssh = true )
    {

        if ( $ssh ) {

            $host = Constants::DB_SSH_HOSTNAME;
            $port = Constants::DB_SSH_PORT;
            $username = Constants::DB_SSH_USERNAME;
            $privkey = Constants::DB_SSH_PRIVKEY_FILENAME;

            // FIXME knownHosts
            //$this->ssh = new SshTunnel($host, $username, $privkey, $port, $knownHosts);
            $this->tunnel = new SshTunnel($host, $username, $privkey, $port);

        }

    }
*RT*/

    /**
     * @throws Exception
     */
    public function connect(): PDO
    {
        if ( ! empty($this->conn) ) {

            return $this->conn;

        }

        $db_host = Constants::DB_HOSTNAME;
        $db_name = Constants::DB_DATABASE;
        if (Constants::DB_MODE == Constants::DB_MODE_TEST) {
            $db_name = Constants::DB_TEST_DATABASE;
        }

        $dsn = "mysql:host=$db_host;dbname=$db_name";
        try {

            $this->conn = new PDO( $dsn, Constants::DB_ADMIN_USER, Constants::DB_ADMIN_PWD );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        } catch(PDOException $e) {

            logError("DB connection failed: " . $e->getMessage(), __CLASS__."::".__FUNCTION__.":".__LINE__);
            throw $e;

        }

        return $this->conn;

    }

}
