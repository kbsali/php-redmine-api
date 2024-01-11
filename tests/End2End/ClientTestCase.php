<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End;

use PDO;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;

class ClientTestCase extends TestCase
{
    private $apiKey;

    private $sqliteFile;

    private $sqliteBackup;

    public function setUp(): void
    {
        $this->sqliteFile = dirname(__FILE__, 3) . '/.docker/redmine_data/sqlite/redmine.db';
        $this->sqliteBackup = dirname(__FILE__, 3) . '/.docker/redmine_data/sqlite/redmine.db.bak';

        // Create backup of database
        copy($this->sqliteFile, $this->sqliteBackup);

        $pdo = new PDO('sqlite:' . $this->sqliteFile);

        // Get admin user to check sqlite connection
        $stmt = $pdo->prepare('SELECT * FROM users WHERE login = :login;');
        $stmt->execute([':login' => 'admin']);
        $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update admin user
        $stmt = $pdo->prepare('UPDATE users SET must_change_passwd = :must_change_passwd WHERE id = :id;');
        $stmt->execute([':id' => $adminUser['id'], ':must_change_passwd' => 0]);

        // Enable rest api
        $stmt = $pdo->prepare('INSERT INTO settings(name, value) VALUES(:name, :value);');
        $stmt->execute([':name' => 'rest_api_enabled', ':value' => 1]);

        $this->apiKey = sha1((string) time());

        // Create api token for admin user
        $stmt = $pdo->prepare('INSERT INTO tokens(user_id, action, value, created_on, updated_on) VALUES(:user_id, :action, :value, :created_on, :updated_on);');
        $stmt->execute([
            ':user_id' => $adminUser['id'],
            ':action' => 'api',
            ':value' => $this->apiKey,
            ':created_on' => $adminUser['last_login_on'],
            ':updated_on' => $adminUser['last_login_on'],
        ]);
    }

    public function tearDown(): void
    {
        // Restore database from backup
        copy($this->sqliteBackup, $this->sqliteFile);
    }

    protected function getNativeCurlClient(): NativeCurlClient
    {
        return new NativeCurlClient(
            'http://redmine:3000',
            $this->apiKey
        );
    }
}
