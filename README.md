xi-bundle-breadcrumbs
=====================

A Breadcrumbs bundle for Symfony2.

This is different from other breadcrumb bundles, because it utilizes routes  
as a tree to build the breadcrumbs in order to not pollute the controller  
actions with repetitive breadcrumbs code.


## Installing

### deps -file
```
[XiAjaxBundle]
    git=http://github.com/xi-project/xi-bundle-breadcrumbs.git
    target=/bundles/Xi/Bundle/BreadcrumbsBundle
```

### autoload.php file
```php
<?php
    'Xi\\Bundle'       => __DIR__.'/../vendor/bundles',
?>
```

### appKernel.php -file
```php
<?php
    new Xi\Bundle\BreadcrumbsBundle\XiBreadcrumbsBundle(),
?>
```
