# Bursdag – registreringsskjema

Et enkelt Doodle-lignende registreringsskjema uten reklame.

Frontend kan hostes gratis på GitHub Pages. Svar lagres i Supabase.

## 1. Opprett Supabase-prosjekt

Lag et nytt prosjekt på Supabase, og kjør SQL-en under i SQL Editor:

```sql
create table public.responses (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  created_at timestamptz not null default now()
);

alter table public.responses enable row level security;

create policy "Alle kan lese svar"
on public.responses
for select
using (true);

create policy "Alle kan legge inn svar"
on public.responses
for insert
with check (true);
```

## 2. Sett inn Supabase-nøkler

Åpne `app.js` og erstatt:

```js
const SUPABASE_URL = "DIN_SUPABASE_URL";
const SUPABASE_ANON_KEY = "DIN_SUPABASE_ANON_KEY";
```

med verdiene fra Supabase:
Project Settings → API → Project URL og anon public key.

## 3. Test lokalt

Åpne `index.html` i nettleseren.

## 4. Publiser på GitHub Pages

1. Lag et nytt GitHub-repo
2. Last opp filene
3. Gå til Settings → Pages
4. Velg Deploy from branch
5. Velg `main` og `/root`
6. Lagre

## Merk om personvern

Alle som har lenken kan se svarene. Ikke be om sensitive opplysninger.
