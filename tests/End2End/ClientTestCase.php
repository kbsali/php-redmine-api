<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End;

use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;

class ClientTestCase extends TestCase
{
    const V050101 = '050101';

    const V050008 = '050008';

    const V050007 = '050007';

    private $instances = [];

    public static function getAvailableRedmineVersions(): array
    {
        return [
            static::V050101, // 5.1.1
            // static::V050008, // 5.0.8 - not available as Docker image
            static::V050007, // 5.0.7
        ];
    }

    public function setUp(): void
    {
        foreach (static::getAvailableRedmineVersions() as $redmineVersion) {
            $this->setUpRedmine($redmineVersion);
        }
    }

    private function setUpRedmine(string $redmineVersion): void
    {
        $this->instances[$redmineVersion] = [
            'sqliteFile' => dirname(__FILE__, 3) . '/.docker/redmine-' . $redmineVersion . '_data/sqlite/redmine.db',
            'sqliteBackup' => dirname(__FILE__, 3) . '/.docker/redmine-' . $redmineVersion . '_data/sqlite/redmine.db.bak',
            'redmineUrl' => 'http://redmine-' . $redmineVersion . ':3000',
            'apiKey' => sha1((string) time()),
        ];

        // Create backup of database
        copy($this->instances[$redmineVersion]['sqliteFile'], $this->instances[$redmineVersion]['sqliteBackup']);

        $now = new DateTimeImmutable();
        $pdo = new PDO('sqlite:' . $this->instances[$redmineVersion]['sqliteFile']);

        // Get admin user to check sqlite connection
        $stmt = $pdo->prepare('SELECT * FROM users WHERE login = :login;');
        $stmt->execute([':login' => 'admin']);
        $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update admin user
        $stmt = $pdo->prepare('UPDATE users SET must_change_passwd = :must_change_passwd WHERE id = :id;');
        $stmt->execute([':id' => $adminUser['id'], ':must_change_passwd' => 0]);

        // Enable rest api
        $stmt = $pdo->prepare('INSERT INTO settings(name, value, updated_on) VALUES(:name, :value, :updated_on);');
        $stmt->execute([
            ':name' => 'rest_api_enabled',
            ':value' => 1,
            ':updated_on' => $now->format('Y-m-d H:i:s.u'),
        ]);

        // Create api token for admin user
        $stmt = $pdo->prepare('INSERT INTO tokens(user_id, action, value, created_on, updated_on) VALUES(:user_id, :action, :value, :created_on, :updated_on);');
        $stmt->execute([
            ':user_id' => $adminUser['id'],
            ':action' => 'api',
            ':value' => $this->instances[$redmineVersion]['apiKey'],
            ':created_on' => $now->format('Y-m-d H:i:s.u'),
            ':updated_on' => $now->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function tearDown(): void
    {
        foreach (static::getAvailableRedmineVersions() as $redmineVersion) {
            // Restore database from backup
            copy($this->instances[$redmineVersion]['sqliteBackup'], $this->instances[$redmineVersion]['sqliteFile']);
            unlink($this->instances[$redmineVersion]['sqliteBackup']);
        }
    }

    protected function getNativeCurlClient(string $redmineVersion): NativeCurlClient
    {
        if (! array_key_exists($redmineVersion, $this->instances)) {
            throw new InvalidArgumentException('Redmine version ' . $redmineVersion . ' is not supported.');
        }

        return new NativeCurlClient(
            $this->instances[$redmineVersion]['redmineUrl'],
            $this->instances[$redmineVersion]['apiKey']
        );
    }
}
