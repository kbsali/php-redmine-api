<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use DateTimeImmutable;
use InvalidArgumentException;
use PDO;

final class RedmineInstance
{
    /**
     * Make sure that supported versions have a service in /docker-composer.yml
     */
    public static function getSupportedVersions(): array
    {
        return [
            RedmineVersion::V5_1_1,
            RedmineVersion::V5_0_7,
        ];
    }

    /**
     * @param TestRunnerTracer $tracer Required to ensure that RedmineInstance is created while Test Runner is running
     */
    public static function create(TestRunnerTracer $tracer, RedmineVersion $version): void
    {
        if (! in_array($version, static::getSupportedVersions())) {
            throw new InvalidArgumentException('Redmine ' . $version->asString() . ' is not supported.');
        }

        $tracer->registerInstance(new self($tracer, $version));
    }

    private TestRunnerTracer $tracer;

    private RedmineVersion $version;

    private string $sqliteFile;

    private string $sqliteBackup;

    private string $redmineUrl;

    private string $apiKey;

    private function __construct(TestRunnerTracer $tracer, RedmineVersion $version)
    {
        $this->tracer = $tracer;
        $this->version = $version;

        $versionId = strval($version->asId());

        $this->sqliteFile = dirname(__FILE__, 3) . '/.docker/redmine-' . $versionId . '_data/sqlite/redmine.db';
        $this->sqliteBackup = dirname(__FILE__, 3) . '/.docker/redmine-' . $versionId . '_data/sqlite/redmine.db.bak';
        $this->redmineUrl = 'http://redmine-' . $versionId . ':3000';
        $this->apiKey = sha1($versionId . (string) time());

        $this->createDatabaseBackup();
        $this->runDatabaseMigration();
    }

    public function getVersionId(): int
    {
        return $this->version->asId();
    }

    public function getRedmineUrl(): string
    {
        return $this->redmineUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function reset(TestRunnerTracer $tracer): void
    {
        if ($tracer !== $this->tracer) {
            throw new InvalidArgumentException();
        }

        $this->restoreDatabaseFromBackup();
        $this->runDatabaseMigration();
    }

    public function shutdown(TestRunnerTracer $tracer): void
    {
        if ($tracer !== $this->tracer) {
            throw new InvalidArgumentException();
        }

        $this->restoreDatabaseFromBackup();
        $this->removeDatabaseBackup();

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
    }

    private function removeDatabaseBackup(): void
    {
        unlink($this->sqliteBackup);
    }
}
