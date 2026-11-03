<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Redmine\Tests\Behat\Bootstrap\FeatureContext;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('default'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '7.0.2',
                    'rootPath' => '%paths.base%/../../.docker/redmine-dev_data',
                    'redmineUrl' => 'http://redmine-dev:3000',
                ],
            )
            ->withPaths('%paths.base%/features'))
        ->withSuite((new Suite('redmine_7_0'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '7.0.2',
                    'rootPath' => '%paths.base%/../../.docker/redmine-70002_data',
                    'redmineUrl' => 'http://redmine-7-0:3000',
                ],
            ))
        ->withSuite((new Suite('redmine_6_1'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '6.1.5',
                    'rootPath' => '%paths.base%/../../.docker/redmine-60105_data',
                    'redmineUrl' => 'http://redmine-6-1:3000',
                ],
            ))
        ->withSuite((new Suite('redmine_6_0'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '6.0.11',
                    'rootPath' => '%paths.base%/../../.docker/redmine-60011_data',
                    'redmineUrl' => 'http://redmine-6-0:3000',
                ],
            ))
        ->withSuite((new Suite('redmine_5_1'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '5.1.12',
                    'rootPath' => '%paths.base%/../../.docker/redmine-50112_data',
                    'redmineUrl' => 'http://redmine-5-1:3000',
                ],
            )))
    ->withProfile((new Profile('github-actions'))
        ->withSuite((new Suite('redmine_7_0'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '7.0.2',
                    'rootPath' => '%paths.base%/../../.docker/redmine-70002_data',
                    'redmineUrl' => 'http://localhost:5070',
                ],
            ))
        ->withSuite((new Suite('redmine_6_1'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '6.1.5',
                    'rootPath' => '%paths.base%/../../.docker/redmine-60105_data',
                    'redmineUrl' => 'http://localhost:5061',
                ],
            ))
        ->withSuite((new Suite('redmine_6_0'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '6.0.11',
                    'rootPath' => '%paths.base%/../../.docker/redmine-60011_data',
                    'redmineUrl' => 'http://localhost:5060',
                ],
            ))
        ->withSuite((new Suite('redmine_5_1'))
            ->addContext(
                FeatureContext::class,
                [
                    'redmineVersion' => '5.1.12',
                    'rootPath' => '%paths.base%/../../.docker/redmine-50112_data',
                    'redmineUrl' => 'http://localhost:5051',
                ],
            )));
