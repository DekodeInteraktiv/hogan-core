# Hogan [ ![Codeship Status for DekodeInteraktiv/hogan-core](https://app.codeship.com/projects/58f4d340-97ba-0135-2412-665d154dd139/status?branch=master)](https://app.codeship.com/projects/251897)

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
[Link list](https://github.com/DekodeInteraktiv/hogan-linklist) | `composer require dekodeinteraktiv/hogan-linklist`
[Links](https://github.com/DekodeInteraktiv/hogan-links) | `composer require dekodeinteraktiv/hogan-links`
[Banner](https://github.com/DekodeInteraktiv/hogan-banner) | `composer require dekodeinteraktiv/hogan-banner`
[Image](https://github.com/DekodeInteraktiv/hogan-image) | `composer require dekodeinteraktiv/hogan-image`
[Expandable list](https://github.com/DekodeInteraktiv/hogan-expandable-list) | `composer require dekodeinteraktiv/hogan-expandable-list`
[Table](https://github.com/DekodeInteraktiv/hogan-table) | `composer require dekodeinteraktiv/hogan-table`
[Simple Posts](https://github.com/DekodeInteraktiv/hogan-simple-posts) | `composer require dekodeinteraktiv/hogan-simple-posts`
[People](https://github.com/DekodeInteraktiv/hogan-people) | `composer require dekodeinteraktiv/hogan-people`


## Adding modules
Adding custom modules can be done using the `hogan_register_module()` function. Create a new module that extends the `\Dekode\Hogan\Module` class and add it to the Hogan repository like this:

```php

class TextModule2 extends extends \Dekode\Hogan\Module {
	…
}

add_action( 'hogan/include_modules', function() {
	hogan_register_module( new TextModule2() );
} );
```

See the [Text Module](https://github.com/DekodeInteraktiv/hogan-text) for a complete example.

See the [Wiki](https://github.com/DekodeInteraktiv/hogan-core/wiki/Guidelines-for-creating-new-modules) for more information. 

## Usage
By default you will get a ACF Flexible Content group with all activated modules for post types `post` and `page`. The built in wysiwyg editor will be removed.

### Customizing the default field group
The default field group can be customized using these filters:
- `hogan/field_group/default/fields_before_flexible_content` Insert custom fields before modules.
- `hogan/field_group/default/fields_after_flexible_content` Insert custom fields after modules.
- `hogan/field_group/default/location` Override the location parameter.
- `hogan/field_group/default/hide_on_screen` Override the hide_on_screen parameter.

### Remove default field group
If you dont want to use the default field group, or for some other reason want to setup a customized field group yourself, run this helper function in the theme setup to disable the default group.

```php
add_action( 'hogan/include_field_groups', function() {
	hogan_deregister_default_field_group();
} );
```

### Adding custom field groups
Use the helper function `hogan_register_field_group()` in action `hogan/include_field_groups` to register custom field groups.

```php
function hogan_register_field_group( $name, $label, $modules = [], $location = [], $hide_on_screen = [], $fields_before_flexible_content = [], $fields_after_flexible_content = [] ) {
```

#### Function arguments:
- `$name`: Unique field group name
- `$label`: Field group label
- `$modules`: Array of modules you want to make available or null for all.
- `$location`: The location parameter for [acf_add_local_field_group()](https://www.advancedcustomfields.com/resources/register-fields-via-php/)
- `$hide_on_screen`: The hide_on_screen parameter for [acf_add_local_field_group()](https://www.advancedcustomfields.com/resources/register-fields-via-php/)
- `$fields_before_flexible_content`: Custom ACF fields before the Flexible Content field.
- `$fields_after_flexible_content`: Custom ACF fields after the Flexible Content field.

#### Example:

This example demonstrates how to add a custom field group with just the text module for post type `page`.
```php
add_action( 'hogan/include_field_groups', function() {

  $name = 'field_group_1';
  $label = __( 'Field group Label', 'text-domain' );
  $modules = [ 'text' ];
  $location = [
    [
      [
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'page',
      ],
    ],
  ];

  hogan_register_field_group( $name, $label, $modules, $location );
});
```

## Search
Modules content is by default indexed as _Content_ by [SearchWP](https://searchwp.com/). This can be disabled using:
```php
add_filter( 'hogan/searchwp/index_modules_as_post_content', '__return_false' );
```


## Author
…
