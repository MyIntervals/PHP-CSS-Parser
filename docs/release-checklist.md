# Steps to release a new version

1. In the [composer.json](../composer.json), update the `branch-alias` entry to
   point to the release _after_ the upcoming release.
1. In the [CHANGELOG.md](../CHANGELOG.md), create a new section with subheadings
   for changes _after_ the upcoming release, set the version number for the
   upcoming release, and remove any empty sections.
1. Update the target milestone in the Dependabot configuration.
1. Create a pull request "Prepare release of version x.y.z" with those changes.
1. Have the pull request reviewed and merged.
1. Tag the new release.
1. In the
   [Releases tab](https://github.com/MyIntervals/PHP-CSS-Parser/releases),
   create a new release and copy the change log entries to the new release.
1. Post about the new release on social media.
