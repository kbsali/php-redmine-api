# Issue tracker: GitHub

Issues and pull requests live on `kbsali/php-redmine-api` (remote `origin`, `git@github.com:kbsali/php-redmine-api.git`).

## Refactoring operations

Used by `/continuous-refactoring-run`.

- **Search**: `gh issue list --state all --search "<words>" --json number,title,state,labels,updatedAt` searches title and text, open and closed together; `--state open` narrows it to the open ones. Raise `--limit` when the default of 30 cuts the list.
- **Done**: an issue closed as completed: `gh api repos/{owner}/{repo}/issues/<n> --jq .state_reason` gives `completed`. Mark one finished with `gh issue close <n> --reason completed`.
- **Merge requests**: pull requests on this GitHub repository, via `gh pr`.
- **Candidate**: the label `code quality`. List the open ones with `gh issue list --state open --label "code quality" --json number,title,labels`.
- **Linked merge request**: the pull request's description carries `Closes #<issue>`. From the issue: `gh issue view <n> --json closedByPullRequestsReferences`.
- **Claim**: `gh issue edit <n> --add-assignee @me`.
- **Rejected**: an issue closed as not planned, the reason in its closing comment: `gh issue close <n> --reason "not planned" --comment "<reason>"`. Found again with **Search**, its query extended by `reason:"not planned"`.
- **Blocked by**: a line `Blocked by: #<n>, #<n>` in the issue's description. The issue is unblocked when every issue on that line is done.
- **Housekeeping**: the template is `docs/refactoring/housekeeping-template.md`. The next ticket is made with `gh issue create`: a title that names Housekeeping and the date the ticket is due from ("Housekeeping – due 2026-11-14"), a line in the text `Due: <date>`, a line naming the template `docs/refactoring/housekeeping-template.md`, and the template's tasks as a checklist. The open ones are listed with `gh issue list --state open --search "Housekeeping in:title" --json number,title`.
