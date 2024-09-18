# API and Deprecation Policy

## API Policy

The code in this library is intended to be called by other projects. It is not
intended to be extended. If you want to extend any classes, you're on your own,
and your code might break with any new release of this library.

Any classes, methods and properties that are `public` and not marked as
`@internal` are considered to be part of the API. Those methods will continue
working in a compatible way over minor and bug-fix releases according
to [Semantic Versioning](https://semver.org/), though we might change the native
type declarations in a way that could break subclasses.

Any classes, methods and properties that are `protected` or `private` are _not_
considered part of the API. Please do not rely on them. If you do, you're on
your own.

Any code that is marked as `@internal` is subject to change or removal without
notice. Please do not call it. There be dragons.

If a class is marked as `@internal`, all properties and methods of this class
are by definition considered to be internal as well.

When we change some code from public to `@internal` in a release, the first
release that might change that code in a breaking way will be the next major
release after that. This will allow you to change your code accordingly. We'll
also add since which version the code is internal.

For example, we might mark some code as `@internal` in version 8.7.0. The first
version that possibly changes this code in a breaking way will then be version
9.0.0.

Before you upgrade your code to the next major version of this library, please
update to the latest release of the previous major version and make sure that
your code does not reference any code that is marked as `@internal`.

## Deprecation Policy

Code that we plan to remove is marked as `@deprecated`. In the corresponding
annotation, we also note in which release the code will be removed.

When we mark some code as `@deprecated` in a release, we'll usually remove it in
the next major release. We'll also add since which version the code is
deprecated.

For example, when we mark some code as `@deprecated` in version 8.7.0, we'll
remove it in version 9.0.0 (or sometimes a later major release).

Before you upgrade your code to the next major version of this library, please
update to the latest release of the previous major version and make sure that
your code does not reference any code that is marked as `@deprecated`.
