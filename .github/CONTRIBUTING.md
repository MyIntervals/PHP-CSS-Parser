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

1. [Fork the git repository](https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project).
2. Clone your forked repository locally and install the development dependencies.
3. Add a local remote "upstream" so you will be able to
   [synchronize your fork with the original repository](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/syncing-a-fork).
4. Create a local branch for your changes.
5. Add unit tests for your changes.
   These tests should fail without your changes.
6. Add your changes. Your added unit tests now should pass, and no other tests
   should be broken. Check that your changes follow the same coding style as the
   rest of the project.
7. Add a changelog entry.
8. Commit and push your changes.
9. [Create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests)
   for your changes. Check that the CI build is green. (If it is not, fix the
   problems listed.)
10. Wait for a review by the maintainers.
11. Polish your changes as needed until they are ready to be merged.
