<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# Customize Partial Refresh

Refresh parts of a Customizer preview instead of reloading the entire page when a setting changed without transport=postMessage.

**Contributors:** [xwp](http://profiles.wordpress.org/xwp), [westonruter](http://profiles.wordpress.org/westonruter)  
**Tags:** [customizer](http://wordpress.org/plugins/tags/customizer), [customize](http://wordpress.org/plugins/tags/customize), [preview](http://wordpress.org/plugins/tags/preview)  
**Requires at least:** 4.1  
**Tested up to:** 4.1  
**Stable tag:** trunk (master)  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Build Status](https://travis-ci.org/xwp/wp-customize-partial-refresh.png?branch=master)](https://travis-ci.org/xwp/wp-customize-partial-refresh) 

## Description ##

The WordPress Customizer is a framework for previewing any change to a site.
By default, settings exposed in the Customizer use a `refresh` transport,
meaning that in order for a setting change to be applied, the entire preview
has to reload. The result is a preview that is anything but live. To remedy this
poor user experience of a laggy preview, the Customizer allows settings to opt-in
to using a `postMessage` transport. This relies on JavaScript to apply the changes
live without doing any server-side communication at all: changes are applied
instantly.

There is a major issue with settings using the `postMessage` transport,
however: any logic used in PHP to render the setting in the template has to be
duplicated in JavaScript. Thus the transport is not DRY, and so it is really only
suitable for simple text or style changes that involve almost no logic, and this
is commonplace in themes.

What the Customizer needs is a middle-ground between refreshing the entire preview
to apply changes with PHP and applying the changes exclusively with JavaScript.
The partial preview refresh is one solution, and is proposed in [#27355](https://core.trac.wordpress.org/ticket/27355).

Partial preview refreshing was first explored when Customizer widget management
was being developed for the 3.9 release. It was [added](https://github.com/xwp/wp-widget-customizer/pull/37)
to the Widget Customizer plugin, and described in a [Make Core post](https://make.wordpress.org/core/2014/01/28/widget-customizer-feature-as-plugin-merge-proposal/#section-live-previews).
But [we decided](https://core.trac.wordpress.org/ticket/27112#comment:10) to
[remove](https://github.com/xwp/wp-widget-customizer/compare/without-partial-previews)
the feature in favor of having a better generalized framework for partial refreshes
in a future release.

This plugin first focuses on resurrecting partial preview refreshes for widgets.
We can then explore adding partial refresh support to other object types, such
as menus, inline styles, or arbitrary regions registered.

### Usage ###
To opt-in to partial preview refreshes for a theme, add:

```
add_theme_support( 'customize-partial-refresh-widgets' );
```

When this is done, widgets in Core will automatically use partial refreshes.
To opt-in to partial refreshes for other widget types, use these filters:

```
add_filter( 'customize_widget_partial_refreshable', '__return_true' ); // opt-in everything
add_filter( "customize_widget_partial_refreshable_{$id_base}", '__return_true' ); // opt-in selectively
```


## Changelog ##

### 0.1 ###
* Resurrect Widget Customizer partial refreshes.

