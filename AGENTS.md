# AGENTS.md — portal.vrcse PHP Application Frontend

> **Scope:** This document describes the PHP application source at `e:/VLSE/labalse_front` (a.k.a. `portal.vrcse` frontend).  
> **Deployment:** This code is deployed to `PORTAL_SCRIPTS_DST` (default `/var/www/portal.vrcse`) by the deployment package in `e:/VLSE/labalse`.

---

## 1. Project Overview

| Attribute | Value |
|-----------|-------|
| **System name** | portal.vrcse / АИС СЭУ |
| **Language** | PHP 5.6 (mod_php) |
| **Encoding** | Windows-1251 (`cp1251`) — **NOT UTF-8** |
| **Database** | MariaDB 10.3, database `totaltab--v12` |
| **Architecture** | Custom lightweight PHP framework (no Composer, no MVC framework) |
| **Author** | Пекшев Петр Александрович, 2008 |
| **Frontend** | Table-based layouts + custom CSS theme system |

This is the **actual application source code** — PHP scripts, HTML templates (embedded in PHP), CSS themes, and JavaScript files that power the forensic expertise case-management portal.

---

## 2. Technology Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| **Language** | PHP 5.6 | Plain PHP, no OOP framework |
| **Encoding** | Windows-1251 | All `.php`, `.css`, `.js` files are in `cp1251` |
| **Database** | MariaDB 10.3 | Custom DB wrapper in `cores/core.db.php` |
| **HTML** | HTML 4.01 | Transitional/Strict doctype |
| **CSS** | Custom theme system | `themes/std0/` is the default theme |
| **JS** | Vanilla JS | Inline scripts + external files in `files/` |
| **Templates** | PHP includes | No template engine — raw `echo` and `include` |
| **PDF** | pdf.js | Embedded in `ext-lib/pdf.js/` |
| **XMPP** | XMPPHP | For notifications |

---

## 3. Directory Layout

```
labalse_front/
├── index.php                    # Main entry point (dashboard after login)
├── core.php                     # Core bootstrap: autoload, DB, auth, constants
├── core.ex.php                  # Extended core functions
├── config.php                   # DB credentials (read from $_SERVER / placeholders)
├── auth.php                     # Login page
├── exit.php                     # Logout handler
│
├── cores/                       # Core framework modules
│   ├── core.html.php            # HTML generation: MainHead_L2(), MainTail(), forms, tables
│   ├── core.db.php              # Database wrapper (mysql_* functions)
│   ├── core.auth.php            # Authentication logic
│   ├── core.user.php            # User management
│   ├── core.config.php          # Config loader from DB
│   ├── core.globals.php         # Global variables
│   ├── core.value.php           # Value formatting utilities
│   ├── core.debug.php           # Debug utilities
│   ├── core.rights.php          # Rights/permissions parsing
│   ├── core.maindb.php          # Main DB operations
│   └── core.report-151.php      # Report generation
│
├── class/                       # PHP classes (autoloaded)
│   ├── TDB.class.php            # Database class
│   ├── TUser.class.php          # User class
│   ├── TGroup.class.php         # Group class
│   ├── TQueryBuilder.class.php  # SQL query builder
│   ├── TSimpleList.class.php    # List rendering
│   ├── TSimpleXLSXTemplate.class.php  # Excel export
│   └── TQRCode.class.php        # QR code generation
│
├── themes/                      # Shared theme assets
│   ├── std0/                    # Default theme (CSS, images, sprites)
│   │   ├── base.css             # MAIN stylesheet (2921 lines) — embedded inline by core.html.php
│   │   ├── base-2.css           # Alternative base stylesheet
│   │   ├── index.css            # Index/dashboard page styles
│   │   ├── buttons.css          # Button styles (.btn, .btn1, .btn2, .btn3)
│   │   ├── forms.css            # Form element styles
│   │   ├── search.css           # Search panel styles
│   │   ├── info.css             # Info page styles
│   │   ├── messages.list.css    # Messages list styles
│   │   ├── equipment.*.css      # Equipment module styles
│   │   └── *.png, *.gif, *.psd  # Image assets (icons, buttons, sprites)
│   └── icons/                   # Icon set
│
├── files/                       # Shared JavaScript files
│   ├── base.js                  # Base JS utilities
│   ├── base.DOM.js              # DOM helpers
│   ├── base.calendarDlg.js      # Calendar dialog
│   ├── base.NAMES.js            # Name formatting
│   ├── search.js                # Search functionality
│   ├── index.js                 # Index page JS
│   ├── main-head--l2.html       # HTML head template (loaded by MainHead_L2)
│   ├── config.js.php            # JS config variables
│   ├── base.constants.js.php    # JS constants (month names, etc.)
│   └── fonts/                   # Custom fonts
│
├── adminka/                     # Admin panel module
│   ├── main.php, accounts.php, add-user.php, ...
│   ├── files/*.js               # Module-specific JS
│   └── themes/std0/*.css        # Module-specific CSS
│
├── maindb/                      # Main database module (cases, expertise)
│   ├── *.php                    # Main DB pages
│   ├── files/*.js               # Module JS
│   └── themes/std0/*.css        # Module CSS
│
├── bills/                       # Billing/invoices module
│   ├── bill.php, list.php, main.php, ...
│   ├── files/*.js               # Module JS
│   └── themes/std0/*.css        # Module CSS
│
├── doc-generator/               # Document generation (FOP integration)
│   ├── doc-generator.js         # Doc generator JS
│   ├── themes/std0/*.css        # Doc generator CSS
│   └── tmpl/                    # Document templates
│
├── file_store/                  # File storage module
│   ├── integration.php          # File store integration
│   └── themes/std0/             # File store styles
│
├── time_table/                  # Timetable/schedule module
│   ├── *.php
│   ├── files/*.js
│   └── themes/std0/*.css
│
├── tools/                       # Tools module
│   ├── *.php
│   └── themes/std0/*.css
│
├── equipment.*.php              # Equipment management
├── marks.core.php               # Marks/grades system
├── documents.php                # Document management
├── barcode.php                  # Barcode generation
├── kuvk/                        # КУВК integration (state case system)
│   └── from-kuvk/               # Incoming КУВК data
│
├── ext-lib/                     # External libraries
│   ├── pdf.js/                  # PDF.js viewer
│   └── XMPPHP/                  # XMPP chat library
│
└── setup/                       # Setup/installation scripts
```

---

## 4. Architecture

### 4.1 Page Lifecycle

Every page follows this pattern:

```php
<?php
require_once( "core.php" );          // Bootstrap: DB, auth, utilities
TryLoginFromCookie();                 // Check auth
if ( !$LoginOk ) {
    Redirect( "auth.php" );           // Redirect to login
}

// Page-specific logic
// ... DB queries, data preparation ...

// Render page header
MainHead_L2( $subTitle, $subTitle2,
    array( '%UT/page-specific.css' ),    // CSS files
    array( 'files/page-specific.js' ),   // JS files
    'hlp/help-page.html',                // Help page link
    ''                                   // Body attributes
);

// Page content (raw HTML/PHP mixed)
?>
<div id="page-content">
    <!-- HTML content -->
</div>
<?php
// Render page footer
MainTail();
?>
```

### 4.2 HTML Template System (`cores/core.html.php`)

`MainHead_L2()` generates the complete HTML `<head>` and opening `<body>`:

1. Loads `files/main-head--l2.html` (HTML template)
2. **Embeds `themes/{theme}/base.css` INLINE** (compressed)
3. Embeds `search.css` INLINE
4. Loads additional CSS via `<link>` tags (with `%UT` → `themes/std0` substitution)
5. Loads JS files
6. Outputs page structure:
   ```html
   <div id="page">
     <div id="page-head">
       <p class="mhTitle">
         <span class="mhTitle1">Organization Name</span>
         <span class="mhTitle2">АИС СЭУ</span>
       </p>
       <table class="mhTable">
         <tr>
           <td class="mhtCell">
             <!-- User info + menu -->
           </td>
         </tr>
       </table>
     </div>
     <div id="page-content">
       <!-- PAGE CONTENT GOES HERE -->
     </div>
     <div id="page-footer"></div>
   </div>
   ```

### 4.3 Theme System

- **Theme location:** controlled by `$UserThemeLoc` global (default: `std0`)
- **Base styles:** `themes/std0/base.css` is embedded inline in every page
- **Module styles:** each module can load its own CSS via `MainHead_L2()`
- **Image assets:** stored in `themes/std0/` (buttons, icons, sprites)

### 4.4 Database Layer

- Custom wrapper in `cores/core.db.php` (uses `mysql_*` functions)
- Connection params from `config.php` (reads `$_SERVER` variables set by Apache `SetEnv`)
- Main connection available as `$portalDB` global

---

## 5. Key Files for Frontend Changes

If you want to modify the visual appearance **without touching PHP logic**:

| File | Purpose | Lines |
|------|---------|-------|
| `themes/std0/base.css` | **Main stylesheet** — page layout, headers, menus, tables, dialogs, calendars | 2921 |
| `themes/std0/index.css` | Dashboard/index page panels, sections, tools | 381 |
| `themes/std0/buttons.css` | All button styles (.btn, .btn1, .btn2, .btn3, disabled variants) | 116 |
| `themes/std0/forms.css` | Form inputs, selects, textareas | ~100 |
| `themes/std0/search.css` | Search panel styles | ~200 |
| `files/main-head--l2.html` | HTML `<head>` template | ~20 |
| Module CSS | `adminka/themes/std0/*.css`, `bills/themes/std0/*.css`, etc. | varies |

**Important:** `base.css` is embedded **INLINE** (not loaded as external file), so changes take effect immediately after file modification — no cache issues for this file.

---

## 6. Code Style & Conventions

- **Encoding:** All files are **Windows-1251 (`cp1251`)**. Do NOT save as UTF-8 or you will break Russian text.
- **Indentation:** Tabs (mixed with spaces in some places)
- **Variables:** `camelCase` for PHP variables, `PascalCase` for classes
- **CSS classes:** Mostly lowercase with hyphens (`.mhTitle`, `.page-content`)
- **PHP tags:** Always `<?php ... ?>` (no short tags)
- **Strings:** Single quotes preferred for literals, double quotes for interpolation
- **Comments:** Russian language, often with author copyright notice

---

## 7. How to Modify the Frontend

### 7.1 Change Colors / Theme

Edit `themes/std0/base.css`:
- `#page-head { background-color: #fec }` — header background
- `.mhMenu` — top navigation menu
- `.mhTable` — header info table
- `#page-content` — main content area
- `.tdlg-base` — dialog boxes
- `.dlg-calendar-*` — calendar popup

### 7.2 Change Buttons

Edit `themes/std0/buttons.css`:
- `.btn`, `.btn1`, `.btn2`, `.btn3` — gradient buttons
- `.btnd`, `.btn1d`, etc. — disabled buttons

### 7.3 Change Dashboard Layout

Edit `themes/std0/index.css`:
- `#main-table` — dashboard table layout
- `#left-column` — left sidebar (256px)
- `.panel-header` — section headers
- `.panel-content` — section content boxes
- `#sections` — navigation sections
- `#tools` — tools panel

### 7.4 Add a New Theme (Safest Approach)

1. Copy `themes/std0/` to `themes/mytheme/`
2. Modify CSS files in `themes/mytheme/`
3. Update `$UserThemeLoc` default in `cores/core.html.php` or set per-user in DB

---

## 8. Security Considerations

- **No input sanitization framework** — each page handles its own. Be careful with SQL injection.
- **Auth via cookies** — `TryLoginFromCookie()` in `core.php`
- **Rights parsing** — string-based rights system (e.g., `'RECORDS = ADD / EDIT / DELETE'`)
- **DB password** — comes from Apache `SetEnv` (see deployment package `labalse/`)

---

## 9. Known Limitations

- **PHP 5.6 EOL** — uses deprecated `mysql_*` functions, `__autoload()`, etc.
- **CP1251 encoding** — modern libraries expecting UTF-8 may break
- **Table-based layouts** — not responsive, fixed widths common
- **No build step** — plain PHP + CSS + JS, no webpack/vite/etc.
- **Inline CSS embedding** — `base.css` is inlined on every page load (performance impact)
- **No API separation** — business logic and presentation are tightly coupled

---

## 10. Testing Changes

Since this is deployed via the `labalse` package:

1. Modify CSS/PHP files in the deployed directory (`/var/www/portal.vrcse/`)
2. Refresh browser — `base.css` is embedded inline, so no cache issues
3. For linked CSS files (module styles), append `?mtime=...` is automatic
4. **No build step required** — changes are immediate

---

## 11. Relationship to `labalse/` Deployment Package

```
labalse/          ← Deployment scripts, configs, DB dumps
└── conf/environments.sh  ← Points PORTAL_SCRIPTS_DST to:

labalse_front/    ← This PHP application
└── Deployed to → /var/www/portal.vrcse (or PORTAL_SCRIPTS_DST)
```

The `labalse` repo handles server setup, DB init, SSL, backups.  
This `labalse_front` repo IS the actual application that gets served by Apache.
