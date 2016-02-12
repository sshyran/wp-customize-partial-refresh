<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# Customize Partial Refresh

Refresh parts of the Customizer preview instead of reloading the entire page.

**Contributors:** [xwp](https://profiles.wordpress.org/xwp), [westonruter](https://profiles.wordpress.org/westonruter), [valendesigns](https://profiles.wordpress.org/valendesigns)  
**Tags:** [customizer](https://wordpress.org/plugins/tags/customizer), [customize](https://wordpress.org/plugins/tags/customize), [preview](https://wordpress.org/plugins/tags/preview)  
**Requires at least:** 4.5-alpha-35776  
**Stable tag:** trunk (master)  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Build Status](https://travis-ci.org/xwp/wp-customize-partial-refresh.svg?branch=master)](https://travis-ci.org/xwp/wp-customize-partial-refresh) 

## Description ##

**This is a feature plugin proposed for WordPress 4.5**

The WordPress Customizer is a framework for previewing any change to a site.
By default, settings exposed in the Customizer use a `refresh` transport,
meaning that in order for a setting change to be applied, the entire preview
has to reload, which has a huge performance hit. The result is a preview that
is anything but live. To remedy this poor user experience of a laggy
preview, the Customizer allows settings to opt-in to using a `postMessage`
transport. This relies on JavaScript to apply the changes live without doing
any server-side communication at all: changes are applied instantly.

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
in a future release. Partial preview refreshing has been added in 4.3 for changes
to menus which can now be managed in the Customizer.

This plugin introduces a “selective refresh” framework whereby template *partials*
are registered. Partials are associated with settings, and when the setting changes
the partial is refreshed via Ajax. A partial has an associated <code>render_callback</code>
which is responsible for generating the content for the partial.

The nav menus partial refreshing introduced to Core in 4.3 is re-implemented in this
plugin to make use of the selective refresh framework. Selective refresh of widgets
is also implemented using the same framework.

Other examples of selective refresh:

* [Site Title Smilies](https://gist.github.com/westonruter/a15b99bdd07e6f4aae7a)
* [Welcome Message](https://gist.github.com/westonruter/ed4ae3f8b6f6d653e0c6)

Trivial example of Site Title Smilies showing `wptexturize` also applying in the preview:

[![Play video on YouTube](https://i1.ytimg.com/vi/ikW8dfaOPng/hqdefault.jpg)](https://www.youtube.com/watch?v=ikW8dfaOPng)

<em>Blue sky:</em> If we can eliminate full-page refreshes, we can start to
introduce controls inline with the preview (e.g. widget controls appearing
with their widgets), and even eliminate the Customizer <code>iframe</code>
altogether since the Customizer pane could then slide-in on any existing
frontend page the user is on without having enter into a completely different
Customizer state; when done, they can just collapse the Customizer away and
continue on their way browsing the site.

**Development of this plugin is done [on GitHub](https://github.com/xwp/wp-customize-partial-refresh). Pull requests welcome. Please see [issues](https://github.com/xwp/wp-customize-partial-refresh/issues) reported there before going to the [plugin forum](https://wordpress.org/support/plugin/customize-partial-refresh).**

## Changelog ##

### 0.5.3 ###
* Capture errors triggered during partial rendering, preventing them from displaying inline which can cause the containers to be corrupted and break subsequent updates.  If `WP_DEBUG_DISPLAY` is enabled, the errors will be added to the console as warnings.
* Also add initial support for Jetpack's Facebook Page widget.

### 0.5.2 ###
* Fix position of widget in sidebar if a `widget_instance` partial container is inserted prior to an refresh.
* Only sort the DOM if the order has changed, to improve performance.
* Introduce `getWidgetIds` and `reflowWidgets` methods for widget area partial.
* Introduce `partial-content-moved` event when a widget is re-ordered.
* Add support for Jetpack's Twitter Timeline and Contact Info widgets.

### 0.5.1 ###
* Fix broken Ajax in preview by adding missing script dependency for wp-util.
* Add support for plugin loaded via theme (on WordPress.com).
* Ensure that dynamic_sidebar_params filter applies during partials render request.
* Remove unnecessary is_registered_sidebar() which broke nested sidebar stack

### 0.5.0 ###
Complete rewrite utilizing new selective/partial refresh framework. Selective refreshing of nav menus (in core) and widgets (in this plugin) were rewritten to make use of the new framework. Documentation forthcoming.

### 0.4.3 ###
* Fix PHP 5.2 compatibility.
* Fix support for subdirectory installs.

### 0.4.2 ###
* Fix typo causing twentyten, twentyeleven, and twentytwelve not being enabled for widget partial refresh.

### 0.4.1 ###
* Remove partial refresh functionality since it is now moved over to Menu Customizer directly.

### 0.4 ###
* Add integration for Menu Customizer.
* See all changes: [0.3...0.4](https://github.com/xwp/wp-customize-partial-refresh/compare/0.3...0.4)

### 0.3 ###
* Use trigger_error instead of error_log; do nothing on WPCOM
* Introduce widgetsExcludedForPostMessage stop gap
* Communicate to pane all widgets rendered, not just main one
* See all changes: [0.2...0.3](https://github.com/xwp/wp-customize-partial-refresh/compare/0.2...0.3)

### 0.2 ###
* Fix partial refresh during theme preview ([PR #9](https://github.com/xwp/wp-customize-partial-refresh/pull/9))
* Eliminate remaining direct calls to parent window, use `postMessage` only ([PR #8](https://github.com/xwp/wp-customize-partial-refresh/pull/8))
* Allow inactive widgets to be rendered if sidebar is specified ([PR #10](https://github.com/xwp/wp-customize-partial-refresh/pull/10))
* Add initial support for plugins to support custom sidebars
* Various other fixes and hardening, see [0.1...0.2](https://github.com/xwp/wp-customize-partial-refresh/compare/0.1...0.2).

### 0.1 ###
* Resurrect Widget Customizer partial refreshes.


