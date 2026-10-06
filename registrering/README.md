# Bursdag – registreringsskjema

Et enkelt Doodle-lignende registreringsskjema uten reklame, skrevet i PHP. Svar lagres i en SQLite-fil.

## Krav

PHP 8.0+ med `pdo_sqlite`.

## Kjør lokalt

```
php -S localhost:8000
```

Åpne `http://localhost:8000`. Databasen opprettes automatisk i `data/registrering.sqlite`.

## Legg inn datoer

```
php seed.php "Lørdag 11. oktober" "Lørdag 18. oktober" "Lørdag 25. oktober"
```

Hver parameter blir en rad i `aktiviteter`. Avkrysningsboksen for aktivitet med id 3 er deaktivert.

## Publisering

Last opp filene til en vert med PHP og pass på at `data/` er skrivbar for webserveren og ikke tilgjengelig utenfra (`.htaccess` følger med for Apache).

## Merk om personvern

Alle som har lenken kan se svarene. Ikke be om sensitive opplysninger.
