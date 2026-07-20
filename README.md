# EZ Goals - WordPress Goal Tracking Plugin

Add deadline-driven goals to WordPress posts with visual urgency indicators and smart caching.

## Features

- 🎯 **Custom Goal Block** - Gutenberg block for managing goals
- 📅 **Multiple Goal Types** - Near Term, Long Term, Stretch Goals, Daily Goals
- ⏰ **Quick Date Presets** - Today, Tomorrow, Next Week, Next Month
- 🎨 **Color-Coded Urgency** - Visual indicators show deadline proximity
  - Grey (7+ days) - Plenty of time
  - Blue (3-7 days) - Upcoming
  - Yellow (1-3 days) - Approaching
  - Orange (<1 day) - Due soon
- 👁️ **Editor Preview** - See goal display in real-time while editing
- 🎨 **Customizable Colors** - Settings page to match your theme
- ⚡ **Performance Optimized** - Smart caching with minimal database queries
- ✅ **Mark Complete** - Track goal completion
- 🌐 **i18n Ready** - Fully translatable

## Installation

### Development Setup

```bash
# Clone the repository
git clone https://github.com/yourusername/flexy-ezgoals.git
cd flexy-ezgoals

# Install dependencies
npm install

# Build the plugin
npm run build
```

### WordPress Installation

1. Copy the `flexy-ezgoals` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the Goal Block to any post or page
4. Manage goals in the Post sidebar panel

### Symbolic Link (Development)

```bash
# Link to your WordPress plugins directory
ln -s /path/to/flexy-ezgoals /path/to/wordpress/wp-content/plugins/flexy-ezgoals
```

## Usage

### Adding Goals

1. **In the Editor**
   - Insert the "Goal Block" in your post
   - Open the **Post** tab in the sidebar (not Block tab)
   - Scroll down to the **Goals** panel
   - Fill in Goal Type, Label, and Deadline
   - Click "Add Goal"

2. **Quick Deadlines**
   - Use the "Quick Deadline" dropdown for common dates
   - Or select "Custom Deadline" for specific date/time

3. **Preview**
   - Goals appear below the block in the editor
   - Shows exactly how they'll look on the front-end

### Customizing Colors

1. Go to **Settings → EZ Goals**
2. Adjust colors for each urgency level
3. Save changes

### Front-End Display

- Goals automatically appear above post content
- Color-coded by urgency
- Responsive design
- Automatic contrast calculation for readability

## Architecture

### Storage Strategy

- **Post Meta** - Goals stored as `_ezgoals_goals` post meta (serialized array)
- **Transients** - Cached "due soon" lists rebuilt daily via cron
- **Options API** - Color settings stored in `flexy_ezgoals_colors`
- **No custom DB tables** - WordPress-native storage only

### Performance

- **1 query per page load** (cached via WordPress object cache)
- **Daily cron** rebuilds "due today" transient
- **Conditional loading** - Front-end CSS only loads when post has goals

### REST API

- `GET /wp-json/flexy-ezgoals/v1/goals/{post_id}` - Fetch goals
- `POST /wp-json/flexy-ezgoals/v1/goals/{post_id}` - Save goals

## Development

### Build Commands

```bash
npm run build   # Production build
npm run start   # Development watch mode
npm run lint:js # JavaScript linting
npm run lint:css # CSS linting
```

### File Structure

```
flexy-ezgoals/
├── flexy-ezgoals.php      # Plugin bootstrap
├── includes/              # PHP functionality
│   ├── post-meta.php      # Goal storage & helpers
│   ├── display.php        # Front-end rendering
│   ├── caching.php        # Cron & transients
│   ├── rest-api.php       # REST endpoints
│   ├── settings.php       # Settings page
│   └── enqueue.php        # Asset loading
├── src/                   # Block source
│   └── goal-block/
│       ├── block.json     # Block metadata
│       ├── index.js       # Registration
│       ├── edit.js        # Editor component
│       └── style.scss     # Styles
└── build/                 # Compiled assets (generated)
```

## WordPress Requirements

- **WordPress**: 6.0+
- **PHP**: 7.4+
- **Node.js**: 18+ (for development)

## Standards & Compliance

- ✅ WordPress Coding Standards (WPCS)
- ✅ Plugin Check Plugin (PCP) compliant
- ✅ Functional paradigm (no OOP)
- ✅ Internationalization (i18n)
- ✅ Accessibility (WCAG AA)
- ✅ Security (nonces, capability checks, sanitization, escaping)

### Plugin Check Expected Warnings

**Note:** Plugin Check warnings (severity < 7) do NOT block WordPress.org submission. The Plugin Check team designed it this way to allow for false positives ([GitHub Issue #479](https://github.com/WordPress/plugin-check/issues/479)).

#### WordPress.DB.SlowDBQuery.slow_db_query_meta_key
**File:** `includes/caching.php:27`  
**Status:** ⚠️ WARNING (not a blocker)

**Why this follows WordPress best practices:**
- ✅ Query runs **once daily** via WP-Cron (not on page load) - [WP-Cron Docs](https://developer.wordpress.org/plugins/cron/)
- ✅ Results cached using **Transients API** (24-hour expiration) - [Official Pattern](https://developer.wordpress.org/apis/transients/)
- ✅ Uses `'fields' => 'ids'` optimization - [Recommended in WP 6.1](https://make.wordpress.org/core/2022/10/07/improvements-to-wp_query-performance-in-6-1/)
- ✅ Cache invalidation on content changes - Required best practice
- ✅ No performance impact on site visitors - Query never runs on page load

**Official WordPress guidance:**
> "Transients are ideal for caching 'long/expensive database queries or complex processed data' that can noticeably improve site load times." - [WordPress Transients API Handbook](https://developer.wordpress.org/apis/transients/)

**Performance impact:** Zero impact on visitors (background process + cached)

#### hidden_files (.gitignore, .distignore)
- Development files excluded from distribution via `.distignore`
- Standard WordPress.org practice

#### unexpected_markdown_file (TODO.md)
- Development tracking file
- Excluded from distribution via `.distignore`

**Plugin Check Status:** ✅ 0 Errors | ⚠️ 3 Expected Warnings (all documented above)

## Roadmap

### Future Enhancements

- [ ] Custom Post Type option for better scalability
- [ ] Goal templates
- [ ] Goal categories/tags
- [ ] Bulk goal operations
- [ ] Goal statistics/analytics
- [ ] Export/import functionality
- [ ] Email reminders for due goals

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

GPL-2.0-or-later

## Credits

Built with [Claude Code](https://claude.ai/claude-code) and [Claude for WordPress](https://github.com/yourusername/Claude-for-WordPress).

## Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/flexy-ezgoals/issues)
- **Documentation**: [Plugin README](readme.txt)

---

Made with ❤️ for the WordPress community
