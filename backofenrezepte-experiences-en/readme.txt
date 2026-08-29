=== Backofenrezepte Experience Database ===
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Collects, moderates, and manages structured user experiences for baking recipes.

== Description ==

This is a standalone experience database for oven/baking recipes, not a
generic contact form. Visitors can anonymously submit structured
experiences for a recipe (oven type, temperature, time,
result, problem, comment). Every experience is reviewed by an
administrator before publication.

= Features =

* Public REST submission endpoint with multi-layered anti-spam
  (honeypot, timing check, rate limiting, duplicate detection)
* Dedicated database table (no misuse of Custom Post Types)
* Moderation: pending / approved / rejected
* Admin dashboard with overview, list, filters, bulk actions
* Detail view/editor with a private internal note
* Recipe-related statistics aggregates
* CSV export (UTF-8 BOM, Excel-compatible)
* Public, read-only aggregate endpoint for approved data
* No external paid services required
* Privacy-friendly: no raw storage of IP addresses

= Frontend Integration =

The existing experience card (HTML/CSS/JS) reads the data provided
by the plugin from the global object:

`window.BackofenRezepteExperience = {
    recipeId: 127,
    restUrl: "https://example.com/wp-json/backofenrezepte/v1/experiences",
    nonce: "...",
    vocabulary: { ovenTypes: {...}, results: {...}, problems: {...}, forms: {...} },
    i18n: { success: "...", error: "..." }
};`

This object is automatically provided on every singular page (is_singular()).
The `br_exp_should_localize` filter can be used to restrict its
availability to a specific recipe post type.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/.
2. Activate it in the WordPress admin area.
3. Check the default values under "Backofenrezepte → Settings".

== Privacy ==

No names, email addresses, or user accounts are collected.
For anti-spam purposes, a "fingerprint" formed from the IP address and
user agent, hashed with a random, site-specific salt, is stored
temporarily (hours up to a maximum of 24h) in WordPress transients,
never permanently in the experiences table, never exported.

== Changelog ==

= 1.0.0 =
First version.
