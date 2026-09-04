'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { signUp } from '@/lib/auth-client';
import { AuthForm, Field } from '../form';

export default function SignUpPage() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [pending, setPending] = useState(false);

  async function onSubmit(data: FormData) {
    setPending(true);
    setError(null);
    const { error } = await signUp.email({
      email: String(data.get('email')),
      password: String(data.get('password')),
      name: String(data.get('name')),
    });
    if (error) {
      setError(error.message ?? 'Could not create that account.');
      setPending(false);
      return;
    }
    // A workspace, a Default project and an owner seat are provisioned by a
    // database hook, so the first render already has an RLS scope.
    router.push('/');
    router.refresh();
  }

  return (
    <AuthForm
      title="Create your workspace"
      submitLabel="Create workspace"
      pending={pending}
      error={error}
      onSubmit={onSubmit}
      footer={
        <>
          Already have one?{' '}
          <Link href="/sign-in" className="text-[var(--accent)] underline">
            Sign in
          </Link>
        </>
      }
    >
      <Field name="name" label="Your name" autoComplete="name" required />
      <Field name="email" label="Work email" type="email" autoComplete="email" required />
      <Field
        name="password"
        label="Password"
        type="password"
        autoComplete="new-password"
        minLength={10}
        hint="At least 10 characters."
        required
      />
    </AuthForm>
  );
}
