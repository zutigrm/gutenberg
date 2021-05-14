# Menus

This package contains common functionality used by the Navigation block (see `@wordpress/block-library`) and the Navigation editor (see `@wordpress/edit-navigation`).

> This package is meant to be used only with WordPress core. Feel free to use it in your own project but please keep in mind that it might never get fully documented.

## Installation

Install the module

```bash
npm install @wordpress/menus
```

_This package assumes that your code will run in an **ES2015+** environment. If you're using an environment that has limited or no support for ES2015+ such as IE browsers then using [core-js](https://github.com/zloirock/core-js) will add polyfills for these methods._

## API documentation

<!-- START TOKEN(Autogenerated API docs) -->

<a name="convertMenuItemsToBlocks" href="#convertMenuItemsToBlocks">#</a> **convertMenuItemsToBlocks**

Convert a flat menu item structure to a tree of nested blocks.

_Parameters_

-   _menuItems_ `WPNavMenuItem[]`: An array of menu items.

_Returns_

-   `WPBlock[]`: An array of blocks.

<a name="menuItemToBlockAttributes" href="#menuItemToBlockAttributes">#</a> **menuItemToBlockAttributes**

Convert block attributes to menu item.

Utilised in both block and editor.

_Parameters_

-   _menuItem_ `WPNavMenuItem`: the menu item to be converted to block attributes.

_Returns_

-   `Object`: the block attributes converted from the WPNavMenuItem item.

<a name="NEW_TAB_TARGET_ATTRIBUTE" href="#NEW_TAB_TARGET_ATTRIBUTE">#</a> **NEW_TAB_TARGET_ATTRIBUTE**

The string identifier for the menu item's "target" attribute indicating
the menu item link should open in a new tab.

_Type_

-   `string`


<!-- END TOKEN(Autogenerated API docs) -->

<br/><br/><p align="center"><img src="https://s.w.org/style/images/codeispoetry.png?1" alt="Code is Poetry." /></p>