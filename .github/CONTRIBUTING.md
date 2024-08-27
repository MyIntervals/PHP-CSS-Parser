# Contributing to PHP-CSS-Parser

Those that wish to contribute bug fixes, new features, refactorings and
clean-up to PHP-CSS-Parser are more than welcome.

When you contribute, please take the following things into account:

## Contributor Code of Conduct

Please note that this project is released with a
[Contributor Code of Conduct](../CODE_OF_CONDUCT.md). By participating in this
project, you agree to abide by its terms.

## General workflow

This is the workflow for contributing changes to this project::

1. [Fork the Git repository](https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project).
2. Clone your forked repository locally and install the development
   dependencies.
3. Create a local branch for your changes.
4. Add unit tests for your changes.
   These tests should fail without your changes.
5. Add your changes. Your added unit tests now should pass, and no other tests
   should be broken. Check that your changes follow the same coding style as the
   rest of the project.
6. Add a changelog entry, newest on top.
7. Commit and push your changes.
8. [Create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests)
   for your changes.
9. Check that the CI build is green. (If it is not, fix the problems listed.)
   Please note that for first-time contributors, you will need to wait for a
   maintainer to allow your CI build to run.
10. Wait for a review by the maintainers.
11. Polish your changes as needed until they are ready to be merged.
