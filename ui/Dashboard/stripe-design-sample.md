# Design Specification: Stripe-Inspired Dashboard (design.md)

**Overview**
This document outlines the UI/UX principles for redesigning the site based on the modern, clean, and data-centric aesthetic of the Stripe dashboard. It includes specifications for global layouts, navigation menus, and specific page templates to ensure consistency across the application.

**1. Color Palette**
*   **Background:** Very light gray (`#F6F9FC`) for the main canvas to provide soft contrast.
*   **Surface/Cards:** Pure white (`#FFFFFF`) for all content containers and the side navigation.
*   **Primary Action/Accent:** Indigo/Blurple (`#635BFF`) for active states, text links, and primary buttons.
*   **Primary Text:** Dark Slate (`#1A1F36`) for high-contrast headers and primary data points.
*   **Secondary Text:** Cool Gray (`#4F566B`) for labels, menu items, timestamps, and secondary information.
*   **Borders/Dividers:** Very subtle light gray (`#E3E8EE`) for separating sections and table rows.

**2. Typography**
*   **Font Family:** A modern, highly legible sans-serif (e.g., Inter, system fonts like San Francisco/Segoe UI).
*   **Hierarchy:**
    *   **Page Titles (H1):** 24px, Bold (e.g., "Today", "Balances").
    *   **Section Headers (H2/H3):** 16px, Semi-bold.
    *   **Body Text:** 14px, Regular.
    *   **Small/Metadata:** 12px, Regular (e.g., "Updated 3 hours ago").

**3. Global Layout & Grid System**
*   **Top Utility Bar:**
    *   Search: Prominent, pill-shaped global Search bar in the center-left with a subtle gray background (`#F3F4F6`) and a magnifying glass icon.
    *   Right-aligned utilities: Tightly grouped, minimalist outline icons for Grid/Apps, Help, Notifications, Settings, and a prominent "+" create button.
*   **Left Sidebar (Navigation):**
    *   Fixed width (approx. 240px), pure white background, separated from the main content by a 1px subtle border.
    *   Header: Account switcher dropdown (e.g., "Grazes" with an avatar/logo).
    *   Footer: "Developers" link pinned to the bottom.
*   **Main Content Area:**
    *   Padding: Generous top and side padding (at least 32px) with a maximum width to maintain readability on extra-wide screens.

**4. Navigation Menu Structure (Sidebar)**
*   **Styling:** Menu items use 14px secondary text (`#4F566B`). Active states feature a bold primary color (`#635BFF`) with a corresponding active icon color. Hover states use a subtle gray background pill.
*   **Categorization:** Grouped with subtle spacing and tiny, all-caps category headers (11px, bold, gray) where necessary.
*   **Menu Groupings:**
    *   **Main Navigation:** Home, Balances, Transactions, Customers, Product catalog.
    *   **Shortcuts (Collapsible/Customizable):** Apps overview, Invoices, Subscriptions.
    *   **Products (Accordion style):** Payments, Billing, Reporting, Apps, More.

**5. Page Templates & Layouts**

*   **Home / Dashboard Page:**
    *   Asymmetrical layout: Main left column (70%) for high-level charts ("Gross volume") and comparative overviews ("Your overview"). Right column (30%) for actionable widgets (API keys, Recommendations, Setup guide).
    *   Cards feature 8px border-radius, pure white backgrounds, and faint 1px borders or ultra-light drop shadows.

*   **List / Data Pages (Transactions, Customers, Invoices):**
    *   **Header:** Page title on the left, primary action button (e.g., "+ Add customer") on the right.
    *   **Filter Bar:** A row below the header containing pill-shaped dropdowns for filtering (Date, Status, Amount) and an export button.
    *   **Data Tables:** Edge-to-edge styling within a white card.
        *   Headers: 12px, all-caps, light gray text.
        *   Rows: 14px text, separated by 1px light gray borders. Hover state on rows turns the background slightly gray.
        *   Actions: An ellipsis icon on the far right of each row for quick actions.

*   **Detail Pages (Customer Profile, Specific Transaction):**
    *   **Two-Column Split:** 
        *   Left Column (30%): Sticky sidebar detailing static entity info (Customer name, email, metadata, ID badges).
        *   Right Column (70%): Scrollable history, showing recent activity, payments, logs, and notes in a vertical timeline or stacked card format.

**6. UI Components**
*   **Tabs:** Underlined style. Active tab has a bold text and a dark slate or indigo bottom border; inactive tabs are gray.
*   **Badges/Status Tags:** Small pill shapes. Green background/dark green text for "Succeeded", gray/dark gray for "Draft", red/dark red for "Failed".
*   **Setup/Progress Guides:** Floating or docked widgets in the bottom right featuring horizontal progress bars and step-by-step checklist items with strikethrough animations upon completion.