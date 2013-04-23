xi-bundle-breadcrumbs
=====================

A Breadcrumbs bundle for Symfony2.

This is different from other breadcrumb bundles, because it utilizes routes  
as a tree to build the breadcrumbs in order to not pollute the controller  
actions with repetitive breadcrumbs code.

[![Build Status](https://secure.travis-ci.org/xi-project/xi-bundle-breadcrumbs.png?branch=service)](http://travis-ci.org/xi-project/xi-bundle-breadcrumbs)


## Design goals

* Implement breadcrumbs with configurable and internationalised labels and urls
* Keep it DRY: Do not repeat breadcrumbs code for the same page in several controller actions
* Avoid using annotations either
* Allow cyclical loops on breadcrumb hierarchy and handle it intelligently


## Installing

### Add bundle to `composer.json`

    "require": {
        # ..
        "xi/breadcrumbs-bundle": ">=2.1"
        # ..
    }

### Add bundle to `AppKernel.php`

```php
public function registerBundles()
{
    $bundles = array(
        ...

        new Xi\Bundle\BreadcrumbsBundle\XiBreadcrumbsBundle(),
    );

    ...
}
```


## Usage


### Basic usage

Add `{{ xi_breadcrumbs() }}` into your template and add `parent` and `label` into your route defaults.  
Label is optional, and defaults to the route name.

```yaml
root:
    pattern:   /
    defaults:
        label: "home"

foo:
    pattern:   /foo
    defaults:
        parent: "root"

bar:
    pattern:   /foo/bar/{slug}
    defaults:
        label: "bar {slug}"
        parent: "foo"
```

`Parent` is the name of the parent route. `Label` can have placeholder values between braces, as shown  
below with `{slug}`. Note that child routes should have all the placeholders available that their parents  
will use – otherwise the placeholders will be stripped from the label.


### Internationalised routes

For internationalised routes using [BesimpleI18nRoutingBundle](https://github.com/BeSimple/BeSimpleI18nRoutingBundle), use a similar array of locales as in the `locales` option.

```yaml
xi_service:
    locales:
        en: /service
        fi: /palvelu
    defaults:
        label:
            en: "Services"
            fi: "Palvelut"
```

### More examples

For more usage examples, see the Yaml files at `Tests/Fixtures` directory.
