<?php

namespace RatingSync;

require "vendor/autoload.php";

use Exception;
use phpseclib3\Crypt\DH\PrivateKey;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class SshTunnel
{
    private SSH2|null $ssh = null;
    private string $hostname;
    private int $port;
    private string $username;
    private string $privkeyFilename;
    private string $privkeyPassphrase;
    private array|null $knownHosts;

/*
$dbhost_pubkey_dev_ed25519 = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAICPQqE8kh+BZ7iQFXz3l2a6VwpSCjVWV4BuhoFf+qoXt";
$dbhost_pubkey_dev_rsaEasy = "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDcwmYtK52HxI1XtysdvGjpm+XbdI9NqRmpCFqSI15F3pn5IL5bYrhKAatL2b7cBtyHhbAC5VIAOeVm8bdvInzjD6d2RvSTx4/AHLM3nc5lH/cHr3MUJKcbrwUNK60rHQ5Vdz5jsCkzJvmQWiNAsTBJy31C2/5+Ee/FeUpkpWckBtqtZGyQ4/vSVnZ3YyLszzagkSk62+FoEEs2iVu0jX5NZarQmHrnFXkWyyMBIwG816siUBxlK1KhP7S//zvRrPA2lG+m0My715ZC645/9QxJWViXnHdY/vr4NBlhaolxDDUXoARUNiYHTGOAlMpESLYEmhsemZKejKX22Smko0p2MRx09yGYICwoH77teSwGhx5Xs+nRnkS0P/YkxbZKx3qkqzmwaI1Gntmbm8YXmU5hyx6HOpbRh5kbyaYDk5IIhiRJ6M0czCfnONlNhkSzMLsPSkpqArPyBHI12dajceXXqgZVoC3js0Mnmp0+9WCj7j+dkVSEhkdJ0MyxMIAkMPU=";
$known_hosts = ["ed25519" => $dbhost_pubkey_dev_ed25519, "rsaEasy" => $dbhost_pubkey_dev_rsaEasy];
*/

    /**
     * @param string $hostname Hostname or IP Address of the server to connect to
     * @param string $username Connect to the server as a user on the server.
     * @param string $privkeyFilename Full path and filename for SSH private key (ED25519 or RSA)
     * @param int $port Custom SSH port
     * @param array $knownHosts If there is a value the connection will only proceed if the host's SSH fingerprint is found in the array. If you do not want to validate the host fingerprint leave this empty.
     * @throws Exception
     */
    public function __construct(string $hostname, string $username, string $privkeyFilename, string $privkeyPassphrase, int $port = 22, array|null $knownHosts = null)
    {
        // FIXME - validation

        $this->hostname = $hostname;
        $this->port = $port;
        $this->username = $username;
        $this->privkeyFilename = $privkeyFilename;
        $this->privkeyPassphrase = $privkeyPassphrase;
        $this->knownHosts = $knownHosts;

        $this->connect();
    }

    /**
     * @throws Exception
     */
    private function connect(): void
    {
        $this->ssh = new SSH2($this->hostname, $this->port);

        if ( ! empty($this->knownHosts) ) {
            $this->validateHostFingerprint( $this->ssh, $this->known_hosts ); // FIXME get known_hosts
        }

        $key = $this->loadKey();
        $loggedIn = $this->ssh->login($this->username, $key);

        if ( ! $loggedIn ) {
            $this->ssh = null;
            $e = new Exception("SSH login failed. Host $this->hostname fingerprint matches and user's key is loaded. Make sure connecting from the command line works for $this->username@$this->hostname:$this->port with key '$this->privkeyFilename'.");
            logError( $e->getMessage(), __CLASS__."::".__FUNCTION__.":" );
            throw $e;
        }
    }


    /**
     * @throws Exception
     */
    private function validateHostFingerprint( SSH2 $ssh, array $known_hosts ): void
    {
        echo date_create()->format("H:i:s:v") . "  getServerPublicHostKey() begin\n";
        $actual_server_fingerprint = $ssh->getServerPublicHostKey();
        echo date_create()->format("H:i:s:v") . "  getServerPublicHostKey() end\n";

        if ( array_search($actual_server_fingerprint, $known_hosts) === false ) {
            echo "Host key verification failed.\n";
            echo "Actual: $actual_server_fingerprint\n";
            echo "Known Hosts:\n";
            foreach ( $known_hosts as $known_host ) {
                echo "\t$known_host\n";
            }
            exit(1);
        }
    }

    /**
     * @throws Exception
     */
    function loadKey(): PrivateKey
    {
        $keyContents = file_get_contents($this->privateKeyFilename);

        if ( ! $keyContents ) {
            throw new Exception( "Unable to read SSH private key \"$this->privateKeyFilename\"\n");
        }

        try {

            return PublicKeyLoader::loadPrivateKey( $keyContents, $this->privkeyPassphrase );

        }
        catch (\Exception $e) {
            throw new Exception("Unable to load private key ($this->privateKeyFilename): " . $e->getMessage() . "\n");
        }
    }

}
