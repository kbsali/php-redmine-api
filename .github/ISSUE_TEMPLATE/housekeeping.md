---
name: Housekeeping
about: Recurring maintenance for this project (maintainers and agents only)
title: "Housekeeping – due YYYY-MM-DD"
---

Due: YYYY-MM-DD

Rhythm: weekly

Recurring maintenance for this project. One Housekeeping ticket is always open, made from this template.
Ideas for a refactoring are left as comments on the ticket. A new task goes above the last one.

- [ ] Scan the Git history for committed secrets: the whole history the first time, afterwards the commits added since the previous Housekeeping ticket's scan. Note the commit scanned up to.
- [ ] Re-check the project's quality tooling: is every tool set up so far still in place and running, and is there a further one to set up? File a ticket for each gap.
- [ ] Audit the third-party dependencies for known vulnerabilities with `composer audit` and update each one with an advisory to a patched version.
- [ ] Run `composer update` and record the output.
- [ ] After a dependency update, re-run PHPStan and check for newly surfaced deprecation warnings (a dependency bump can start calling an API the current ruleset did not flag before). Fix them.
- [ ] Look for a newer PER-CS version than the one pinned in `.php-cs-fixer.dist.php` (currently `@PER-CS3x0`; `vendor/bin/php-cs-fixer list-sets` after `composer update`). Where there is one, switch to it and set each rule that now reports findings to `false` with a `Not yet:` comment and its count; only the config changes, never the code.
- [ ] Work through the comments on this ticket that propose a refactoring: carry out each idea that is small and safe, file a ticket for each bigger one, and leave a comment starting with the word `Ideas` that says what became of each idea.
- [ ] Create the next Housekeeping ticket from this template (`gh issue create --template Housekeeping`), due one rhythm from today.
