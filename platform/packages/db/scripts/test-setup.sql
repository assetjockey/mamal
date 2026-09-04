-- Creates the non-superuser role the RLS tests must run as.
-- Superusers bypass row level security entirely, so a suite run as the schema
-- owner would pass vacuously. rls.live.test.ts asserts it is NOT a superuser.
do $$ begin
  if not exists (select 1 from pg_roles where rolname = 'mamal_app') then
    create role mamal_app login password 'mamal_dev_pw';
  end if;
end $$;

grant usage on schema public to mamal_app;
grant select, insert, update, delete on all tables in schema public to mamal_app;
grant usage, select on all sequences in schema public to mamal_app;
alter default privileges in schema public
  grant select, insert, update, delete on tables to mamal_app;

-- Some suites create their own fixture tables (a stand-in for an entity table
-- that a later phase will add). Scoped to the dev/test database only.
grant create on schema public to mamal_app;
