# Notatki

## Wprowadzone zmiany

- `SeedDatabaseCommand.php` - wyniesienie danych seed'ujących do osobnych metod
- `SeedDatabaseCommand.php` - $entityManager jako `readonly` w konstruktorze
- `Version20251212134104` - propozycje zmian: dodanie nazwy dla constraint unique `username`
- Konfiguracje `bootstrap.php` (w `index.php`) oraz `autoload.php` (w `bootstrap.php`) ładujemy tylko raz (bez ładowania warunkowego) dlatego lepiej użyć `require_once` zamiast `require` 
- Uporządkowanie pakietów w projekcie, użycie podejścia warstwowego, wg. mnie za mały projekt aby wdrażać podejście domenowe
- Refaktoryzacja `AuthController`, klasa naruszała zasadę pojedyńczej odpowiedzialności, wyniesiono odpowiedzialność logowania do dedykowanego serwisu `AuthService` 
- Naprawa logiki logowania użykotników, logika pozwalała weryfikowała tylko czy token istnieje oraz czy użytkownik istnieje, nie weryfikowała czy token jest poprawnie przypisany do usera
- Funkcjonalność logowania pozwalała na wykonanie SQL Injection - SQL nie był escape'owany. Zamieniono na prepared statement
- Ogólne sprzątanie klas: dodanie informacji o rzucanych wyjątkach, dodanie importów dla klas std np. Throwable, Exception itd.
- Rename `LikeService::execute` -> `LikeService::likePhoto` - nazwa execute nic nie mówiła

## Poprawki na które zabrakło czasu

## Problemy i ich rozwiązanie

- Przy próbie pierwszego uruchomienia projektu komenda z pliku README nie zadziałała. Przy próbie uruchomienia dostawałem błąd, rozwiązaniem okazało się uruchomienie poprzez komendę `podman compose up --build`
```shell
=> ERROR [phoenix internal] booting buildkit
No such file or directory: OCI runtime attempted to invoke a command that was not found
```

- Lokalnie miałem już uruchomionego PostgreSQL'a na porcie 5432 przez co istniał konflikt i występowały błędy typu: `relation does not exists...` - aplikacja łączyła się do innej bazy. Rozwiązanie to ustawienie HOST_PORT na 5434 (port kontenera pozostawiony na 5432)

## Usprawnienia

- jeżeli aplikacja miałaby być używana przez osoby z Polski to warto dodać internacjonalizacje (tłumaczenia)
- strona nie jest responsywna na urządzeniach poniżej 1235px

## Sposób wykorzystania AI

- Fix uruchamiania projektu na Podman, wykorzystano _Claude_, model _Sonnet 4.6_
