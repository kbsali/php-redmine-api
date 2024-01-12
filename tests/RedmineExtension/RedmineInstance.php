<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use DateTimeImmutable;
use InvalidArgumentException;
use PDO;

final class RedmineInstance
{
    public static function create(TestRunnerTracer $tracer, RedmineVersion $version): void
    {
        $instance = match ($version) {
            RedmineVersion::V5_1_1 => new self('5.1.1', '050101'),
            default => throw new InvalidArgumentException($version->name . ' is not supported.'),
        };

        $tracer->registerInstance($instance);
    }

    private $version;

    private $versionId;

    private $sqliteFile;

    private $sqliteBackup;

    private $redmineUrl;

    private $apiKey;

    private function __construct(string $version, string $versionId)
    {
        $this->version = $version;
        $this->versionId = $versionId;
        $this->sqliteFile = dirname(__FILE__, 3) . '/.docker/redmine-' . $versionId . '_data/sqlite/redmine.db';
        $this->sqliteBackup = dirname(__FILE__, 3) . '/.docker/redmine-' . $versionId . '_data/sqlite/redmine.db.bak';
        $this->redmineUrl = 'http://redmine-' . $versionId . ':3000';
        $this->apiKey = sha1((string) time());

        $this->createDatabaseBackup();
        $this->runDatabaseMigration();
    }

    public function getVersionId(): string
    {
        return $this->versionId;
    }

    public function getRedmineUrl(): string
    {
        return $this->redmineUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function shutdown(TestRunnerTracer $tracer): void
    {
        $this->restoreDatabaseFromBackup();

        $tracer->deregisterInstance($this);
    }

    private function createDatabaseBackup()
    {
        // Create backup of database
        copy($this->sqliteFile, $this->sqliteBackup);
    }

    private function runDatabaseMigration()
    {
        $now = new DateTimeImmutable();
        $pdo = new PDO('sqlite:' . $this->sqliteFile);

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
            ':value' => $this->apiKey,
            ':created_on' => $now->format('Y-m-d H:i:s.u'),
            ':updated_on' => $now->format('Y-m-d H:i:s.u'),
        ]);
    }

    private function restoreDatabaseFromBackup(): void
    {
        copy($this->sqliteBackup, $this->sqliteFile);
        unlink($this->sqliteBackup);
    }
}
