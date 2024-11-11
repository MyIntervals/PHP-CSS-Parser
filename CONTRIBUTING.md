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
1. Clone your forked repository locally and install the development
   dependencies.
1. Create a local branch for your changes.
1. Add unit tests for your changes.
   These tests should fail without your changes.
1. Add your changes. Your added unit tests now should pass, and no other tests
   should be broken. Check that your changes follow the same coding style as the
   rest of the project.
1. Add a changelog entry, newest on top.
1. Commit and push your changes.
1. [Create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests)
   for your changes.
1. Check that the CI build is green. (If it is not, fix the problems listed.)
   Please note that for first-time contributors, you will need to wait for a
   maintainer to allow your CI build to run.
1. Wait for a review by the maintainers.
1. Polish your changes as needed until they are ready to be merged.

## About code reviews

After you have submitted a pull request, the maintainers will review your
changes. This will probably result in quite a few comments on ways to improve
your pull request. This project receives contributions from developers around
the world, so we need the code to be the most consistent, readable, and
maintainable that it can be.

Please do not feel frustrated by this - instead please view this both as our
contribution to your pull request as well as a way to learn more about
improving code quality.

If you would like to know whether an idea would fit in the general strategy of
this project or would like to get feedback on the best architecture for your
ideas, we propose you open a ticket first and discuss your ideas there
first before investing a lot of time in writing code.

## Install the development dependencies

To install the most important development dependencies, please run the following
command:

```bash
composer install
```

We also have some optional development dependencies that require higher PHP
versions than the lowest PHP version this project supports. Hence they are not
installed by default.

To install these, you will need to have [PHIVE](https://phar.io/) installed.
You can then run the following command:

```bash
phive install
```

## Unit-test your changes

Please cover all changes with unit tests and make sure that your code does not
break any existing tests. We will only merge pull requests that include full
code coverage of the fixed bugs and the new features.

To run the existing PHPUnit tests, run this command:

```bash
composer ci:tests:unit
```

## Coding Style

Please use the same coding style
([PER 2.0](https://www.php-fig.org/per/coding-style/)) as the rest of the code.
Indentation is four spaces.

We will only merge pull requests that follow the project's coding style.

Please check your code with the provided static code analysis tools:

```bash
composer ci:static
```

Please make your code clean, well-readable and easy to understand.

If you add new methods or fields, please add proper PHPDoc for the new
methods/fields. Please use grammatically correct, complete sentences in the
code documentation.

You can autoformat your code using the following command:

```bash
composer fix
```
