# Logingrupa.SearchOffersShopaholic

Extends Lovata.SearchShopaholic for offer search: adds a search_synonym field to offers
and swaps the container binding of SearchHelper for a locale-aware
TranslatableSearchHelper. Namespace LoginGrupa\SearchOffersShopaholic, composer package
logingrupa/oc-searchoffersshopaholic-plugin. Requires Lovata.Shopaholic, Lovata.Toolbox,
Lovata.OrdersShopaholic, Lovata.SearchShopaholic. README.MD documents usage.

## Environment

- Parent app: C:\laragon\www\nc.
- This plugin dir is its OWN git repo - commit here, not in the root repo.

## Architecture map

- classes/event/   OfferModelHandler (offer model extension), ExtendFieldHandler
                   (search_synonym backend field)
- classes/helper/  TranslatableSearchHelper - case-insensitive translated-field search,
                   bound parameters, base-table fallback when a translation row is missing
- components/      OfferList
- updates/         update_table_offers (adds search_synonym to lovata_shopaholic_offers)

## Quality gates

No working automated gate - tests do not exist and lint does not cover this dir.
composer lint does NOT cover this plugin (phpcs.xml scope excludes plugins/logingrupa) - fix
phpcs.xml scope or lint manually; `vendor/bin/phpcs --standard=phpcs.xml <plugin path>` won't
work either since the ruleset pins files; note as known gap.

## Ship

Ship via /nc-ship (root CLAUDE.md release flow); package logingrupa/oc-searchoffersshopaholic-plugin.

## Conventions

Root CLAUDE.md governs: Hungarian notation, Store -> Collection -> Item read path, Tiger-Style.

## Gotchas

- boot() does `$this->app->bind(SearchHelper::class, TranslatableSearchHelper::class)` -
  EVERY call site that resolves SearchHelper from the container gets this plugin's
  subclass. Behavior changes here affect all Shopaholic search, not just offers.
- Namespace case is mixed: Plugin.php imports handlers as LoginGrupa\, the files declare
  Logingrupa\ - works because PHP namespaces are case-insensitive.
- version.yaml line 5 is malformed ("- update_table_offers.php1.0.3:" merged line) and the
  migration declares namespace Lovata\SearchShopaholic\Updates (stolen namespace).
