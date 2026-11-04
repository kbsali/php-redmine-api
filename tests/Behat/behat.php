<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Redmine\Tests\Behat\Bootstrap\FeatureContext;

/**
 * Redmine versions tested by the Behat suites.
 *
 * Make sure that every entry has a service in /docker-compose.yml
 * and a matching port in the github-actions matrix of /.github/workflows/tests.yml.
 *
 * @var array<string, array{version: string, dataDir: string, service: string, port: int}>
 */
$redmineVersions = [
    'redmine_7_0' => ['version' => '7.0.3', 'dataDir' => 'redmine-70003_data', 'service' => 'redmine-7-0', 'port' => 5070],
    'redmine_6_1' => ['version' => '6.1.6', 'dataDir' => 'redmine-60106_data', 'service' => 'redmine-6-1', 'port' => 5061],
    'redmine_6_0' => ['version' => '6.0.12', 'dataDir' => 'redmine-60012_data', 'service' => 'redmine-6-0', 'port' => 5060],
    'redmine_5_1' => ['version' => '5.1.12', 'dataDir' => 'redmine-50112_data', 'service' => 'redmine-5-1', 'port' => 5051],
];

$createSuite = static fn(string $name, string $version, string $dataDir, string $url): Suite => (new Suite($name))
    ->addContext(
        FeatureContext::class,
        [
            'redmineVersion' => $version,
            'rootPath' => '%paths.base%/../../.docker/' . $dataDir,
            'redmineUrl' => $url,
        ],
    );

// Profile for local development: the Redmine instances are reachable through the Docker network
$default = (new Profile('default'))
    ->withSuite(
        $createSuite('default', '7.0.3', 'redmine-dev_data', 'http://redmine-dev:3000')
            ->withPaths('%paths.base%/features'),
    );

// Profile for GitHub Actions: the Redmine instances are reachable through localhost ports
$githubActions = new Profile('github-actions');

foreach ($redmineVersions as $name => $redmine) {
    $default = $default->withSuite(
        $createSuite($name, $redmine['version'], $redmine['dataDir'], 'http://' . $redmine['service'] . ':3000'),
    );

    $githubActions = $githubActions->withSuite(
        $createSuite($name, $redmine['version'], $redmine['dataDir'], 'http://localhost:' . $redmine['port']),
    );
}

return (new Config())
    ->withProfile($default)
    ->withProfile($githubActions);
