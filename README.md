# Hogan [ ![Codeship Status for DekodeInteraktiv/hogan-core](https://app.codeship.com/projects/58f4d340-97ba-0135-2412-665d154dd139/status?branch=master)](https://app.codeship.com/projects/251897)

Modular Flexible Content System for ACF Pro

## Installation
Install Hogan WordPress plugin using [Composer](https://getcomposer.org/) by requiring any of the modules listed below or just the core framework using:

```
composer require dekodeinteraktiv/hogan-core:@dev
```

Each module and the core framework itself will be installed as seperate WordPress plugins in the `wp-content/plugin` folder.


## Core Framework Modules

Module | Installation | composer.json
--- | --- | ---
[Text](https://github.com/DekodeInteraktiv/hogan-text) | `composer require dekodeinteraktiv/hogan-text:@dev` | `"dekodeinteraktiv/hogan-text": "@dev"`
[Gravity Forms](https://github.com/DekodeInteraktiv/hogan-form) | `composer require dekodeinteraktiv/hogan-form:@dev` | `"dekodeinteraktiv/hogan-form": "@dev"`

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

## Usage
By default you will get a ACF Flexible Content group with all activated modules for post types `post` and `page`. The default wysiwyg editor will be replaced.

### Remove default field group
If you dont want to use the default field group, or for some other reason want to customize the field group yourself, run this helper function in the theme setup.

```php
add_action( 'hogan/include_field_groups', function() {
	hogan_deregister_default_field_group();
} );
```

### Adding custom field groups
Use the helper function `hogan_register_field_group()` in action `hogan/include_field_groups` to register a custom field groups.

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

…

## Author
…
