'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { signIn } from '@/lib/auth-client';
import { AuthForm, Field } from '../form';

export default function SignInPage() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [pending, setPending] = useState(false);

  async function onSubmit(data: FormData) {
    setPending(true);
    setError(null);
    const { error } = await signIn.email({
      email: String(data.get('email')),
      password: String(data.get('password')),
    });
    if (error) {
      setError(error.message ?? 'Those credentials did not work.');
      setPending(false);
      return;
    }
    router.push('/');
    router.refresh();
  }

  return (
    <AuthForm
      title="Sign in"
      submitLabel="Sign in"
      pending={pending}
      error={error}
      onSubmit={onSubmit}
      footer={
        <>
          No account yet?{' '}
          <Link href="/sign-up" className="text-[var(--accent)] underline">
            Create one
          </Link>
        </>
      }
    >
      <Field name="email" label="Email" type="email" autoComplete="email" required />
      <Field
        name="password"
        label="Password"
        type="password"
        autoComplete="current-password"
        required
      />
    </AuthForm>
  );
}
