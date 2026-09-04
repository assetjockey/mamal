import { redirect } from 'next/navigation';
import { ToastProvider } from '@mamal/ui';
import { AppShell } from '@/components/shell';
import { getSession } from '@/lib/session';
import '../globals.css';

export const dynamic = 'force-dynamic';

/**
 * Everything under (app) requires a session. RLS would stop a signed-out
 * request from seeing data anyway, but the redirect is what makes that a
 * product rather than an empty screen.
 */
export default async function AppLayout({ children }: { children: React.ReactNode }) {
  const session = await getSession();
  if (!session) redirect('/sign-in');

  return (
    <html lang="en" data-theme="light" suppressHydrationWarning>
      <head>
        {/*
          Runs before first paint, deliberately blocking and inline.
          Deciding the theme in an effect means the page renders light and then
          flips — a white flash on every navigation for anyone who chose dark,
          which is the one thing dark mode exists to avoid. There is no way to
          do this after hydration, so it is a script tag.

          `prefers-color-scheme` is the default and the stored choice overrides
          it, which is the order the brief asks for: honour the system until the
          person says otherwise.
        */}
        <script
          dangerouslySetInnerHTML={{
            __html:
              "(function(){try{var t=localStorage.getItem('mamal.theme');" +
              "if(t!=='light'&&t!=='dark'){t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}" +
              "document.documentElement.dataset.theme=t;}catch(e){}})()",
          }}
        />
      </head>
      <body suppressHydrationWarning>
        <ToastProvider>
        <AppShell
          allowed={session.workspace.allowed}
          workspace={{
            name: session.workspace.name,
            plan: session.workspace.plan,
            credits: session.workspace.credits,
          }}
          user={{ name: session.user.name ?? session.user.email, email: session.user.email }}
        >
          {children}
        </AppShell>
        </ToastProvider>
      </body>
    </html>
  );
}
