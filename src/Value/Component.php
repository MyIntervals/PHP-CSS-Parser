<?php

namespace Sabberworm\CSS\Value;

/**
 * To be used by any class that represents a component of a CSS property value.
 * Values often comprise 'component operator component' (recursively).
 * This interface has no methods to implement;
 * its purpose is abstract: to allow a unique type to be specified when a `Component` is an argument of a method.
 */
interface Component {}
