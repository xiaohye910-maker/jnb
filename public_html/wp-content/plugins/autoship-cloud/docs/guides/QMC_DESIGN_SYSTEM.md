# QMC Design System & Component Implementation Guide

> **Version**: 2.0
> **Last Updated**: 2025-11-19
> **Purpose**: Comprehensive design system for QPilot Merchant Center (QMC) application

---

## Table of Contents

### Part I: Executive & Customer Success Guide
1. [Design System Overview](#design-system-overview)
2. [Visual Layout Patterns](#visual-layout-patterns)
3. [Component Catalog](#component-catalog)
4. [User Experience Principles](#user-experience-principles)
5. [How to Request New Features](#how-to-request-new-features)

### Part II: Developer Implementation Guide
6. [Technical Architecture](#technical-architecture)
7. [Layout Implementation Patterns](#layout-implementation-patterns)
8. [Component Development Guide](#component-development-guide)
9. [Theme & Styling System](#theme--styling-system)
10. [Best Practices & Code Examples](#best-practices--code-examples)

---

# Part I: Executive & Customer Success Guide

## Design System Overview

The QMC Design System is a comprehensive framework that ensures consistency, usability, and scalability across the QPilot Merchant Center application. It defines how every screen, component, and interaction should look and behave.

### Core Design Principles

1. **📐 Compact & Efficient**: Maximize data density while maintaining readability
2. **🎯 Task-Focused**: Every screen has a clear primary action
3. **📱 Responsive**: Adapts seamlessly from mobile to desktop
4. **♿ Accessible**: WCAG 2.1 AA compliant for all users
5. **⚡ Performance-First**: Fast load times and smooth interactions

### Application Structure

```
┌──────────────────────────────────────────────────────────────────┐
│                        HEADER (60px)                              │
│  [Logo]  Sites > Dashboard > Current Page        [User] [Store]  │
├────────┬──────────────────────────────────────────────────────────┤
│        │                                                           │
│  SIDE  │                   MAIN CONTENT AREA                      │
│  BAR   │                                                           │
│        │  • Fixed height containers                                │
│ (200px)│  • Internal scrolling only                                │
│        │  • Action buttons always visible                          │
│        │                                                           │
├────────┴──────────────────────────────────────────────────────────┤
│                        FOOTER (40px)                              │
│  © 2025 QPilot  |  v1.5.19  |  Privacy  |  Terms                 │
└──────────────────────────────────────────────────────────────────┘
```

---

## Visual Layout Patterns

### Pattern 1: List with Sidebar Filters (e.g., Scheduled Orders)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Sites > Dashboard > Scheduled Orders                    [Create] 🔲 │
├──┬────────────┬─────────────────────────────────────────────────────┤
│🔽│  FILTERS   │  126 Scheduled Orders                              │
├──┼────────────┼─────────────────────────────────────────────────────┤
│  │ 🔍 Search  │ ID ↕  Status  Customer     Next Date    Actions    │
│T │            ├─────────────────────────────────────────────────────┤
│O │ Status     │ #134  ACTIVE  john@...     Apr 17 2025  [···]      │
│G │ ☑ Active   │ #135  FAILED  mary@...     Apr 17 2025  [···]      │
│G │ ☑ Failed   │ #136  PAUSED  bob@...      May 16 2025  [···]      │
│L │ ☐ Paused   │ #137  ACTIVE  sara@...     May 17 2025  [···]      │
│E │            │ #138  DELETED alex@...     May 20 2025  [···]      │
│  │ Date Range │ #139  ACTIVE  tom@...      May 21 2025  [···]      │
│  │ [Apr 1-30] │                    ⋮                               │
│  │            │                                                     │
│  │ + More     │                                                     │
│  │            ├─────────────────────────────────────────────────────┤
│  │            │ [<] [1] 2 3 4 5 [>]  Showing 1-20 of 126           │
└──┴────────────┴─────────────────────────────────────────────────────┘
   ↑            ↑
Toggle      Filters                   Data Table
(48px)      (320px)                   (Flexible Width)
```

**Key Features:**
- Collapsible filter sidebar (320px expanded, 48px collapsed)
- Sticky pagination at bottom
- Compact data rows with hover states
- Inline actions via dropdown menus
- Real-time search and filtering

### Pattern 1A: Mobile Card View (< 768px)

**CRITICAL**: On mobile devices, tables transform into stacked cards. This is NOT optional - all data tables must implement this pattern.

```
MOBILE CARD LIST (<768px)
┌─────────────────────────────────────────────────────────────────┐
│  126 Scheduled Orders                        [+ Create]         │
├──────┬──────────────────────────────────────────────────────────┤
│      │                                                          │
│  T   │  ┌───────────────────────────────────────────────────┐   │
│  O   │  │ ▶  #1234                                      ⋮   │   │
│  G   │  │ ─────────────────────────────────────────────────│   │
│  G   │  │ NAME           John Doe                          │   │
│  L   │  │ STATUS         ┌────────┐                        │   │
│  E   │  │                │ ACTIVE │                        │   │
│      │  │                └────────┘                        │   │
│  48  │  │ EMAIL          john@example.com                  │   │
│  px  │  │ NEXT DATE      Apr 17, 2025                      │   │
│      │  │ FREQUENCY      30 Days                           │   │
│      │  └───────────────────────────────────────────────────┘   │
│      │                                                          │
│      │  ┌───────────────────────────────────────────────────┐   │
│      │  │ ▼  #1235                                      ⋮   │← Expanded
│      │  │ ─────────────────────────────────────────────────│   │
│      │  │ NAME           Jane Smith                        │   │
│      │  │ STATUS         ┌────────┐                        │   │
│      │  │                │ FAILED │                        │   │
│      │  │                └────────┘                        │   │
│      │  │ EMAIL          jane@example.com                  │   │
│      │  └─────────────────────────────────────────────────┬┘   │
│      │  ┌─────────────────────────────────────────────────┘    │← Connected
│      │  │  ┌──────────┐ ┌────────────┐ ┌──────────────┐   │    │  Expanded
│      │  │  │  Edit    │ │ View Logs  │ │ View Cycles  │   │    │  Row
│      │  │  └──────────┘ └────────────┘ └──────────────┘   │    │
│      │  │                                                  │    │
│      │  │  CUSTOMER INFO         PAYMENT INFO              │    │
│      │  │  First Name: Jane      Method: Stripe            │    │
│      │  │  Last Name: Smith      Currency: USD             │    │
│      │  └──────────────────────────────────────────────────┘    │
│      │                                                          │
│      │  ┌───────────────────────────────────────────────────┐   │
│      │  │ ▶  #1236                                      ⋮   │   │
│      │  │ ...                                              │   │
│      │  └───────────────────────────────────────────────────┘   │
├──────┴──────────────────────────────────────────────────────────┤
│  Showing 1 to 25 of 126                                         │
│  [<] [1] 2 3 [>]     [25 ▼]                                     │
└─────────────────────────────────────────────────────────────────┘
```

**Mobile Card Structure:**
```
CARD HEADER (First Line):
┌───────────────────────────────────────────────────────────────┐
│ ▶  #1234                                                   ⋮  │
│ ↑    ↑                                                     ↑  │
│ 24px ID (flex:1, font-weight:700)           Actions (absolute)│
└───────────────────────────────────────────────────────────────┘

DATA ROWS (Label: Value format):
┌───────────────────────────────────────────────────────────────┐
│ LABEL (85px)                                        VALUE     │
│ font-size: 0.75rem                        text-align: right   │
│ font-weight: 600                          overflow: ellipsis  │
│ color: #6b7280                            flex: 1             │
│ text-transform: uppercase                                     │
└───────────────────────────────────────────────────────────────┘

Card CSS:
- border: 1px solid #e5e7eb
- border-radius: 0.75rem (12px)
- padding: 0.5rem 0.75rem
- margin-bottom: 0.75rem
- box-shadow: 0 2px 4px rgba(0,0,0,0.08)
- background: white
```

**Expanded Card Connection:**
```
EXPANDED STATE - Card and expanded row connect visually:

┌───────────────────────────────────────────────────────────────┐
│ ▼  #1235                                                   ⋮  │ ← border-radius: top only
│ NAME           Jane Smith                                     │    margin-bottom: 0
│ STATUS         [FAILED]                                       │    border-bottom: none
│ EMAIL          jane@example.com                               │
└───────────────────────────────────────────────────────────────┤
┌───────────────────────────────────────────────────────────────┘ ← border-radius: bottom only
│  [Edit] [View Logs] [View Cycles]                             │    background: #f8fafc
│  ─────────────────────────────────                            │    border: 1px solid #e5e7eb
│  SECTION HEADER          SECTION HEADER                       │    border-top: none
│  Label: Value            Label: Value                         │
└───────────────────────────────────────────────────────────────┘
```

**Mobile Filter Sidebar (Overlay):**
```
┌──────┬─────────────────────────────────────────────────────────┐
│      │░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│
│  T   │░░  FILTERS                                     [Clear]░░│
│  O   │░░  ─────────────────────────────────────────────────  ░░│
│  G   │░░  🔍 Search...                                       ░░│
│  G   │░░  ▼ STATUS                                           ░░│
│  L   │░░  ☑ Active (89)                                      ░░│
│  E   │░░  ☑ Failed (12)                                      ░░│
│      │░░  ☐ Paused (15)                                      ░░│
│  48  │░░  ...                                                ░░│
│  px  │░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│
└──────┴─────────────────────────────────────────────────────────┘
   ↑              ↑                                            ↑
   Toggle       Sidebar (overlay)                         Backdrop
   (always      position: absolute                       rgba(0,0,0,0.5)
   visible)     left: 48px, width: auto                  click to close
                z-index: 1000
```

**Key Mobile Features:**
- Table headers hidden, replaced with inline labels per cell
- Each row becomes a rounded card with shadow
- Chevron + ID + Actions on first line
- Actions button absolutely positioned (top-right)
- Data fields stack vertically with label:value format
- Expanded row visually connects to card (no gap, matching borders)
- Filter sidebar overlays content with backdrop
- Ultra-compact pagination (hide first/last buttons)
- Font sizes: 0.75rem (12px) vs desktop 0.875rem (14px)

**Reference Implementation**: `src/app/features/scheduled-orders/components/scheduled-orders-table/scheduled-orders-table.component.scss`

### Pattern 1B: Mobile Card Detailed Structure (Current Implementation)

**This is the ACTUAL implementation pattern used in Scheduled Orders, Customers, and Quick Links tables:**

```
MOBILE CARD WITH STATUS BAR (Scheduled Orders Style)
┌────────────────────────────────────────────────────────────────────────────┐
│▌ MOBILE CARD                                                               │
│▌ width: calc(100% - 0.25rem)                                               │
│▌ margin: 0.25rem 0 0.25rem 0.25rem                                         │
│▌ padding: 0.625rem, padding-left: 0.875rem                                 │
│▌ border: 1px solid #e5e7eb, border-radius: 0.5rem                          │
│▌                                                                           │
│▌ ← 4px STATUS COLOR BAR (position: absolute, left: 0)                      │
│▌    • Active:     #22c55e → #16a34a (green gradient)                       │
│▌    • Failed:     #ef4444 → #dc2626 (red gradient)                         │
│▌    • Paused:     #f59e0b → #d97706 (yellow gradient)                      │
│▌    • Processing: #3b82f6 → #2563eb (blue gradient)                        │
│▌    • Queued:     #06b6d4 → #0891b2 (cyan gradient)                        │
│▌    • Deleted:    #6b7280 → #4b5563 (gray gradient)                        │
│▌                                                                           │
│▌  ┌──────────────────────────────────────────────────────────────────┐    │
│▌  │ HEADER (.mobile-card-header)                                      │    │
│▌  │ display: flex; gap: 0.25rem; margin-bottom: 0.5rem                │    │
│▌  │                                                                   │    │
│▌  │  LEFT          CENTER                        RIGHT                │    │
│▌  │  □             #123 [ACTIVE]                 [📋] [🕐]           │    │
│▌  │  checkbox      ID + status tag               quick icons          │    │
│▌  └──────────────────────────────────────────────────────────────────┘    │
│▌                                                                           │
│▌  ┌──────────────────────────────────────────────────────────────────┐    │
│▌  │ BODY (.mobile-card-body) - clickable [pRowToggler]="item"         │    │
│▌  │ padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6               │    │
│▌  │                                                                   │    │
│▌  │ .mobile-card-customer-name (font-weight: 600, 0.9375rem)          │    │
│▌  │ John Doe                                                          │    │
│▌  │                                                                   │    │
│▌  │ .mobile-card-customer-email (0.8125rem, #6b7280)                  │    │
│▌  │ john.doe@example.com                                              │    │
│▌  └──────────────────────────────────────────────────────────────────┘    │
│▌                                                                           │
│▌  ┌──────────────────────────────────────────────────────────────────┐    │
│▌  │ FOOTER (.mobile-card-footer) - clickable [pRowToggler]="item"     │    │
│▌  │ padding-top: 0.5rem                                               │    │
│▌  │                                                                   │    │
│▌  │ INFO ITEMS with vertical separators                               │    │
│▌  │ ┌────────┐│┌────────┐│┌────────┐│┌────────┐                      │    │
│▌  │ │ EVERY  ││ NEXT    ││ ITEMS   ││ VALUE   │                      │    │
│▌  │ │ 30 Days││ Dec 15  ││ 3       ││ $45.99  │                      │    │
│▌  │ └────────┘│└────────┘│└────────┘│└────────┘                      │    │
│▌  │           ↑ separator: 1px × 24px, #e5e7eb                        │    │
│▌  │                                                                   │    │
│▌  │ .mobile-card-info-label: 0.5625rem, #9ca3af, uppercase            │    │
│▌  │ .mobile-card-info-value: 0.75rem, #111827, font-weight: 600       │    │
│▌  └──────────────────────────────────────────────────────────────────┘    │
│▌                                                                           │
└────────────────────────────────────────────────────────────────────────────┘
```

**Expanded State Connection:**
```
┌────────────────────────────────────────────────────────────────────────────┐
│▌ CARD (expanded=true)                                                      │
│▌ border-radius: 0.5rem 0.5rem 0 0  (top corners only)                      │
│▌ margin-bottom: 0                   (no gap)                               │
│▌                                                                           │
│▌  [Header + Body + Footer as above]                                        │
│▌                                                                           │
└────────────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────────────────────────────────────────────────┐
│ EXPANDED ROW CONTENT                                                        │
│ border: 1px solid #e5e7eb, border-top: NONE (connects to card)             │
│ border-radius: 0 0 0.5rem 0.5rem (bottom corners only)                     │
│ background: white, padding: 0.625rem                                        │
│ margin-bottom: 1rem (spacing after)                                         │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ QUICK ACTIONS (horizontal scroll container)                          │   │
│  │ background: #f8fafc, border: 1px solid #e5e7eb                       │   │
│  │ border-radius: 0.5rem, padding: 0.5rem                               │   │
│  │ overflow-x: auto, -webkit-overflow-scrolling: touch                  │   │
│  │                                                                      │   │
│  │ [Process] [Skip] [Pause] [Edit] [History] [Cycles] →→→ scroll        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │ DETAIL SECTION                                                       │   │
│  │ .section-header: 0.625rem, uppercase, #374151, font-weight: 700      │   │
│  │ border-bottom: 1px solid #e5e7eb                                     │   │
│  │ CUSTOMER INFORMATION                                                 │   │
│  │ ────────────────────────────────────────────────────                 │   │
│  │ .detail-list: grid (auto 1fr)                                        │   │
│  │ First Name: John  (dt: 0.5625rem #6b7280, dd: 0.625rem #1f2937)      │   │
│  │ Last Name:  Doe                                                      │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘
```

**Tag Sizing (Ultra-Compact):**
```
Desktop Tags:
- font-size: 0.5625rem (9px)
- padding: 0.125rem 0.375rem
- font-weight: 600
- text-transform: uppercase
- letter-spacing: 0.025em

Mobile Card Header Tags:
- font-size: 0.625rem (10px)
- padding: 0.125rem 0.375rem
- font-weight: 700

Status Tag Colors:
┌──────────────┬─────────────┬────────────┬──────────┐
│ Status       │ Severity    │ Background │ Text     │
├──────────────┼─────────────┼────────────┼──────────┤
│ Active       │ success     │ #dcfce7    │ #166534  │
│ Paused       │ warn        │ #fef3c7    │ #92400e  │
│ Failed       │ danger      │ #fee2e2    │ #991b1b  │
│ Processing   │ info        │ #dbeafe    │ #1e40af  │
│ Queued       │ info        │ #dbeafe    │ #1e40af  │
│ Deleted      │ secondary   │ #f3f4f6    │ #374151  │
└──────────────┴─────────────┴────────────┴──────────┘
```

**Mobile Pagination (Ultra-Compact):**
```
┌─────────────────────────────────────────────────────────────────┐
│ Showing 1-25 of 245                     (order: -1, full width) │
│ ──────────────────────────────────────────────────────────────  │
│ [<] [1] [2] [3] [>]     [25▼]           (buttons: 1.5rem each)  │
│  ↑                         ↑                                    │
│  Hide first/last       10px font-size                           │
│  buttons on mobile                                              │
└─────────────────────────────────────────────────────────────────┘
```

### Pattern 2: Tabbed Settings Form (e.g., Customer Portal Settings)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Sites > Dashboard > Customer Portal Settings                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ [General] [Content] [Styling] [Advanced]                           │
│ ━━━━━━━━                                                            │
│                                                                      │
│ ┌───────────────────────────────────────────────────────────────┐  │
│ │ 📋 General Settings                                           │  │
│ │                                                                │  │
│ │ Customer Options                  Frequency Options           │  │
│ │ ┌─────────────────────────┐      ┌─────────────────────────┐ │  │
│ │ │ ☑ Update Order Notes    │      │ ☑ Manage Frequencies    │ │  │
│ │ │ ☑ Add/Remove Coupons    │      │                         │ │  │
│ │ │ ☑ Retry Failed Orders   │      │ Type: [Dropdown ▼]      │ │  │
│ │ │ ☐ Cancel Subscriptions  │      │ Value: 1, 2, 3          │ │  │
│ │ └─────────────────────────┘      │ [Add]                   │ │  │
│ │                                   │                         │ │  │
│ │                                   │ ⚫ 1 Days  ⚫ Monday    │ │  │
│ │                                   └─────────────────────────┘ │  │
│ └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│ ┌───────────────────────────────────────────────────────────────┐  │
│ │ ℹ️ Changes will be applied immediately                         │  │
│ │                              [Restore Defaults] [Update]      │  │
│ └───────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘

Key Elements:
• Tab navigation for logical grouping
• Two-column layout for related settings
• Sticky action bar at bottom
• Visual feedback for changes
```

### Pattern 3: Split-Pane Editor (e.g., Email Template Editor)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Sites > Dashboard > Edit Template                    [Preview] [💾] │
├────────────────────────────────┬────────────────────────────────────┤
│                                │                                    │
│ Edit Lock Notification         │         LIVE PREVIEW               │
│                                │                                    │
│ ▼ Basic Email Settings         │ ┌────────────────────────────────┐│
│   Subject: [Order Locked      ]│ │    Your order is locked!       ││
│   From: [noreply@qpilot.cloud]│ │                                ││
│                                │ │ Hello John,                    ││
│ ▼ Email Content                │ │                                ││
│   ┌──────────────────────────┐ │ │ Your order #61572 will be      ││
│   │ <p>Hello                 │ │ │ processed on Apr 17, 2025      ││
│   │ {{CustomerFirstName}},   │ │ │                                ││
│   │ </p>                     │ │ │ ┌────────────────────────┐    ││
│   │                          │ │ │ │ Order Summary          │    ││
│   │ <p>Your order            │ │ │ │ • 2-Row Malt     $30   │    ││
│   │ #{{OrderNumber}}         │ │ │ │ • Shipping       $10   │    ││
│   │ will be processed on     │ │ │ │ • Tax            TBD   │    ││
│   │ {{NextDate}}</p>         │ │ │ │ Total:           $40   │    ││
│   └──────────────────────────┘ │ │ └────────────────────────┘    ││
│                                │ │                                ││
│ ▼ Table Settings               │ │ [Update Order] [Contact Us]    ││
│   [Configure...]               │ └────────────────────────────────┘│
└────────────────────────────────┴────────────────────────────────────┘
         Editor (50%)                    Preview (50%)

Features:
• Real-time preview updates
• Collapsible settings sections
• Variable insertion helpers
• Responsive preview sizing
```

### Pattern 4: Accordion Form with Sidebar Navigation (e.g., Edit Scheduled Order)

**Reference Implementation**: `src/app/features/scheduled-orders/pages/edit/`

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│ ← Scheduled Order #143577  ACTIVE  ⋯                                                │
├──────┬──────────────────────────────────────────────────┬───────────────────────────┤
│      │                                                   │                           │
│  ✓   │  Payment Method                                  │  Order Total Summary      │
│ Sum. │  Select how this order will be charged           │                           │
│      │                                                   │  Order item subtotal      │
│  ✓   │  ▼ Select Payment Method                 REQUIRED│                    $9.00  │
│ Cust │  ┌────────────────────────────────────────────┐ │  Order coupon discount    │
│      │  │ Currency *                                  │ │  (Coupon: a)       -$0.90 │
│  ✓   │  │ USD - US Dollar                        ▼   │ │                           │
│ Prod │  │                                              │ │  Order subtotal           │
│      │  │ Select Payment Method *                     │ │                    $8.10  │
│  ✓   │  │ Stripe                                      │ │                           │
│ Sche │  │ Visa ending in 4242 (expires 02/...)    ▼   │ │  SAT. DAL VEH - (TX) DAL  │
│      │  └────────────────────────────────────────────┘ │  VEH.2D            $13.99 │
│  ▼   │                                                   │                           │
│ Paym │  ▼ Load Methods from Gateway                     │  Shipping total           │
│      │  ┌────────────────────────────────────────────┐ │                   $13.99  │
│  5   │  │ Gateway Customer Profile                    │ │                           │
│ Ship │  │ Select gateway customer profile        ▼   │ │  Tax total                │
│      │  └────────────────────────────────────────────┘ │                       -   │
│  6   │                                                   │                           │
│Coupo│                                                   │  Order total              │
│      │                                                   │                   $22.09  │
│  7   │                                                   │                           │
│Valid │                                                   │  ✅ Customer is saving    │
│      │                                                   │     $0.90 on this order!  │
│      │                                                   │                           │
│ ←    │                                                   │                           │
│ Back │                                                   │                           │
│      │                                                   │                           │
│ 💾   │                                                   │                           │
│ Save │                                                   │                           │
└──────┴──────────────────────────────────────────────────┴───────────────────────────┘
(200px)               Content Area (Flex)                        Summary (280px)

Sidebar Elements:
✓ = Completed section (green checkmark)
▼ = Active/expanded section (blue indicator)
Numbers = Step order (non-interactive)

Key Features:
- Collapsible accordion sections (only one open at a time)
- Non-linear navigation (jump to any section)
- Real-time validation and total calculation
- Sticky sidebar and summary on scroll
- Persistent "Back" and "Save Changes" buttons at bottom
```

### Pattern 5: Card Grid Layout (e.g., Email Templates List)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Sites > Dashboard > Email Templates                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ [General Settings] [Upcoming Orders] [Lock Notifications] [+More]   │
│                                                                      │
│ ┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────┐│
│ │  📧 General         │ │  📦 Upcoming Orders │ │  🔒 Lock Notice  ││
│ │                     │ │                     │ │                   ││
│ │  Configure sender   │ │  Notify customers  │ │  Alert when order ││
│ │  address and style  │ │  about upcoming     │ │  is locked        ││
│ │                     │ │  orders             │ │                   ││
│ │  [Edit Settings]    │ │  [Edit Template]    │ │  [Edit Template]  ││
│ └─────────────────────┘ └─────────────────────┘ └─────────────────┘│
│                                                                      │
│ ┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────┐│
│ │  ⚠️ Validation Err  │ │  ✅ Status Updates  │ │  ❌ Order Errors ││
│ │                     │ │                     │ │                   ││
│ │  Alert on payment   │ │  Subscription       │ │  Failed order     ││
│ │  validation issues  │ │  status changes     │ │  notifications    ││
│ │                     │ │                     │ │                   ││
│ │  [Edit Template]    │ │  [Edit Template]    │ │  [Edit Template]  ││
│ └─────────────────────┘ └─────────────────────┘ └─────────────────┘│
│                                                                      │
│ ┌─────────────────────────────────────────────────────────────────┐│
│ │ ℹ️ Changes will be applied immediately        [Update Settings] ││
│ └─────────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────┘
```

---

## Component Catalog

### Navigation Components

| Component | Purpose | Visual Example |
|-----------|---------|----------------|
| **Breadcrumb** | Shows location hierarchy | `Home > Sites > Dashboard > Orders` |
| **Sidebar Menu** | Primary navigation | Collapsible accordion with icons |
| **Tab Navigation** | Section switching | Horizontal tabs with active indicator |
| **Stepper** | Multi-step process | Numbered circles with progress line |

### Data Display Components

| Component | Purpose | Key Features |
|-----------|---------|--------------|
| **Data Table** | List large datasets | • Sortable columns<br>• Inline actions<br>• Sticky pagination<br>• Compact rows |
| **Status Badge** | Show state | Color-coded tags (Active=Green, Failed=Red, Paused=Yellow) |
| **Summary Card** | Key metrics | Icon + Label + Value format |
| **Info Panel** | Detailed view | Collapsible sections with metadata |

### Form Components

| Component | Usage | Behavior |
|-----------|-------|----------|
| **Text Input** | Single-line text | Border highlight on focus |
| **Select Dropdown** | Choice from list | Searchable with icons |
| **Checkbox Group** | Multiple selections | Indeterminate state support |
| **Date Picker** | Date selection | Range selection, presets |
| **Action Buttons** | Form submission | Primary (blue), Secondary (gray), Danger (red) |

### Feedback Components

| Component | When to Use | Duration |
|-----------|-------------|----------|
| **Toast** | Success/Error messages | 3-5 seconds auto-dismiss |
| **Loading Spinner** | Data fetching | Until complete |
| **Skeleton Loader** | Initial load | Replaces with real content |
| **Table Skeleton** | Table loading state | Use `TableSkeletonRowsComponent` |
| **Confirmation Dialog** | Destructive actions | Modal with Yes/No |

---

## User Experience Principles

### 1. Progressive Disclosure
Start with essential information, reveal complexity on demand:
- Basic filters visible, advanced behind "More options"
- Summary view by default, details on expansion
- Core actions prominent, secondary in menus

### 2. Consistent Interaction Patterns
Users should predict behavior:
- All tables sort the same way (click header)
- All forms save the same way (bottom-right button)
- All modals close the same way (X or Escape)

### 3. Clear Visual Hierarchy
Guide the eye naturally:
- Page title → Primary action → Content → Secondary actions
- Use size, color, and spacing to show importance
- Group related items visually

### 4. Immediate Feedback
Every action has a response:
- Hover states on interactive elements
- Loading states during processing
- Success/error messages after actions
- Form validation in real-time

### 5. Mobile-First Responsive
Design for small screens, enhance for larger:
- Stack columns on mobile
- Collapse sidebars to toggles
- Simplify tables (hide columns)
- Touch-friendly tap targets (44px minimum)

---

## How to Request New Features

### Feature Request Template

When requesting new features, provide a comprehensive specification following this template. This ensures developers have all necessary information to implement efficiently.

---

### 1. Executive Summary

**Feature Name**: [Clear, descriptive name]
**Version**: 1.0
**Date**: [Request date]
**Priority**: Critical | High | Medium | Low
**Estimated Scope**: Small (1-3 days) | Medium (1 week) | Large (2+ weeks)

**Goal**: [One paragraph describing what this feature accomplishes]

**Use Cases**:
1. [Primary use case with specific example]
2. [Secondary use case]
3. [Additional use cases if applicable]

---

### 2. User Interface Design (ASCII Mockup)

#### List/Table View Example
```
┌─────────────────────────────────────────────────────────────────────┐
│ Sites > Dashboard > [Feature Name]                    [+ Create New]│
├──┬────────────┬─────────────────────────────────────────────────────┤
│🔽│  FILTERS   │  126 Items Found                                   │
├──┼────────────┼─────────────────────────────────────────────────────┤
│  │ 🔍 Search  │ Col1 ↕  Col2    Col3        Col4       Actions     │
│T │            ├─────────────────────────────────────────────────────┤
│O │ Status     │ #134   ACTIVE   Value1      Apr 17     [···]       │
│G │ ☑ Active   │ #135   PENDING  Value2      Apr 18     [···]       │
│G │ ☑ Pending  │ #136   FAILED   Value3      Apr 19     [···]       │
│L │ ☐ Failed   │                                                     │
│E │            │                                                     │
│  │ Date Range │                                                     │
│  │ [Apr 1-30] │                                                     │
│  │            ├─────────────────────────────────────────────────────┤
│  │ + More     │ [<] [1] 2 3 4 5 [>]  Showing 1-20 of 126           │
└──┴────────────┴─────────────────────────────────────────────────────┘

Dimensions:
- Toggle: 48px
- Filters: 320px expanded
- Actions dropdown content: Edit | Duplicate | Delete | Export
```

#### Form/Modal View Example
```
┌─────────────────────────────────────────────────────────────────────┐
│ Create [Feature Name]                                         [X]    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│ Section 1: Basic Information                                         │
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ Field Label *                                                  │   │
│ │ [_________________________________________________]            │   │
│ │ Helper text explaining the field                               │   │
│ │                                                                │   │
│ │ Dropdown Field *                                               │   │
│ │ [Select Option ▼]                                              │   │
│ │                                                                │   │
│ │ ☐ Checkbox Option 1                                            │   │
│ │ ☑ Checkbox Option 2                                            │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                       │
│ Section 2: Advanced Settings                                         │
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ ◉ Radio Option 1                                               │   │
│ │   Description of what this option does                         │   │
│ │                                                                │   │
│ │ ○ Radio Option 2                                               │   │
│ │   Description of what this option does                         │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                       │
│                                      [Cancel]  [Save & Continue]     │
└─────────────────────────────────────────────────────────────────────┘

States to show:
- Loading: Skeleton loaders
- Empty: "No items found" message
- Error: Red banner with error message
- Success: Green toast notification
```

---

### 3. Data Model & API Specifications

#### Data Model
```typescript
// Request interfaces
export interface CreateFeatureRequest {
  name: string;           // Required, max 255 chars
  description?: string;   // Optional, max 1000 chars
  status: 'active' | 'inactive' | 'pending';
  metadata?: {
    [key: string]: any;
  };
  settings: {
    option1: boolean;
    option2: number;    // Range: 1-100
    option3: string[];  // Max 10 items
  };
}

// Response interfaces
export interface FeatureResponse {
  id: number;
  siteId: string;
  name: string;
  description: string | null;
  status: 'active' | 'inactive' | 'pending';
  createdAt: string;  // ISO 8601
  updatedAt: string;  // ISO 8601
  metadata: Record<string, any>;
  settings: FeatureSettings;
  _links: {
    self: string;
    update: string;
    delete: string;
  };
}

export interface FeatureListResponse {
  data: FeatureResponse[];
  pagination: {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    pageSize: number;
  };
}
```

#### API Endpoints

**Base URL**: `https://api.qpilot.com/api/v1`

##### 1. List Features
```http
GET /Sites/{siteId}/Features

Query Parameters:
- page (number, default: 1): Page number
- pageSize (number, default: 25): Items per page
- status (string): Filter by status
- search (string): Search term
- sortBy (string): Field to sort by
- sortOrder ('asc' | 'desc'): Sort direction

Response 200 OK:
{
  "data": [
    {
      "id": 123,
      "siteId": "shop-123",
      "name": "Feature Name",
      "description": "Feature description",
      "status": "active",
      "createdAt": "2025-01-19T10:00:00Z",
      "updatedAt": "2025-01-19T10:00:00Z",
      "metadata": {
        "customField": "value"
      },
      "settings": {
        "option1": true,
        "option2": 50,
        "option3": ["value1", "value2"]
      },
      "_links": {
        "self": "/api/v1/Sites/shop-123/Features/123",
        "update": "/api/v1/Sites/shop-123/Features/123",
        "delete": "/api/v1/Sites/shop-123/Features/123"
      }
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 5,
    "totalItems": 123,
    "pageSize": 25
  }
}

Error Response 400:
{
  "error": "INVALID_PARAMETERS",
  "message": "Invalid query parameters",
  "details": {
    "pageSize": "Must be between 1 and 100"
  }
}
```

##### 2. Create Feature
```http
POST /Sites/{siteId}/Features

Request Headers:
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
  "name": "New Feature",
  "description": "Description of the feature",
  "status": "active",
  "metadata": {
    "source": "manual",
    "priority": "high"
  },
  "settings": {
    "option1": true,
    "option2": 75,
    "option3": ["email", "sms"]
  }
}

Response 201 Created:
{
  "id": 124,
  "siteId": "shop-123",
  "name": "New Feature",
  "description": "Description of the feature",
  "status": "active",
  "createdAt": "2025-01-19T10:30:00Z",
  "updatedAt": "2025-01-19T10:30:00Z",
  "metadata": {
    "source": "manual",
    "priority": "high"
  },
  "settings": {
    "option1": true,
    "option2": 75,
    "option3": ["email", "sms"]
  },
  "_links": {
    "self": "/api/v1/Sites/shop-123/Features/124",
    "update": "/api/v1/Sites/shop-123/Features/124",
    "delete": "/api/v1/Sites/shop-123/Features/124"
  }
}

Validation Errors 422:
{
  "error": "VALIDATION_FAILED",
  "message": "Request validation failed",
  "details": {
    "name": ["Name is required", "Name must be unique"],
    "settings.option2": ["Value must be between 1 and 100"]
  }
}
```

##### 3. Update Feature
```http
PUT /Sites/{siteId}/Features/{id}

Request Body: (same structure as POST)

Response 200 OK: (returns updated object)
```

##### 4. Delete Feature
```http
DELETE /Sites/{siteId}/Features/{id}

Response 204 No Content

Error 409 Conflict:
{
  "error": "CANNOT_DELETE",
  "message": "Feature has active dependencies",
  "details": {
    "dependencies": ["Order #1234", "Customer #5678"]
  }
}
```


---

# Part II: Developer Implementation Guide

## Technical Architecture

### Technology Stack
- **Framework**: Angular 19 (Standalone Components)
- **UI Library**: PrimeNG with QPilotBrandBridgePreset
- **Styling**: SCSS + Tailwind CSS utility classes
- **State**: Service-based with RxJS Observables
- **Forms**: Strongly-typed Reactive Forms

### File Structure
```
src/app/
├── core/              # Shared services, models
├── features/          # Feature modules
│   ├── scheduled-orders/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   └── interfaces/
│   └── settings/
├── shared/           # Reusable components
└── my-theme.ts       # PrimeNG theme config
```

---

## Layout Implementation Patterns

### Pattern: Fixed Height Container with Internal Scroll

**CRITICAL**: This is the foundation of all QMC layouts. NO page-level scrolling allowed.

#### HTML Structure
```html
<div class="page-container">
  <!-- Fixed Header -->
  <div class="page-header">
    <h1>{{ title }}</h1>
    <p-button label="Primary Action" />
  </div>

  <!-- Scrollable Content -->
  <div class="content-section">
    <p-table
      [value]="data"
      [scrollable]="true"
      scrollHeight="flex"
      [paginator]="true">
      <!-- Table content -->
    </p-table>
  </div>

  <!-- Fixed Footer Actions -->
  <div class="actions-footer">
    <p-button label="Cancel" severity="secondary" />
    <p-button label="Save" />
  </div>
</div>
```

#### SCSS Implementation
```scss
// CRITICAL: Every component needs this base pattern
:host {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  overflow: hidden;
  min-height: 0; // CRITICAL for flexbox
}

.page-container {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 200px); // Account for app header/footer
  max-height: calc(100vh - 200px);
  min-height: 0; // CRITICAL
  overflow: hidden;
}

.page-header {
  flex-shrink: 0; // Never shrink
  padding: 1rem;
  background: var(--surface-section);
  border-bottom: 1px solid var(--surface-border);

  display: flex;
  justify-content: space-between;
  align-items: center;
}

.content-section {
  flex: 1; // Take remaining space
  overflow-y: auto;
  overflow-x: hidden;
  min-height: 0; // CRITICAL for flex children
  padding: 1rem;
}

.actions-footer {
  flex-shrink: 0;
  padding: 1rem;
  background: white;
  border-top: 1px solid var(--surface-border);
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);

  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;

  // Keep visible with sticky
  position: sticky;
  bottom: 0;
  z-index: 10;
}
```

### Pattern: Sidebar Filter with Table

**Reference**: `src/app/features/scheduled-orders/pages/scheduled-orders-list/`

**⚠️ MANDATORY: Auto-Collapse/Expand Based on Screen Size**

All sidebar filter components MUST implement responsive sidebar behavior:
- **Mobile (≤768px)**: Auto-collapse sidebar
- **Desktop (>768px)**: Auto-expand sidebar

See `CLAUDE_PATTERNS.md` for the complete implementation pattern.

```typescript
// Component must implement:
private initializeResponsiveSidebar(): void {
  const isMobile = window.innerWidth <= 768;
  this.sidebarCollapsed.set(isMobile);
}

private setupWindowResizeListener(): void {
  const resizeHandler = () => {
    const isMobile = window.innerWidth <= 768;
    if (isMobile && !this.userManuallyCollapsed) {
      this.sidebarCollapsed.set(true);
    } else if (!isMobile && !this.userManuallyCollapsed) {
      this.sidebarCollapsed.set(false);
    }
    this.cdr.markForCheck();
  };
  // ... listener setup ...
}
```

#### HTML Structure
```html
<div class="main-layout">
  <!-- Toggle Area (48px) -->
  <div class="sidebar-toggle-area">
    <p-button
      icon="pi pi-filter"
      [text]="true"
      size="small"
      (onClick)="toggleSidebar()" />
  </div>

  <!-- Filter Sidebar (320px expanded) -->
  <div class="filter-sidebar" [class.collapsed]="sidebarCollapsed()">
    <div class="filters-header-main">
      <h2 class="filters-title">Filters</h2>
      <button
        class="clear-all-btn"
        [class.has-filters]="hasActiveFilters()"
        (click)="clearFilters()">
        Clear All
      </button>
    </div>

    <div class="filters-content-scroll">
      <app-filters [formGroup]="filterForm" />
    </div>
  </div>

  <!-- Content Area -->
  <div class="content-area">
    <div class="content-toolbar">
      <span>{{ totalRecords }} Records</span>
      <p-button label="Create New" size="small" />
    </div>

    <div class="table-container">
      <app-data-table [data]="data()" />
    </div>
  </div>
</div>
```

#### SCSS for Sidebar Layout
```scss
.main-layout {
  display: flex;
  flex: 1;
  min-height: 0;
  overflow: hidden;
  position: relative;
}

.sidebar-toggle-area {
  width: 48px;
  flex-shrink: 0;
  background: var(--surface-section);
  padding: 0.5rem 0;
  border-right: 1px solid rgba(226, 232, 240, 0.4);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.filter-sidebar {
  width: 320px;
  background: var(--surface-section);
  display: flex;
  flex-direction: column;
  height: 100%;
  flex-shrink: 0;
  overflow: hidden;
  border-right: 1px solid rgba(226, 232, 240, 0.7);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);

  &.collapsed {
    width: 0;
    opacity: 0;
    border-right: none;
  }

  // MOBILE: Use width: auto to prevent overflow on small screens
  @media (max-width: 768px) {
    position: absolute;
    top: 0;
    left: 48px;
    bottom: 0;
    z-index: 1000;
    width: auto; // CRITICAL: Use auto, not fixed width
    right: 0; // Fills remaining space
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: left 0.3s ease; // Sync with backdrop animation

    &.collapsed {
      left: -100%;
      width: auto;
      opacity: 1; // Keep visible during slide
    }
  }

  .filters-header-main {
    flex-shrink: 0;
    padding: 1rem;
    background: white;
    border-bottom: 1px solid #f1f5f9;

    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .filters-content-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;

    // Custom scrollbar
    &::-webkit-scrollbar {
      width: 4px;
    }

    &::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 2px;

      &:hover {
        background: #9ca3af;
      }
    }
  }
}

.content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}

.content-toolbar {
  flex-shrink: 0;
  padding: 0.75rem 1rem;
  background: var(--surface-section);
  border-bottom: 1px solid var(--surface-border);

  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-container {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
```

### Pattern: Ultra-Compact Table Styling

**MANDATORY**: Copy this exact pattern for ALL tables

```scss
// ULTRA AGGRESSIVE FONT SIZE OVERRIDE - Must be at root level
:host ::ng-deep {
  .p-datatable-thead > tr > th {
    font-size: 0.875rem !important; // 14px
    padding: 0.25rem 0.3rem !important;
    font-weight: 600 !important;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;

    &:first-child {
      padding-left: 0.4rem;
    }

    &:last-child {
      padding-right: 0.4rem;
    }
  }

  .p-datatable-tbody > tr > td {
    font-size: 0.875rem !important; // 14px
    padding: 0.2rem 0.3rem !important;
    vertical-align: middle;
    line-height: 1.1;
    border-bottom: 1px solid #f3f4f6;

    * {
      font-size: 0.875rem !important; // ALL children
    }

    &:first-child {
      padding-left: 0.4rem;
    }

    &:last-child {
      padding-right: 0.4rem;
    }
  }

  // Mobile responsiveness
  @media (max-width: 768px) {
    .p-datatable-thead > tr > th,
    .p-datatable-tbody > tr > td,
    .p-datatable-tbody > tr > td * {
      font-size: 0.75rem !important; // 12px
    }
  }

  // Row interactions
  .p-datatable-tbody > tr {
    transition: background-color 0.15s ease;

    &:hover {
      background-color: #f9fafb;
    }

    &:last-child > td {
      border-bottom: none;
    }
  }

  // Override PrimeNG constraints
  .p-datatable-table-container,
  .p-datatable-scrollable-table-wrapper {
    max-height: none !important;
    height: 100% !important;
  }

  // Sticky pagination
  .p-paginator {
    flex-shrink: 0;
    position: sticky;
    bottom: 0;
    background: white;
    border-top: 1px solid var(--surface-border);
    z-index: 9;
  }
}
```

### Pattern: Mobile Card Layout Implementation

**CRITICAL**: Tables must transform to cards on mobile (<768px). Use `responsiveLayout="stack"` and `breakpoint="768px"`.

#### p-table Configuration
```html
<p-table
  [value]="data?.Items || []"
  [loading]="loading"
  [paginator]="true"
  [rows]="25"
  [totalRecords]="data?.TotalRecords || 0"
  [lazy]="true"
  [scrollHeight]="scrollHeight"
  [scrollable]="true"
  scrollDirection="both"
  size="small"
  responsiveLayout="stack"          <!-- CRITICAL: enables stack mode -->
  breakpoint="768px"                <!-- CRITICAL: card breakpoint -->
  dataKey="Id"
  [rowExpandMode]="'single'"
  [expandedRowKeys]="expandedRows"
  (onRowExpand)="onRowExpand($event)"
  (onRowCollapse)="onRowCollapse($event)"
>
```

#### Mobile Card SCSS Pattern
```scss
// MOBILE CARD LAYOUT - Copy this pattern
@media (max-width: 768px) {
  :host ::ng-deep {
    // Hide table header on mobile
    .p-datatable-thead {
      display: none !important;
    }

    // Prevent horizontal overflow
    .p-datatable-wrapper,
    .p-datatable-scrollable-wrapper,
    .p-datatable-scrollable-body {
      max-width: 100% !important;
      overflow-x: hidden !important;
      box-sizing: border-box !important;
    }

    .p-datatable-table {
      width: 100% !important;
      max-width: 100% !important;
    }

    // Transform rows to cards
    .p-datatable-tbody > tr {
      display: block !important;
      width: 100% !important;

      > td {
        display: block !important;
        padding: 0 !important;
        border: none !important;
      }
    }

    // Ultra-compact mobile pagination
    // CRITICAL: Use flex-start to align pagination left, avoiding overlap with chat widgets
    .p-paginator {
      padding: 0.25rem 0.5rem !important;
      gap: 0.125rem !important;
      flex-wrap: wrap !important;
      justify-content: flex-start !important; // REQUIRED: Prevents conflict with floating chat buttons
      font-size: 0.6875rem !important;

      .p-paginator-current {
        font-size: 0.625rem !important;
        order: -1;
        width: 100%;
        margin-bottom: 0.25rem !important;
        color: #6b7280;
      }

      .p-paginator-first,
      .p-paginator-last {
        display: none !important;
      }

      .p-paginator-prev,
      .p-paginator-next,
      .p-paginator-page {
        min-width: 1.5rem !important;
        width: 1.5rem !important;
        height: 1.5rem !important;
        font-size: 0.625rem !important;
      }

      .p-select {
        height: 1.5rem !important;
        font-size: 0.625rem !important;
        min-width: 3rem !important;
      }
    }
  }
}

// Mobile card styling
.mobile-card {
  position: relative;
  width: calc(100% - 0.25rem);
  max-width: calc(100% - 0.25rem);
  margin: 0.25rem 0 0.25rem 0.25rem;
  padding: 0.625rem;
  padding-left: 0.875rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-sizing: border-box;
  overflow: hidden;

  // Status color bar
  &::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 0.5rem 0 0 0.5rem;
  }

  // Status gradients
  &.status-active::before {
    background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
  }
  &.status-failed::before {
    background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
  }
  &.status-paused::before {
    background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
  }
  &.status-processing::before {
    background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
  }
  &.status-queued::before {
    background: linear-gradient(180deg, #06b6d4 0%, #0891b2 100%);
  }
  &.status-deleted::before {
    background: linear-gradient(180deg, #6b7280 0%, #4b5563 100%);
  }

  // Expanded state - connect to expanded row
  &.expanded {
    border-radius: 0.5rem 0.5rem 0 0;
    margin-bottom: 0;

    &::before {
      border-radius: 0.5rem 0 0 0;
    }
  }
}

// Mobile card sections
.mobile-card-header {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-bottom: 0.5rem;

  .mobile-card-header-left {
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }

  .mobile-card-header-center {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex: 1;
  }

  .mobile-card-header-right {
    display: flex;
    align-items: center;
    gap: 0.125rem;
  }
}

.mobile-card-body {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;

  .mobile-card-customer-name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: #111827;
    margin-bottom: 0.125rem;
  }

  .mobile-card-customer-email {
    font-size: 0.8125rem;
    color: #6b7280;
  }
}

.mobile-card-footer {
  padding-top: 0.5rem;
  cursor: pointer;

  .mobile-card-info-inline {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: stretch;
  }

  .mobile-card-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.0625rem;
    min-width: 0;
  }

  .mobile-card-separator {
    width: 1px;
    height: 24px;
    background-color: #e5e7eb;
    flex-shrink: 0;
    align-self: center;
  }

  .mobile-card-info-label {
    font-size: 0.5625rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.025em;
  }

  .mobile-card-info-value {
    font-size: 0.75rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}

// Expanded row content (mobile)
.expanded-row-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
  padding: 0.625rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-top: none;
  border-radius: 0 0 0.5rem 0.5rem;
  margin-bottom: 1rem;

  // Quick actions scroll container
  .quick-actions-section {
    display: flex;
    gap: 0.375rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 0.5rem;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;

    &::-webkit-scrollbar {
      height: 4px;
    }
  }

  .detail-section {
    .section-header {
      font-size: 0.625rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #374151;
      margin-bottom: 0.25rem;
      padding-bottom: 0.25rem;
      border-bottom: 1px solid #e5e7eb;
    }

    .detail-list {
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 0.125rem 0.375rem;

      dt {
        font-size: 0.5625rem;
        font-weight: 600;
        color: #6b7280;
        &::after { content: ":"; }
      }

      dd {
        font-size: 0.625rem;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
      }
    }
  }
}
```

---

## Component Development Guide

### Creating a New List Page

```typescript
// scheduled-orders-list.component.ts
@Component({
  selector: 'app-scheduled-orders-list',
  standalone: true,
  imports: [
    CommonModule,
    TableModule,
    ButtonModule,
    ScheduledOrdersFiltersComponent,
    ScheduledOrdersTableComponent
  ],
  templateUrl: './scheduled-orders-list.component.html',
  styleUrls: ['./scheduled-orders-list.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScheduledOrdersListComponent implements OnInit {
  // Signals for reactive state
  sidebarCollapsed = signal(false);
  data = signal<OrderData | null>(null);
  loading = signal(true);

  // Form for filters
  filterForm: FormGroup<FilterForm>;

  constructor(
    private formBuilder: FormBuilder,
    private apiService: ApiService
  ) {
    this.filterForm = this.createFilterForm();
  }

  ngOnInit(): void {
    this.setupFilterSubscription();
    this.loadData();
  }

  private createFilterForm(): FormGroup<FilterForm> {
    return this.formBuilder.nonNullable.group({
      search: [null as string | null],
      status: [[] as string[]],
      dateRange: [null as DateRange | null]
    });
  }

  private setupFilterSubscription(): void {
    this.filterForm.valueChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged()
      )
      .subscribe(() => this.loadData());
  }

  toggleSidebar(): void {
    this.sidebarCollapsed.update(v => !v);
  }

  hasActiveFilters(): boolean {
    const values = this.filterForm.value;
    return !!(
      values.search?.trim() ||
      (values.status && values.status.length > 0) ||
      values.dateRange
    );
  }

  clearFilters(): void {
    this.filterForm.reset();
  }
}
```

### Creating a Settings Form

```typescript
// customer-portal-settings.component.ts
interface CustomerPortalForm {
  general: FormGroup<GeneralSettings>;
  content: FormGroup<ContentSettings>;
  styling: FormGroup<StylingSettings>;
}

@Component({
  selector: 'app-customer-portal-settings',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TabsModule,
    GeneralSettingsComponent,
    ContentManagementComponent,
    StylingSettingsComponent
  ],
  template: `
    <div class="settings-container">
      <p-tabs [(activeIndex)]="activeTab">
        <p-tab-list>
          <p-tab [value]="0">General Settings</p-tab>
          <p-tab [value]="1">Content Management</p-tab>
          <p-tab [value]="2">Styling Settings</p-tab>
        </p-tab-list>

        <p-tab-panels>
          <p-tab-panel [value]="0">
            <app-general-settings [formGroup]="formGroup" />
          </p-tab-panel>

          <p-tab-panel [value]="1">
            <app-content-management [formGroup]="formGroup" />
          </p-tab-panel>

          <p-tab-panel [value]="2">
            <app-styling-settings [formGroup]="formGroup" />
          </p-tab-panel>
        </p-tab-panels>
      </p-tabs>

      <div class="actions-container">
        <div class="actions-card">
          <div class="changes-indicator">
            <i class="pi pi-info-circle"></i>
            <span>Changes will be applied immediately</span>
          </div>
          <div class="actions-buttons">
            <p-button
              label="Restore Defaults"
              severity="secondary"
              [outlined]="true"
              (onClick)="restoreDefaults()" />
            <p-button
              label="Update"
              [disabled]="formGroup.invalid || !isFormDirty()"
              (onClick)="save()" />
          </div>
        </div>
      </div>
    </div>
  `
})
export class CustomerPortalSettingsComponent {
  formGroup: FormGroup<CustomerPortalForm>;
  activeTab = signal(0);
  private originalData?: CustomerPortalData;

  ngOnInit(): void {
    this.initializeForm();
    this.loadData();
  }

  private initializeForm(): void {
    this.formGroup = this.formBuilder.nonNullable.group({
      general: this.createGeneralForm(),
      content: this.createContentForm(),
      styling: this.createStylingForm()
    });
  }

  isFormDirty(): boolean {
    // Compare with original data
    return JSON.stringify(this.formGroup.value) !==
           JSON.stringify(this.originalData);
  }
}
```

---

## Theme & Styling System

### QPilot Brand Colors

```typescript
// my-theme.ts
export const QPilotBrandBridgePreset = definePreset(Aura, {
  primitive: {
    // Primary - QPilot Blue
    blue: {
      50: '#eff6ff',
      100: '#dbeafe',
      300: '#93c5fd',
      400: '#60a5fa',
      500: '#3b82f6', // Base
      600: '#2563eb', // Primary actions
      700: '#1d4ed8',
      800: '#1e40af',
      900: '#1e3a8a'
    },

    // Secondary - Autoship Purple
    violet: {
      50: '#f5f3ff',
      100: '#ede9fe',
      300: '#c4b5fd',
      400: '#a78bfa',
      500: '#8b5cf6', // Base
      600: '#7c3aed', // Secondary actions
      700: '#6d28d9',
      800: '#5b21b6',
      900: '#4c1d95'
    },

    // Status Colors
    green: { 600: '#059669' }, // Success
    amber: { 600: '#d97706' }, // Warning
    red: { 600: '#dc2626' },   // Danger
    sky: { 600: '#0284c7' }    // Info
  }
});
```

### Component-Specific Styling

```scss
// Use CSS custom properties for theming
.status-badge {
  &.active {
    background: var(--green-600);
    color: white;
  }

  &.paused {
    background: var(--amber-600);
    color: white;
  }

  &.failed {
    background: var(--red-600);
    color: white;
  }
}

// Utility classes (Tailwind-inspired)
.flex { display: flex; }
.flex-1 { flex: 1; }
.flex-column { flex-direction: column; }
.gap-2 { gap: 0.5rem; }
.p-3 { padding: 0.75rem; }
.mb-3 { margin-bottom: 0.75rem; }
```

---

## Best Practices & Code Examples

### 1. NEVER Use Page-Level Scrolling

```scss
// ❌ WRONG - Allows page scroll
.container {
  height: auto; // NO!
  overflow: visible; // NO!
}

// ✅ CORRECT - Fixed height with internal scroll
.container {
  height: calc(100vh - 200px);
  overflow: hidden;
  display: flex;
  flex-direction: column;

  .content {
    flex: 1;
    overflow-y: auto;
    min-height: 0; // CRITICAL
  }
}
```

### 2. ALWAYS Use Strongly-Typed Forms

```typescript
// ❌ WRONG - No type safety
formGroup: FormGroup; // NO!

// ✅ CORRECT - Full type safety
interface MyForm {
  email: FormControl<string>;
  status: FormControl<'active' | 'inactive'>;
  items: FormArray<FormGroup<ItemForm>>;
}

formGroup: FormGroup<MyForm>;
```

### 3. Use Signals for UI State

```typescript
// ✅ Modern Angular 19 pattern
export class MyComponent {
  // UI state with signals
  isLoading = signal(false);
  sidebarOpen = signal(true);
  selectedItem = signal<Item | null>(null);

  // Computed values
  hasSelection = computed(() => this.selectedItem() !== null);

  // Update state
  toggleSidebar(): void {
    this.sidebarOpen.update(open => !open);
  }
}
```

### 4. ALWAYS Use Modern Control Flow Syntax

**🚫 NEVER use `*ngIf`, `*ngFor`, `*ngSwitch` - These are DEPRECATED**

**✅ ALWAYS use `@if`, `@for`, `@switch` - Up to 90% faster rendering**

```html
<!-- ❌ WRONG: Old structural directives (DEPRECATED) -->
<div *ngIf="user">{{ user.name }}</div>
<div *ngFor="let item of items; trackBy: trackByFn">{{ item.name }}</div>
<div [ngSwitch]="status">
  <span *ngSwitchCase="'active'">Active</span>
</div>

<!-- ✅ CORRECT: New control flow blocks -->
@if (user) {
  <div>{{ user.name }}</div>
}

@for (item of items; track item.id) {
  <div>{{ item.name }}</div>
} @empty {
  <div>No items found</div>
}

@switch (status) {
  @case ('active') {
    <span>Active</span>
  }
  @default {
    <span>Unknown</span>
  }
}
```

**Critical Rules:**
- `track` is **MANDATORY** in `@for` blocks
- Use `track item.id` for items with unique IDs
- Use `track $index` only for static/immutable lists
- Use `@empty` block for empty state handling
- No imports needed - works without CommonModule

**Contextual Variables in @for:**
```html
@for (item of items; track item.id; let idx = $index, let isLast = $last) {
  <div [class.border-b]="!isLast">{{ idx + 1 }}. {{ item.name }}</div>
}
```

| Variable | Purpose |
|----------|---------|
| `$index` | Current index (0-based) |
| `$count` | Total items |
| `$first` | `true` if first |
| `$last` | `true` if last |
| `$even` | `true` if even index |
| `$odd` | `true` if odd index |

### 5. Implement Responsive Breakpoints

```scss
// Mobile-first approach
.component {
  // Mobile (default)
  font-size: 0.75rem;
  padding: 0.5rem;

  // Tablet
  @media (min-width: 768px) {
    font-size: 0.875rem;
    padding: 0.75rem;
  }

  // Desktop
  @media (min-width: 1200px) {
    font-size: 1rem;
    padding: 1rem;
  }
}
```

### 6. Table Skeleton Loading States (MANDATORY)

**ALL tables MUST use the shared `TableSkeletonRowsComponent` for consistent loading states.**

```typescript
// 1. Import in table component
import {
  TableSkeletonRowsComponent,
  SkeletonColumnConfig,
  SKELETON_PRESETS
} from "../../../../shared/components/table-skeleton";

// 2. Add to component imports array
@Component({
  imports: [TableSkeletonRowsComponent, TableModule, ...],
})
export class MyTableComponent {
  // 3. Define skeleton columns (use preset or custom)
  readonly skeletonColumns: SkeletonColumnConfig[] = SKELETON_PRESETS.products;
}
```

```html
<!-- 4. In p-table, add loadingbody template -->
<p-table [value]="data" [loading]="loading">
  <!-- ... header and body templates ... -->

  <ng-template pTemplate="loadingbody">
    <app-table-skeleton-rows [rows]="5" [columns]="skeletonColumns" />
  </ng-template>
</p-table>
```

**Available Presets:**
- `SKELETON_PRESETS.customers` - Customers table (10 columns)
- `SKELETON_PRESETS.products` - Products table (15 columns)
- `SKELETON_PRESETS.scheduledOrders` - Scheduled orders table (14 columns)

**Custom Configuration:**
```typescript
// Create array matching your table columns
readonly skeletonColumns: SkeletonColumnConfig[] = [
  { width: "1.5rem", height: "1.5rem", shape: "circle", align: "center" }, // Checkbox
  { width: "3rem", height: "1rem" },           // ID
  { width: "120px", height: "1rem" },          // Name
  { width: "60px", height: "1.5rem" },         // Status tag
  { width: "80px", height: "1rem", align: "right" }, // Amount
];
```

**SkeletonColumnConfig Interface:**
| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `width` | string | required | Width of skeleton bar |
| `height` | string | "1rem" | Height of skeleton bar |
| `shape` | "rectangle" \| "circle" | "rectangle" | Shape of skeleton |
| `align` | "left" \| "center" \| "right" | "left" | Text alignment |

### 7. Handle Loading States

```typescript
// Component
loading = signal(true);
error = signal<Error | null>(null);

loadData(): void {
  this.loading.set(true);
  this.error.set(null);

  this.apiService.getData()
    .pipe(
      finalize(() => this.loading.set(false))
    )
    .subscribe({
      next: (data) => this.processData(data),
      error: (err) => this.error.set(err)
    });
}
```

```html
<!-- Template - Using Modern Control Flow -->
<div class="container">
  @if (loading()) {
    <!-- Loading state -->
    <div class="loading-container">
      <p-skeleton height="2rem" class="mb-2" />
      <p-skeleton height="20rem" />
    </div>
  } @else if (error()) {
    <!-- Error state -->
    <div class="error-container">
      <i class="pi pi-exclamation-triangle"></i>
      <p>{{ error()!.message }}</p>
      <p-button label="Retry" (onClick)="loadData()" />
    </div>
  } @else {
    <!-- Success state -->
    <div class="content">
      <!-- Your content -->
    </div>
  }
</div>
```

---

**Document Version**: 2.2
**Last Updated**: 2025-12-10
**Maintained By**: Development Team

For questions or suggestions, contact the development team or refer to the living examples in the codebase.