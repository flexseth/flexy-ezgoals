=== EZ Goals ===
Contributors: sethflexperception
Tags: goals, deadlines, productivity, tracking, blocks
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add goals to WordPress posts with deadline tracking and color-coded urgency indicators.

== Description ==

EZ Goals makes it easy to attach goal-tracking blocks to your WordPress posts. Set deadlines, track progress, and see at-a-glance urgency indicators with customizable color schemes.

**Features:**

* **Custom Goal Block** - Add goals directly in the WordPress block editor
* **Multiple Goal Types** - Near Term, Long Term, Stretch Goals, and Daily Goals
* **Deadline Tracking** - Set deadlines with an intuitive date picker
* **Color-Coded Urgency** - Visual indicators show how soon goals are due
  * Grey - Plenty of time (7+ days)
  * Blue - Upcoming (3-7 days)
  * Yellow - Approaching (1-3 days)
  * Orange - Due soon (< 1 day)
* **Customizable Colors** - Change urgency colors to match your theme
* **Performance Optimized** - Smart caching minimizes database queries
* **Transient Goals** - Mark goals as complete when done

**How It Works:**

1. Add a Goal Block to any post or page
2. Select the goal type and set a deadline
3. Goals automatically appear above your content with urgency indicators
4. Mark goals complete as you achieve them

**Perfect For:**

* Content creators tracking publication goals
* Project managers setting milestones
* Bloggers managing editorial calendars
* Anyone who wants visible, deadline-driven goal tracking

== Installation ==

1. Upload the `flexy-ezgoals` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Run `npm install && npm run build` from the plugin directory (required for block to appear)
4. Add a Goal Block to any post using the block inserter
5. Customize colors at Settings → EZ Goals (optional)

== Frequently Asked Questions ==

= Where do goals appear? =

Goals appear above the post content on the front-end when they're active. In the editor, you'll manage goals through the block inspector sidebar.

= Can I customize the colors? =

Yes! Go to Settings → EZ Goals to customize the urgency color scheme. Choose colors that match your theme and ensure good contrast for readability.

= How many goals can I add to a post? =

You can add as many goals as you need. Each goal is displayed with its own urgency indicator.

= What happens to completed goals? =

Completed goals are hidden from the front-end display but remain in the post meta for future reference.

= Does this work with any theme? =

Yes! EZ Goals uses WordPress standards and will work with any properly-coded theme. Color contrast is automatically calculated for readability.

= How does caching work? =

The plugin uses WordPress transients to cache due-goals lists. A daily cron job rebuilds the cache, and it's cleared whenever you update goals. This ensures minimal database overhead.

== Screenshots ==

1. Goal block in the WordPress editor
2. Multiple goals displayed on front-end with urgency colors
3. Settings page for color customization
4. Goal type selector with date picker

== Changelog ==

= 0.1.0 =
* Initial release
* Custom goal block with date picker
* Four goal types: Near Term, Long Term, Stretch, Daily
* Color-coded urgency indicators
* Settings page for color customization
* REST API for goal management
* Performance-optimized caching
* i18n ready

== Upgrade Notice ==

= 0.1.0 =
Initial release of EZ Goals.
