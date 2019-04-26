# Hogan [![Build Status](https://travis-ci.org/DekodeInteraktiv/hogan-core.svg?branch=master)](https://travis-ci.org/DekodeInteraktiv/hogan-core)

Modular Flexible Content System for ACF Pro

## Installation
Install Hogan WordPress plugin using [Composer](https://getcomposer.org/) by requiring any of the modules listed below or just the core framework using:

```shell
$ composer require dekodeinteraktiv/hogan-core
```

Each module and the core framework itself will be installed as seperate WordPress plugins in the `wp-content/plugin` folder.


## Core Framework Modules

Module | Installation
--- | ---
[Text](https://github.com/DekodeInteraktiv/hogan-text) | `composer require dekodeinteraktiv/hogan-text`
[Forms](https://github.com/DekodeInteraktiv/hogan-form) | `composer require dekodeinteraktiv/hogan-form`
[Embed](https://github.com/DekodeInteraktiv/hogan-embed) | `composer require dekodeinteraktiv/hogan-embed`
[Gallery](https://github.com/DekodeInteraktiv/hogan-gallery) | `composer require dekodeinteraktiv/hogan-gallery`
[Grid](https://github.com/DekodeInteraktiv/hogan-grid) | `composer require dekodeinteraktiv/hogan-grid`
[Content Grid](https://github.com/DekodeInteraktiv/hogan-content-grid) | `composer require dekodeinteraktiv/hogan-content-grid`
[Link list](https://github.com/DekodeInteraktiv/hogan-linklist) | `composer require dekodeinteraktiv/hogan-linklist`
[Links](https://github.com/DekodeInteraktiv/hogan-links) | `composer require dekodeinteraktiv/hogan-links`
[Banner](https://github.com/DekodeInteraktiv/hogan-banner) | `composer require dekodeinteraktiv/hogan-banner`
[Image](https://github.com/DekodeInteraktiv/hogan-image) | `composer require dekodeinteraktiv/hogan-image`
[Expandable list](https://github.com/DekodeInteraktiv/hogan-expandable-list) | `composer require dekodeinteraktiv/hogan-expandable-list`
[Table](https://github.com/DekodeInteraktiv/hogan-table) | `composer require dekodeinteraktiv/hogan-table`
[Parallax Image](https://github.com/DekodeInteraktiv/hogan-parallax-image) | `composer require dekodeinteraktiv/hogan-parallax-image`
[Simple Posts](https://github.com/DekodeInteraktiv/hogan-simple-posts) | `composer require dekodeinteraktiv/hogan-simple-posts`
[Reusable Modules](https://github.com/DekodeInteraktiv/hogan-reusable-modules) | `composer require dekodeinteraktiv/hogan-reusable-modules`


## Adding modules
Adding custom modules can be done using the `register_module()` function in Core. Create a new module that extends the `\Dekode\Hogan\Module` class and add it to the Hogan repository like this:

```php

class DemoModule extends extends \Dekode\Hogan\Module {
  …
}

add_action( 'hogan/include_modules', function( \Dekode\Hogan\Core $core ) {
  require_once 'class-demomodule.php';
  $core->register_module( new DemoModule() );
}, 10, 1 );
```

## Usage
By default you will get a ACF Flexible Content group with all activated modules for post type `page` only. The built in wysiwyg editor will be removed.

### Adding Hogan to other post types.
Hogan is by default added to pages. Use the filter `hogan/field_group/default/supported_post_types` to declare support to other post types.

```php
add_filter( 'hogan/field_group/default/supported_post_types', function( array $post_types ) : array {
	$post_types[] = 'post'; // Add Hogan support for posts.
	return $post_types;
}, 10, 2 );
```

### Customizing the default field group
All field groups, including the default one, can be filtered using the `hogan/field_group/<name>/args` filter. The default args are:
```php
'name'                           => 'default',
'title'                          => __( 'Content Modules', 'hogan-core' ),
'modules'                        => [], // All modules.
'location'                       => [],
'hide_on_screen'                 => [],
'fields_before_flexible_content' => [],
'fields_after_flexible_content'  => [],
```

### Disable default field group
If you don't want to use the default field group, or for some other reason want to setup a customized field group yourself, field groups can be disabled with a filter.

```php
add_filter( 'hogan/field_group/default/enabled', '__return_false' );
```

### Adding custom field groups
Use the core function `register_field_group()` in action `hogan/include_field_groups` to register custom field groups.


```php
add_action( 'hogan/include_field_groups', function( \Dekode\Hogan\Core $core ) {
  $args = []; // Your field group args.
  $core->register_field_group( $args );
}, 10, 1 );
```

See Customizing the default field group above for possible arguments.

#### Example:

This example demonstrates how to add a custom field group with just the text module for post type `post`.
```php
add_action( 'hogan/include_field_groups', function( \Dekode\Hogan\Core $core ) {
  $core->register_field_group( [
    'name' => 'field_group_1',
    'title' => __( 'Field group title', 'text-domain' ),
    'modules' => [ 'text' ],
    'location' => [
      [
        [
		  'param' => 'post_type',
		  'operator' => '==',
          'value' => 'post',
        ],
      ],
    ],
  ] );
}, 10, 1);
```

## Adding header and lead to modules
You can turn on a heading and/or lead field for every single module. Default is no heading or lead. The heading and lead will be included before module specific fields. E.g. to enable heading and lead for Hogan Grid use:

```
add_filter( 'hogan/module/text/heading/enabled', '__return_true' );
add_filter( 'hogan/module/text/lead/enabled', '__return_true' );
```

## Style
Hogan core comes with a minimal stylesheet.

The width of hogan modules is by default set to 1360px. This can be changed
using the filter `hogan/frontend/content_width`:
```php
add_filter( 'hogan/frontend/content_width', function( int $content_width ) {
	return 1920;
}
```

If you don't want the stylesheet in your theme you can deregister it.
```php
wp_deregister_style( 'hogan-core' );
```

## Search
Modules content is by default indexed as _Content_ by [SearchWP](https://searchwp.com/). This can be disabled using:
```php
add_filter( 'hogan/searchwp/index_modules_as_post_content', '__return_false' );
```
## Running tests locally
Running tests locally can be beneficial during development as it is quicker than
committing changes and waiting for Travis CI to run the tests.

We’re going to assume that you have installed `git`, `svn`, `php`, `apache` and
`PHPUnit`

1. Initialize the testing environment locally: `cd` into the plugin directory
   and run the install script (you will need to have `wget` installed).

   ```bash
   bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

   The install script first it installs a copy of WordPress in the `/tmp` directory
   (by default) as well as the WordPress unit testing tools. Then it creates a
   database to be used while running tests. The parameters that are passed to
   `install-wp-tests.sh` setup the test database.

   * `wordpress_test` is the name of the test database (all data will be deleted!)
   * `root` is the MySQL user name
   * `''` is the MySQL user password
   * `localhost` is the MySQL server host
   * `latest` is the WordPress version; could also be 3.7, 3.6.2 etc.

2. Run the plugin tests:

   ```bash
   phpunit
   ```

For more info see https://make.wordpress.org/cli/handbook/plugin-unit-tests/#running-tests-locally

## Changelog
See [CHANGELOG.md](CHANGELOG.md).
