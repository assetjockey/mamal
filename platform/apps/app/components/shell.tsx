'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  Activity, BarChart3, BellRing, ChevronsLeft, Link2, Menu, Moon,
  Search, Settings, ShieldCheck, Sun, TrendingUp, Workflow, X,
} from 'lucide-react';
import {
  CommandPalette,
  ShortcutSheet,
  TOOL_NAV,
  groupItems,
  type PaletteItem,
  type ShortcutGroup,
  type ToolNav,
} from '@mamal/ui';
import { signOut } from '@/lib/auth-client';
import { searchResources } from '@/app/(app)/palette-actions';

const ICONS = { ShieldCheck, BellRing, Link2, TrendingUp, Activity, BarChart3 } as const;

const cx = (...p: (string | false | null | undefined)[]) => p.filter(Boolean).join(' ');

export type ShellProps = {
  children: React.ReactNode;
  /** Entitlement keys this workspace has. Nav is filtered SERVER-side; this
   *  is only for the lock affordance on tools the workspace cannot use. */
  allowed: string[];
  workspace: { name: string; plan: string; credits: number };
  user: { name: string; email: string };
};

export function AppShell({ children, allowed, workspace, user }: ShellProps) {
  const pathname = usePathname();
  const active = TOOL_NAV.find((t) => pathname === t.href || pathname.startsWith(`${t.href}/`));
  const router = useRouter();
  const [contextOpen, setContextOpen] = useState(true);
  const [mobileNav, setMobileNav] = useState(false);
  /*
   * Seeded from what the pre-paint script already decided, so this never
   * disagrees with what is on screen. `useState('light')` here meant a
   * returning dark-mode user got one frame of white and then a flip — and,
   * because nothing was stored, got it again on every reload.
   */
  const [theme, setTheme] = useState<'light' | 'dark'>(() =>
    typeof document !== 'undefined' && document.documentElement.dataset.theme === 'dark'
      ? 'dark'
      : 'light',
  );
  const [paletteOpen, setPaletteOpen] = useState(false);
  const [shortcutsOpen, setShortcutsOpen] = useState(false);
  // `g` is a prefix, not a key: `g a` jumps to Audit. It expires so a stray `g`
  // does not silently arm a jump minutes later.
  const pendingJump = useRef<number | null>(null);
  const [jumpArmed, setJumpArmed] = useState(false);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    // Per-user and per-device. A workspace-level setting would be wrong: the
    // same person wants dark on a laptop at night and light on a bright screen.
    try {
      localStorage.setItem('mamal.theme', theme);
    } catch {
      // A private window still has to work; it just forgets between sessions.
    }
  }, [theme]);

  const toggleTheme = useCallback(() => setTheme((t) => (t === 'light' ? 'dark' : 'light')), []);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      // The palette opens from anywhere, including a text field — that is the
      // point of a global shortcut, and it is what every user expects of ⌘K.
      if (e.key.toLowerCase() === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        setShortcutsOpen(false);
        setPaletteOpen((v) => !v);
        return;
      }
      if (e.key === 'Escape') {
        // Close whatever is open, from wherever focus happens to be. The
        // palette also handles Escape on its input, but focus lands there on a
        // timeout — so for a moment after opening, the input-level handler is
        // not yet reachable and Escape would do nothing.
        setMobileNav(false);
        setPaletteOpen(false);
        setShortcutsOpen(false);
        return;
      }
      // Everything below is a bare key, so it must never fire while typing.
      if (isTyping(e) || e.metaKey || e.ctrlKey || e.altKey) return;

      if (jumpArmed) {
        const tool = TOOL_NAV.find((t) => t.jumpKey === e.key.toLowerCase());
        const platform =
          e.key === 'h' ? '/' : e.key === 's' ? '/settings' : e.key === 'w' ? '/automations' : null;
        if (tool || platform) {
          e.preventDefault();
          router.push(tool ? tool.href : platform!);
        }
        disarm();
        return;
      }

      if (e.key === 'g') {
        setJumpArmed(true);
        if (pendingJump.current) window.clearTimeout(pendingJump.current);
        pendingJump.current = window.setTimeout(disarm, 1500);
        return;
      }
      if (e.key === '/') {
        e.preventDefault();
        setPaletteOpen(true);
        return;
      }
      if (e.key === '?') {
        e.preventDefault();
        setShortcutsOpen((v) => !v);
        return;
      }
      if (e.key === '[') setContextOpen((v) => !v);
    };

    function disarm() {
      setJumpArmed(false);
      if (pendingJump.current) window.clearTimeout(pendingJump.current);
      pendingJump.current = null;
    }

    window.addEventListener('keydown', onKey);
    return () => {
      window.removeEventListener('keydown', onKey);
      if (pendingJump.current) window.clearTimeout(pendingJump.current);
    };
  }, [jumpArmed, router]);

  /*
   * The palette's contents are derived from the same nav data the sidebar
   * renders and the same `allowed` list that gates it, so a tool the workspace
   * cannot use is absent from both. Building this list by hand is how a palette
   * ends up offering a route that 403s.
   */
  const paletteItems = useMemo<PaletteItem[]>(() => {
    const items: PaletteItem[] = [];

    for (const tool of TOOL_NAV) {
      const unlocked = hasAnyAccess(tool, allowed);
      items.push({
        key: `tool-${tool.key}`,
        label: tool.label,
        hint: unlocked ? undefined : 'Locked',
        keywords: tool.description,
        section: 'Go to',
        shortcut: `g ${tool.jumpKey}`,
        href: unlocked ? tool.href : `/settings/plans?tool=${tool.key}`,
      });
      if (!unlocked) continue;
      for (const item of visibleItems(tool, allowed)) {
        items.push({
          key: `nav-${item.key}`,
          label: item.label,
          hint: tool.label,
          section: 'Go to',
          href: item.href,
        });
      }
    }

    items.push(
      { key: 'nav-home', label: 'Dashboard', section: 'Go to', shortcut: 'g h', href: '/' },
      { key: 'nav-automations', label: 'Automations', section: 'Go to', shortcut: 'g w', href: '/automations' },
      { key: 'nav-settings', label: 'Settings', section: 'Go to', shortcut: 'g s', href: '/settings' },
      { key: 'nav-plans', label: 'Plans and billing', keywords: 'upgrade subscription price credits', section: 'Go to', href: '/settings/plans' },
      { key: 'act-add-site', label: 'Add a website', keywords: 'new create site audit monitor track', section: 'Actions', href: '/welcome' },
      { key: 'act-theme', label: `Switch to ${theme === 'light' ? 'dark' : 'light'} theme`, keywords: 'dark light appearance', section: 'Actions', run: toggleTheme },
      { key: 'act-collapse', label: contextOpen ? 'Collapse sidebar' : 'Expand sidebar', section: 'Actions', shortcut: '[', run: () => setContextOpen((v) => !v) },
      { key: 'help-shortcuts', label: 'Keyboard shortcuts', keywords: 'keys help', section: 'Help', shortcut: '?', run: () => setShortcutsOpen(true) },
      { key: 'act-signout', label: 'Sign out', section: 'Actions', run: () => void signOut().then(() => { window.location.href = '/sign-in'; }) },
    );
    return items;
  }, [allowed, theme, contextOpen, toggleTheme]);

  const shortcutGroups = useMemo<ShortcutGroup[]>(
    () => [
      {
        title: 'General',
        keys: [
          { keys: ['⌘', 'K'], label: 'Open the command palette' },
          { keys: ['/'], label: 'Search' },
          { keys: ['?'], label: 'This sheet' },
          { keys: ['['], label: 'Collapse or expand the sidebar' },
          { keys: ['Esc'], label: 'Close' },
        ],
      },
      {
        title: 'Jump to',
        keys: [
          ...TOOL_NAV.map((t) => ({ keys: ['g', t.jumpKey], label: t.label })),
          { keys: ['g', 'h'], label: 'Dashboard' },
          { keys: ['g', 'w'], label: 'Automations' },
          { keys: ['g', 's'], label: 'Settings' },
        ],
      },
      {
        title: 'In the palette',
        keys: [
          { keys: ['↑', '↓'], label: 'Move through results' },
          { keys: ['↵'], label: 'Open the highlighted result' },
        ],
      },
    ],
    [],
  );

  useEffect(() => setMobileNav(false), [pathname]);

  return (
    <div className="flex min-h-dvh bg-[var(--surface-canvas)]">
      {/*
        Both nav tiers sit ahead of the content in the DOM, so without this a
        keyboard user tabs through every tool and every section before reaching
        the page — on every route. Visible only once focused (WCAG 2.4.1).
      */}
      <a
        href="#main"
        className="sr-only z-50 focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:rounded-[4px] focus:bg-[var(--accent-solid)] focus:px-4 focus:py-2 focus:text-[14px] focus:text-[var(--on-accent)]"
      >
        Skip to content
      </a>

      {/*
        ---------------------------------------------------- tier 1: rail

        The rail (64px) and the context nav (216px) are written as pixels, and
        so is the 280px offset that clears them. Everything else in the app uses
        the 8px spacing scale, but these three numbers are a specification and
        they have to agree with each other: `w-56` on an 8px scale is 448px, and
        pairing it with a `pl-[18rem]` offset put 288px of every page
        permanently underneath the navigation at lg and up.
      */}
      <nav
        aria-label="Tools"
        className="fixed inset-y-0 left-0 z-30 hidden w-[64px] flex-col items-center gap-1 border-r border-[var(--border-hairline)] bg-[var(--surface-raised)] py-4 md:flex"
      >
        <Link
          href="/"
          className="mb-3 flex size-5 items-center justify-center rounded-[4px] bg-[var(--accent-solid)] text-[15px] text-[var(--on-accent)]"
          aria-label="Home"
        >
          M
        </Link>

        {TOOL_NAV.map((tool) => (
          <RailButton
            key={tool.key}
            tool={tool}
            active={active?.key === tool.key}
            locked={!hasAnyAccess(tool, allowed)}
          />
        ))}

        <div className="my-2 h-px w-4 bg-[var(--border-hairline)]" />
        <RailIcon href="/automations" label="Automations" active={pathname.startsWith('/automations')}>
          <Workflow size={18} strokeWidth={1.5} />
        </RailIcon>
        <RailIcon href="/settings" label="Settings" active={pathname.startsWith('/settings')}>
          <Settings size={18} strokeWidth={1.5} />
        </RailIcon>

        <button
          onClick={() => setTheme((t) => (t === 'light' ? 'dark' : 'light'))}
          className="mt-auto flex size-5 items-center justify-center rounded-[4px] text-[var(--text-secondary)] transition-colors duration-[120ms] hover:bg-[var(--surface-hover)]"
          aria-label="Toggle theme"
        >
          {theme === 'light' ? <Moon size={18} strokeWidth={1.5} /> : <Sun size={18} strokeWidth={1.5} />}
        </button>
      </nav>

      {/* ------------------------------------------------- tier 2: context */}
      {active && contextOpen ? (
        <aside
          aria-label={`${active.label} navigation`}
          className="fixed inset-y-0 left-[64px] z-20 hidden w-[216px] flex-col border-r border-[var(--border-hairline)] bg-[var(--surface-raised)] lg:flex"
        >
          <ContextNav
            tool={active}
            allowed={allowed}
            workspace={workspace}
            user={user}
            pathname={pathname}
            onOpenPalette={() => setPaletteOpen(true)}
          />
          <button
            onClick={() => setContextOpen(false)}
            className="flex items-center gap-1.5 border-t border-[var(--border-hairline)] px-4 py-2.5 text-[12px] text-[var(--text-faint)] transition-colors hover:text-[var(--text-secondary)]"
          >
            <ChevronsLeft size={14} strokeWidth={1.5} /> Collapse
            <kbd className="ml-auto rounded-[3px] border border-[var(--border-hairline)] px-1 text-[10px]">[</kbd>
          </button>
        </aside>
      ) : null}

      {/* ------------------------------------------------------ mobile bar */}
      <header className="fixed inset-x-0 top-0 z-30 flex h-[56px] items-center gap-3 border-b border-[var(--border-hairline)] bg-[var(--surface-raised)] px-4 md:hidden">
        <button onClick={() => setMobileNav(true)} aria-label="Open navigation" className="text-[var(--text-secondary)]">
          <Menu size={20} strokeWidth={1.5} />
        </button>
        <span className="text-[14px]">{active?.label ?? 'Mamal'}</span>
        <span className="ml-auto text-[12px] tabular-nums text-[var(--text-faint)]">
          {workspace.credits.toLocaleString()} cr
        </span>
      </header>

      {mobileNav ? (
        <div className="fixed inset-0 z-40 md:hidden">
          <div className="absolute inset-0 bg-black/30" onClick={() => setMobileNav(false)} />
          <div className="absolute inset-y-0 left-0 w-[min(20rem,85vw)] overflow-y-auto bg-[var(--surface-raised)] p-4">
            <div className="mb-4 flex items-center justify-between">
              <span className="text-[14px]">{workspace.name}</span>
              <button onClick={() => setMobileNav(false)} aria-label="Close navigation">
                <X size={18} strokeWidth={1.5} />
              </button>
            </div>
            {TOOL_NAV.map((tool) => (
              <div key={tool.key} className="mb-4">
                <Link href={tool.href} className="mb-1 block text-[13px] text-[var(--text-primary)]">
                  {tool.label}
                </Link>
                <div className="ml-3 border-l border-[var(--border-hairline)] pl-3">
                  {visibleItems(tool, allowed).map((i) => (
                    <Link
                      key={i.key}
                      href={i.href}
                      className="block py-1 text-[13px] text-[var(--text-secondary)]"
                    >
                      {i.label}
                    </Link>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      ) : null}

      {/* --------------------------------------------------------- content */}
      <CommandPalette
        open={paletteOpen}
        onClose={() => setPaletteOpen(false)}
        items={paletteItems}
        onSearch={searchResources}
        onNavigate={(href) => router.push(href)}
      />
      <ShortcutSheet
        open={shortcutsOpen}
        onClose={() => setShortcutsOpen(false)}
        groups={shortcutGroups}
      />

      <main
        id="main"
        tabIndex={-1}
        className={cx(
          'min-w-0 flex-1 pt-[56px] md:pt-0 md:pl-[64px]',
          active && contextOpen ? 'lg:pl-[280px]' : '',
        )}
      >
        <div className="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-10">{children}</div>
      </main>
    </div>
  );
}

function ContextNav({
  tool,
  allowed,
  workspace,
  user,
  pathname,
  onOpenPalette,
}: {
  tool: ToolNav;
  allowed: string[];
  workspace: ShellProps['workspace'];
  user: ShellProps['user'];
  pathname: string;
  onOpenPalette: () => void;
}) {
  return (
    <>
      <div className="border-b border-[var(--border-hairline)] px-4 py-3.5">
        <div className="truncate text-[14px] text-[var(--text-primary)]">{workspace.name}</div>
        <div className="mt-0.5 text-[12px] text-[var(--text-faint)]">{workspace.plan}</div>
      </div>

      {/* The palette is the fastest path through the app, and a shortcut with no
          visible trigger is a shortcut only its author uses. */}
      <div className="px-2 pt-3">
        <button
          onClick={onOpenPalette}
          className="flex w-full items-center gap-2 rounded-[4px] border border-[var(--border-hairline)] px-2.5 py-1.5 text-left text-[13px] text-[var(--text-faint)] transition-colors duration-[120ms] hover:bg-[var(--surface-hover)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
        >
          <Search size={14} strokeWidth={1.5} aria-hidden />
          <span className="min-w-0 flex-1 truncate">Search</span>
          <kbd className="shrink-0 rounded-[3px] border border-[var(--border-hairline)] px-1 text-[10px]">
            ⌘K
          </kbd>
        </button>
      </div>

      <div className="flex-1 overflow-y-auto px-2 py-3">
        {groupItems(visibleItems(tool, allowed)).map(({ group, items }) => (
          <div key={group} className="mb-4">
            {group ? (
              <div className="px-2 pb-1.5 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                {group}
              </div>
            ) : null}
            {items.map((item) => {
              const isActive = pathname === item.href;
              return (
                <Link
                  key={item.key}
                  href={item.href}
                  aria-current={isActive ? 'page' : undefined}
                  className={cx(
                    'block rounded-[4px] px-2 py-1.5 text-[14px] transition-colors duration-[120ms]',
                    isActive
                      ? 'bg-[var(--accent-wash)] font-normal text-[var(--accent)]'
                      : 'text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]',
                  )}
                >
                  {item.label}
                </Link>
              );
            })}
          </div>
        ))}
      </div>

      <div className="border-t border-[var(--border-hairline)] px-4 py-3">
        <div className="flex items-baseline justify-between">
          <span className="text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">Credits</span>
          <span className="text-[14px] tabular-nums">{workspace.credits.toLocaleString()}</span>
        </div>
        <div className="mt-3 flex items-center justify-between gap-2 border-t border-[var(--border-hairline)] pt-3">
          <span className="truncate text-[12px] text-[var(--text-muted)]" title={user.email}>
            {user.name}
          </span>
          <button
            onClick={() => void signOut().then(() => { window.location.href = '/sign-in'; })}
            className="shrink-0 text-[12px] text-[var(--text-faint)] transition-colors hover:text-[var(--accent)]"
          >
            Sign out
          </button>
        </div>
      </div>
    </>
  );
}

function RailButton({ tool, active, locked }: { tool: ToolNav; active: boolean; locked: boolean }) {
  const Icon = ICONS[tool.icon as keyof typeof ICONS] ?? Activity;
  return (
    <Link
      href={locked ? `/settings/plans?tool=${tool.key}` : tool.href}
      title={locked ? `${tool.label} — upgrade to unlock` : tool.label}
      aria-label={tool.label}
      className={cx(
        'group relative flex size-5 items-center justify-center rounded-[4px] transition-colors duration-[120ms]',
        active
          ? 'bg-[var(--accent-wash)] text-[var(--accent)]'
          : locked
            ? 'text-[var(--text-faint)] opacity-50 hover:opacity-100'
            : 'text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]',
      )}
    >
      <Icon size={18} strokeWidth={1.5} />
    </Link>
  );
}

function RailIcon({
  href, label, active, children,
}: { href: string; label: string; active: boolean; children: React.ReactNode }) {
  return (
    <Link
      href={href}
      title={label}
      aria-label={label}
      className={cx(
        'flex size-5 items-center justify-center rounded-[4px] transition-colors duration-[120ms]',
        active
          ? 'bg-[var(--accent-wash)] text-[var(--accent)]'
          : 'text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]',
      )}
    >
      {children}
    </Link>
  );
}

/** An item with no `requires` is always shown; otherwise the workspace needs it. */
function visibleItems(tool: ToolNav, allowed: string[]) {
  return tool.items.filter((i) => !i.requires || allowed.includes(i.requires));
}

function hasAnyAccess(tool: ToolNav, allowed: string[]) {
  return tool.items.some((i) => !i.requires || allowed.includes(i.requires));
}

function isTyping(e: KeyboardEvent) {
  const el = e.target as HTMLElement | null;
  return !!el && ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName);
}
