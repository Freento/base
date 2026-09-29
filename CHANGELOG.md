# Changelog

## 1.0.0

First release. Freento_Base is the shared foundation for all Freento extensions: it provides the
common "Freento" admin menu item and configuration tab, shows which Freento products are installed
and whether updates are available, recommends other free Freento modules and displays
announcements published on freento.com.

### Requirements

- PHP 8.1 or later.
- No configuration is needed after installation: the module works out of the box.

### Features

- **Shared "Freento" admin menu item and configuration tab.** Other Freento modules attach their
  own items to the `Freento_Base::freento_base` ACL resource and to the "freento" configuration
  tab, instead of each module creating its own root menu item. The menu item carries the Freento
  logo, added as an SVG icon through the standard admin LESS pipeline.

- **New configuration page: `Stores > Configuration > Freento > Products & Notifications`**
  (section `freento_base`, ACL resource `Freento_Base::base_config`). It contains three groups.

- **Products.** A table of the Freento modules installed on the store, with the installed version
  and the latest published one. Only modules installed through Composer are listed: the version is
  read from the module's own `composer.json`. When a newer version exists, it is shown next to the
  installed one and links to the module page. Modules are sorted by name, and Freento_Base itself
  is not listed.

- **Recommended for you.** Freento modules from the feed that are not installed on this store yet,
  with a short description and a link. The group always ends with a link to the Freento GitHub
  organization.

- **Notifications.** An HTML block published by Freento, for product announcements and news.

- **Both feeds are cached for 24 hours** and are requested with a 3 second timeout, so a slow or
  unavailable freento.com never blocks the Admin panel. A failed request is written to the log and
  the page is rendered without the corresponding group.

### Installation

1. The module is installed automatically as a dependency of other Freento modules, so there is no
   need to install it explicitly.
2. Grant the "Freento" and "Products & Notifications" ACL resources to the roles that need access.
