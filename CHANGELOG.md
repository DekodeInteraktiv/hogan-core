# Changelog

## 1.1.4
- Fixed a bug where enqueue module assets was runned multiple times [#70](https://github.com/DekodeInteraktiv/hogan-core/pull/70)
- Add `include_file` helper function for modules [#71](https://github.com/DekodeInteraktiv/hogan-core/pull/71)

## 1.1.3
- Added `hogan_get_link_title` helper function. [#68](https://github.com/DekodeInteraktiv/hogan-core/pull/68)

## 1.1.2
- Added `hogan_url_to_postid` helper function. A cached version of `url_to_postid`. [#63](https://github.com/DekodeInteraktiv/hogan-core/pull/63)
- Fix a bug where flexible content was rendered multiple times on the same page. [#66](https://github.com/DekodeInteraktiv/hogan-core/pull/66)

## 1.1.1
- Check if server runs required php version
- Added filters to hogan toolbars. `hogan/tinymce/toolbar/{hogan|hogan_caption}`

## 1.1.0
### Breaking changes
- Hogan is no longer added by default to the post types `post`. Use the filter `hogan/supported_post_types` to declare Hogan support to different post types.

### Changed
- Heading and lead is now built into Hogan Core and can to be included in every module using filters, see section [Adding header and lead to modules](#adding-header-and-lead-to-modules) for example.

### Added
- `hogan_module` - Helper function to render template with static content.
- `hogan_enqueue_module_assets` - Helper function to enqueue module assets.

## 1.0.17
- Added custom blockquote btn to hogan toolbar. Only appears if TinyMCEPlugin [Blockquote with cite](https://github.com/DekodeInteraktiv/WP-Snippets) is added to MU-plugins in the project you are working on.

## 1.0.13
- Deprecated `hogan/module/{ $module }/outer_wrapper_tag` filter. Use `hogan/module/outer_wrapper_tag` instead.
- Deprecated `hogan/module/{ $module }/inner_wrapper_tag` filter. Use `hogan/module/inner_wrapper_tag` instead.
- Deprecated `hogan/module/{ $module }/outer_wrapper_classes` filter. Use `hogan/module/outer_wrapper_classes` instead.
- Deprecated `hogan/module/{ $module }/inner_wrapper_classes` filter. Use `hogan/module/inner_wrapper_classes` instead.

## 1.0.8
Added module title to collapsed layout name.

## 1.0.16
- Bugfix get_current_post_layouts(). This would previously return modules for first post only in a loop. Array _current_layouts is now two dimensional with post id as key.
