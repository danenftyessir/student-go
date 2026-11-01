
# MASTER AUDIT STUDENT GO

## Comprehensive Website Design Audit & Design Discovery

### 1. Role and Objective

Act as a **Senior Product Designer, Design Systems Architect, UX Researcher, and Frontend Design Auditor**.

Your responsibility in this phase is to understand the current product, explore all accessible pages and states, assess its visual design and user experience, identify inconsistencies, and develop evidence-based recommendations for the future design direction.

**This is strictly an analysis and discovery phase. Do not implement the design yet.**

Do not modify existing application source code, styling, components, configuration, dependencies, database schemas, seed data, or application behavior. Do not replace existing UI elements or begin building the new design system.

The expected outcome is a thorough, actionable design audit that can serve as the primary reference for the next design phase.

---

### 2. Application Environment, Local Database, and Test Credentials

The application is **already running locally with a local PostgreSQL database**. You must use the existing running application rather than assuming you need to initialize the environment.

#### 2.1 Application URL and Port

Open the application at:

**http://127.0.0.1:8001/login**

The application is running on **port 8001**.

Important environment constraints:

- Port `8001` is the intended local application instance for this audit.
- Port `8000` is running a different server configuration that still uses Supabase credentials from `.env`.
- Do not use port `8000` for this audit.
- Do not modify `.env`.
- The local database connection to the `studentgo` PostgreSQL database, using the local PostgreSQL user `postgres`, is supplied through environment variables when the intended server is launched.
- If you start the application yourself, do not assume that running `php artisan serve` without the original environment-variable configuration will connect to the local database.
- Prefer using the application already running on port `8001`.
- If the application is unavailable, investigate the existing process and startup configuration before attempting to restart it. Preserve the existing local database configuration and do not expose or overwrite environment secrets.

**Do not switch databases, change application ports, alter environment variables, or reset the database just to perform the design audit.**

#### 2.2 Available Test Accounts

Use the following accounts to access the website and explore role-specific interfaces.

The password for all three accounts is:

`password123`

| User role               | Email                                                   |
| ----------------------- | ------------------------------------------------------- |
| Mahasiswa (Student)     | `juliasuryanto1000@uniku.ac.id`                       |
| Institusi (Institution) | `dinaskesehatankabupatenbogor85@dinaskesehatan.go.id` |
| Perusahaan (Company)    | `hr@koperasitanimakmurjaya.co.id`                     |

All credentials above are intended for local development and testing.

Log in with each account separately and investigate the application from each role's perspective. Do not limit the audit to the first account that successfully logs in.

For each role:

1. Open the login page and verify the available interface.
2. Log in using the relevant test credentials.
3. Record the landing page, navigation structure, available routes, and visible actions.
4. Explore all accessible pages and relevant interactive states.
5. Investigate dynamic record detail pages and the available data associated with that role.
6. Record differences in layouts, components, terminology, permissions, and user workflows.
7. Test direct navigation and refresh behavior for accessible routes.
8. Log out before switching to another account, or otherwise ensure sessions are correctly separated.
9. Document any route that cannot be accessed because of permissions, authentication failures, missing data, or application errors.

Do not assume that a route is irrelevant merely because it is absent from the navigation menu of one role. It may be accessible to another role.

Do not assume that all roles share the same dashboard, page templates, actions, or information hierarchy.

#### 2.3 Database Inventory and Available Data

The local database currently contains:

- **908 users in total**
  - 700 mahasiswa.
  - 79 institusi.
  - 129 perusahaan.
- **77 problem records.**

Use this information as the initial dataset inventory. Verify the relevant data sources through the application, repository, or safe read-only database inspection when access is available.

Use the real data to discover actual routes, identifiers, relationships, and variations in rendered content.

For example, records may lead to different detail pages, profile pages, problem pages, or other data-driven views, depending on the application's actual routing structure.

Do not assume that the database counts correspond directly to the number of accessible pages. Discover how records are connected to routes, which identifiers are exposed in the UI, and which records can actually be reached.

The database counts above are provided as starting context. If your inspection produces different counts, investigate the discrepancy and report the source, scope, and reason for the difference. Do not silently replace verified results with the initial counts.

Do not modify records, delete data, create test users, or seed additional records during the audit.

#### 2.4 Regional Data Limitation

The BPS API is currently unreachable.

As a result, the local database/application contains placeholder regional data: for each of 29 provinces, one placeholder regency/city has been added, using a name resembling `Kota <nama provinsi>`.

This data is sufficient for exploring the application and observing how regional information is displayed, but **it is not authentic regional administrative data**.

During the audit:

- Treat these regional entries as placeholders.
- Do not interpret them as verified official administrative names or actual regional hierarchies.
- Do not flag the placeholder names themselves as a product-design defect.
- You may identify genuine layout problems caused by their length, formatting, or presentation.
- Evaluate whether dropdowns, location selectors, profile sections, filters, and dependent fields render and behave consistently with the data currently available.
- Document where the limited dataset prevents meaningful verification of a UI state or interaction.
- Do not modify regional records or attempt to replace the placeholder data as part of this design audit.

Separate data-quality limitations from actual UX and visual-design findings.

#### 2.5 Admin Account Limitation

An admin account is not included in the current seeder.

Do not assume that an admin account exists, fabricate admin credentials, bypass authentication, or treat inaccessible admin-only routes as ordinary public routes.

Investigate any admin-related routes and components through repository and routing analysis. Record their intended structure when the implementation provides sufficient evidence, and clearly distinguish code-level findings from visually verified findings.

If an admin interface cannot be accessed, report it as blocked or not visually inspected. Do not claim that it has been fully audited.

#### 2.6 Environment Safety and Audit Boundaries

The primary task is design analysis, not infrastructure debugging.

Do not spend time attempting to restore the BPS API, replace placeholder data, reconfigure PostgreSQL, migrate the database, or repair unrelated backend issues unless an application issue directly prevents a necessary part of the audit.

When blocked, investigate what can safely be learned from the source code and existing UI, then document the limitation.

Never expose `.env` contents, database passwords, session cookies, authentication tokens, or other secrets in the report or screenshots.

---

### 3. Initial Repository and Application Investigation

Before making any design judgments, understand how the existing application works.

Investigate:

- The project structure and frontend framework.
- Routing configuration and page organization.
- Application entry points and layouts.
- Existing design tokens, global styles, CSS variables, Tailwind configuration, and theme definitions.
- Font imports, font files, font weights, and typography configuration.
- Shared components, page-specific components, and component dependencies.
- Navigation menus, sidebars, headers, footers, breadcrumbs, and other global UI elements.
- Authentication flows, role-based interfaces, and permission-dependent pages.
- API endpoints, server actions, database models, mock datasets, fixtures, and seed data relevant to rendering pages.
- Existing responsive breakpoints and mobile-specific layouts.
- Loading, empty, error, success, disabled, and other UI states.
- Existing icons, SVG assets, illustrations, logos, and image resources.

Identify which parts of the interface are generated from reusable components and which are implemented individually.

Do not assume that the current code structure accurately reflects the intended user experience. Compare the implementation with the actual rendered interface.

When possible, run the application locally and examine it through a browser at its real routes. Use the existing server and database configuration described in Section 2.

If the application cannot be executed, document the limitation and distinguish code-level observations from visually verified observations.

---

### 4. Exhaustive Page and Route Discovery

This is one of the most important requirements.

**You must explore the entire application, not merely the homepage, dashboard, or a handful of representative screens.**

#### 4.1 Build a Complete Route Inventory

Discover every accessible route by combining evidence from:

- Framework routing files and route definitions.
- File-system-based routing conventions.
- Navigation menus and internal links.
- Buttons, links, dropdowns, and other interactive elements that lead to another page.
- API responses and data-driven links.
- Redirects and authentication flows.
- Pagination, search results, filters, tabs, and query parameters.
- Routes that are not linked directly from the homepage.
- Role-specific or conditionally accessible routes.
- Concrete URLs discovered from the local database and rendered application.

Classify all discovered routes into the following categories:

1. Static routes.
2. Dynamic routes with path parameters.
3. Dynamic routes with nested parameters.
4. Routes controlled by query parameters.
5. Authentication-related routes.
6. Routes that depend on user roles or permissions.
7. Error, fallback, and not-found routes.
8. Other route patterns discovered during exploration.

For every route, record its actual URL pattern, purpose, entry points, access requirements, shared layout, and exploration status.

#### 4.2 Explore Every Dynamic Route Instance

Dynamic routes require more than inspecting a single example.

For example, a route pattern might resemble:

- `/students`
- `/students/1`
- `/students/2`
- `/students/3`

These are illustrative examples only. Discover the actual URL patterns and identifiers used by the application.

For every dynamic route:

1. Identify the parameter names and how the application resolves them.
2. Determine where the available identifiers originate, such as a database, API, local fixture, seed file, or mock dataset.
3. Enumerate all discoverable identifiers when the dataset is accessible.
4. Open and inspect every reachable record-specific URL when practical.
5. Compare how different records affect the rendered interface.
6. Identify differences in content length, metadata, relationships, status, images, available actions, and layout.
7. Record broken pages, missing data, inconsistent layouts, unexpected redirects, and other rendering problems.

**Never assume that two URLs following the same route pattern render the same interface.**

A detail page with a long title, missing image, different status, more metadata, or additional actions can expose design problems that are invisible on another record.

When a route contains many records, automate browser navigation, screenshots, or DOM inspection where appropriate. You may group pages that have demonstrably identical structural layouts, but you must still establish the inventory of identifiers and check for meaningful visual and behavioral variations across them.

Do not claim that every instance has been inspected if only a subset has been visited.

If exhaustive inspection is impossible because of access limitations, data volume, unavailable credentials, or other blockers, document the total records discovered, the records actually inspected, the sampling method, and the remaining coverage gaps.

#### 4.3 Explore Multiple Data and UI States

Inspect relevant variations, including:

- Normal records with complete data.
- Records with unusually long or short content.
- Missing optional fields or images.
- Different statuses, categories, or permission-dependent actions.
- Empty collections and no-result searches.
- Loading and failed-request states.
- Invalid, unavailable, or nonexistent identifiers.
- Pagination boundaries and different filter combinations.
- Long tables, overflow-prone content, and unusually dense pages.

Only test states that are applicable to the actual application. Do not fabricate functionality that does not exist.

The local database contains placeholder regional data, so distinguish issues attributable to the test dataset from actual design defects.

#### 4.4 Test Interactions, Not Just Screenshots

Explore relevant interaction flows, including:

- Navigation between pages.
- Opening and closing menus or dialogs.
- Search, filters, sorting, and pagination.
- Tabs and expandable sections.
- Form validation and submission states.
- Buttons and their resulting actions.
- Modals, drawers, dropdowns, tooltips, and notifications.
- Browser back and forward navigation.
- Direct URL access and refresh behavior.
- Role-specific navigation and actions.
- Responsive navigation and touch interactions.

Record the intended behavior when it can be established and the actual behavior observed.

Do not perform destructive actions or modify production data. Use the supplied test accounts and the local environment.

---

### 5. Comprehensive Visual Design Analysis

Assess the existing website systematically. Do not rely on vague statements such as "the website looks outdated" or "the design needs to be modernized."

Every major finding must identify the relevant page or component, explain the visible problem, describe its impact, and suggest a direction for improvement.

#### 5.1 Typography and Font System

Investigate the current typography in detail.

Analyze:

- Primary and secondary font families.
- Whether the fonts fit the product's purpose and audience.
- Font weights and whether the available weights are used correctly.
- Heading hierarchy from page titles to section headings.
- Body text, labels, metadata, captions, and helper text.
- Font size, line height, letter spacing, and paragraph spacing.
- Text width, line length, and wrapping behavior.
- Readability in dense dashboards, data tables, forms, and detail pages.
- Typographic contrast between primary and secondary information.
- Consistency across pages and responsive breakpoints.
- Font fallbacks, loading behavior, and missing glyphs where relevant.

Identify where typography is being used to establish hierarchy and where the interface relies excessively on boxes, borders, colors, or decorative elements to communicate importance.

Recommend a typography direction suitable for Student Go, including a proposed type scale, font roles, weight conventions, and usage guidelines.

Do not select a font solely because it is currently popular or frequently used in AI-generated designs.

#### 5.2 Color System

Inspect all significant colors used in the application.

Document:

- Primary brand colors.
- Secondary and accent colors.
- Background and surface colors.
- Text and muted-text colors.
- Borders, dividers, and separators.
- Interactive states.
- Success, warning, error, and informational colors.
- Status-specific colors and badges.
- Hover, active, focus, selected, and disabled states.
- Light and dark surfaces, if applicable.
- Contrast and readability.
- Redundant or inconsistent color usage.

Distinguish semantic colors from decorative colors. A single semantic meaning should not be represented by unrelated colors across different parts of the product without a clear reason.

Assess whether the current palette supports a coherent visual identity and remains usable across forms, tables, dashboards, detail pages, and mobile layouts.

Propose a role-based color system for the future Student Go design, rather than just suggesting an attractive collection of hex codes.

Do not invent the final brand palette prematurely. Explain the design rationale behind each proposed color role.

#### 5.3 Layout, Spacing, and Geometry

Investigate the overall spatial structure of the interface.

Assess:

- Page margins and content widths.
- Grid systems and column proportions.
- Sidebar and navigation dimensions.
- Vertical rhythm and section spacing.
- Padding and gaps within components.
- Alignment of text, icons, controls, and data.
- Density of information.
- Relationship between headings and their content.
- Use of whitespace and negative space.
- Border radius and corner consistency.
- Border thickness, shadows, and elevation.
- Horizontal and vertical overflow.
- Alignment between related pages.
- Layout behavior with dynamic content.

Identify unnecessarily constrained layouts, excessive whitespace, overcrowded screens, inconsistent spacing, and elements that lack a clear visual relationship.

Recommend a consistent spacing scale and layout principles that support the actual information architecture.

#### 5.4 Component and Visual Pattern Audit

Inventory recurring interface patterns, including:

- Headers and navigation.
- Sidebars and navigation groups.
- Breadcrumbs.
- Page titles and action areas.
- Search bars and filter controls.
- Tables and list views.
- Cards and panels.
- Forms and field groups.
- Buttons and icon buttons.
- Badges and status indicators.
- Tabs and segmented navigation.
- Dialogs and drawers.
- Empty states.
- Notifications and feedback messages.
- Pagination.
- Charts and data visualizations.
- Avatars and profile sections.
- Detail page information groups.
- Onboarding and informational sections.

For every significant pattern, determine whether it is consistent, understandable, accessible, and appropriate for its context.

Identify duplicated components that look or behave differently without a functional reason.

Also identify cases where supposedly reusable components impose unnecessary visual uniformity on pages with different information needs.

Do not assume every section should become a reusable card or that every page should use the same layout template.

#### 5.5 Icons, SVGs, Illustrations, and Graphics

Inspect the application's graphical assets.

Analyze:

- Icon families and their consistency.
- Stroke weight, proportions, and visual style.
- Icon size and alignment with text.
- The use of generic iconography.
- Unnecessary decorative illustrations.
- Placeholder graphics and stock-like visual elements.
- SVG quality, scaling, and rendering.
- Whether icons communicate meaning or merely fill space.
- Whether complex graphics improve comprehension.
- Whether the same semantic action uses different icons in different locations.

Recommend a coherent graphic direction for Student Go.

For future custom illustrations, logos, diagrams,
