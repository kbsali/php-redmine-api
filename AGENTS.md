# Agent Guidelines

## Project: php-redmine-api

### Docker setup

All PHP commands run inside Docker containers. Check `docker-compose.yml` for the service names. On `v2.x` (default) the `php` and `behat` services both run PHP 8.2.

The `behat` container intentionally runs on PHP 8.2 (the lowest supported version). In CI, Behat, PHPStan and code style run on PHP 8.2 as well.

| Command | Container | Invocation |
|---------|-----------|------------|
| PHPUnit | `php` | `docker compose run --rm php composer run phpunit` |
| Code style | `php` | `docker compose run --rm php composer run codestyle -- --dry-run --diff` |
| PHPStan | `php` | `docker compose run --rm php composer run phpstan` |
| Behat | `behat` | `docker compose run --rm behat composer run bdt` |

Start the containers: `docker compose up -d`

Notes:
- **After switching branches, rebuild the images:** `docker compose build && docker compose up -d --remove-orphans`. Compose does not rebuild an existing image on its own. Verify with `docker compose run --rm php php -v`.
- **composer.lock and vendor/ are not versioned** (`/composer.lock` is in `.gitignore`); CI always resolves the latest allowed dependency versions. New releases of dev tools (e.g. PHPStan) can therefore turn CI red without any code change – reproduce locally with `composer update`.
- **vendor/ must match the PHP version of the container.** A lock file resolved on PHP ≥ 8.4 (PHPUnit 13) fails on PHP 8.2. For PHP 8.2, resolve the dependencies inside an 8.2 container as the host user, otherwise composer cannot write the files:
  `docker compose run --rm --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer behat composer update --with-all-dependencies`
  This installs PHPUnit 11.5. If composer reports permission errors in `vendor/`, fix the ownership first: `docker compose run --rm --user 0 behat chown -R 1000:33 vendor composer.lock`
- **Behat** needs running Redmine containers. After `docker compose up -d`, wait until all containers are healthy (about 30–60s) before running Behat.
- The **pipeline** has 27 checks (PHPUnit × 5 PHP versions, Behat × 4 suites, code quality × 3, GitGuardian). Every push triggers a full pipeline run.

### Issue tracker

The issue tracker is GitHub (`kbsali/php-redmine-api`). How agents search, close and link issues and pull requests is described in `docs/agents/issue-tracker.md`.

### Refactoring ideas

When you notice a possible refactoring while doing other work, ask the human whether to note it. Once they agree, post it as a comment on the open Housekeeping ticket (`docs/agents/issue-tracker.md`, **Housekeeping**): where it is, what would change, and why. When two Housekeeping tickets are open, ask the human which one, and recommend the younger.

### Conventions

- **Strict BC promise** – no breaking changes; new exceptions only behind the per-client future mode gate (`$this->isFutureModeEnabled()` in `AbstractApi`, enabled via `$client->enableFutureMode()`; tests use `Redmine\Tests\Fixtures\FutureMode::enable($api)`)
- **QA before committing** – PHPUnit + code style + PHPStan + Behat must pass, on PHP 8.2 (the lowest supported version). Mention the PHP versions used in the PR's QA section.
- **No co-author/attribution lines** – commit messages and PR descriptions contain no `Co-Authored-By:` trailers, no session links and no "Generated with" notes.
- **English on GitHub** – issue comments, PR titles, PR descriptions and review comments on GitHub are always written in English.
