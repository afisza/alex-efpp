# GitHub Updater - Instrukcja konfiguracji

Wtyczka Alex EFPP zawiera wbudowany system aktualizacji z GitHub, który pozwala na automatyczne aktualizowanie wtyczki bezpośrednio z WordPress dashboard.

## Jak to działa?

1. Updater sprawdza najnowszą wersję z GitHub Releases API
2. Porównuje z aktualną wersją wtyczki
3. Jeśli dostępna jest nowsza wersja, pokazuje powiadomienie w WordPress dashboard
4. Pozwala na aktualizację jednym kliknięciem

## Konfiguracja

Aby włączyć updater, musisz skonfigurować URL do Twojego repozytorium GitHub. Masz dwie opcje:

### Opcja 1: Stała w `wp-config.php` (Zalecane)

Dodaj do pliku `wp-config.php` (przed linią `/* That's all, stop editing! */`):

```php
define('ALEX_EFPP_GITHUB_REPO_URL', 'https://github.com/TWOJA_NAZWA_UZYTKOWNIKA/NAZWA_REPOZYTORIUM');
```

**Przykład:**
```php
define('ALEX_EFPP_GITHUB_REPO_URL', 'https://github.com/alexscar/alex-efpp');
```

### Opcja 2: Filtr w `functions.php` motywu

Dodaj do pliku `functions.php` Twojego motywu:

```php
add_filter('alex_efpp_github_repo_url', function() {
    return 'https://github.com/TWOJA_NAZWA_UZYTKOWNIKA/NAZWA_REPOZYTORIUM';
});
```

**Przykład:**
```php
add_filter('alex_efpp_github_repo_url', function() {
    return 'https://github.com/alexscar/alex-efpp';
});
```

## Wymagania GitHub

Aby updater działał poprawnie, musisz:

1. **Utworzyć repozytorium GitHub** z kodem wtyczki
2. **Utworzyć Release** na GitHub z tagiem wersji (np. `v1.0.3.2` lub `1.0.3.2`)
3. **Upewnić się, że tag wersji w GitHub Release** odpowiada wersji w nagłówku wtyczki (`Version: 1.0.3.2`)

## Tworzenie Release na GitHub

1. Przejdź do swojego repozytorium na GitHub
2. Kliknij **"Releases"** → **"Create a new release"**
3. Wybierz tag (lub utwórz nowy) - np. `v1.0.3.2`
4. Wypełnij tytuł i opis (opcjonalnie)
5. Kliknij **"Publish release"**

GitHub automatycznie utworzy plik ZIP z kodem, który WordPress pobierze podczas aktualizacji.

## Jak aktualizować wersję?

1. **Zaktualizuj wersję w nagłówku wtyczki** (`alex-efpp.php`):
   ```php
   Version: 1.0.3.3
   ```

2. **Commit i push** zmiany do GitHub:
   ```bash
   git add .
   git commit -m "Update to version 1.0.3.3"
   git push origin main
   ```

3. **Utwórz nowy Release** na GitHub z tagiem `v1.0.3.3`

4. **WordPress automatycznie wykryje** nową wersję i pokaże powiadomienie o aktualizacji

## Sprawdzanie aktualizacji

WordPress automatycznie sprawdza aktualizacje:
- Przy każdym wejściu do panelu administracyjnego
- Cache wyniku sprawdzania: 1 godzina (aby nie obciążać GitHub API)

Możesz też wymusić sprawdzenie:
1. Przejdź do **Plugins** → **Installed Plugins**
2. Kliknij **"Check for updates"** (jeśli dostępne)

## Rozwiązywanie problemów

### Updater nie działa

1. **Sprawdź czy URL repozytorium jest poprawnie skonfigurowany**
   - Upewnij się, że stała lub filtr jest poprawnie ustawiony
   - URL powinien być w formacie: `https://github.com/username/repo-name`

2. **Sprawdź czy repozytorium jest publiczne**
   - Updater działa tylko z publicznymi repozytoriami
   - Dla prywatnych repozytoriów potrzebny byłby token API (nieobsługiwane w tej wersji)

3. **Sprawdź czy Release istnieje na GitHub**
   - Przejdź do: `https://github.com/username/repo/releases`
   - Upewnij się, że istnieje przynajmniej jeden Release

4. **Sprawdź czy tag wersji jest poprawny**
   - Tag powinien odpowiadać wersji w nagłówku wtyczki
   - Może być z prefiksem `v` (np. `v1.0.3.2`) lub bez (`1.0.3.2`)

### Błąd podczas pobierania

1. **Sprawdź połączenie z internetem**
2. **Sprawdź czy GitHub jest dostępny**
3. **Sprawdź logi błędów WordPress** (`wp-content/debug.log` jeśli `WP_DEBUG` jest włączony)

## Bezpieczeństwo

- Updater używa oficjalnego GitHub API
- Nie wymaga żadnych tokenów API dla publicznych repozytoriów
- Wszystkie pobierane pliki są weryfikowane przez WordPress
- Cache zapobiega nadmiernym zapytaniom do GitHub API

## Uwagi

- Updater działa tylko z repozytoriami GitHub
- Wymaga utworzenia Release na GitHub (nie działa z branchami)
- Wersja w nagłówku wtyczki musi odpowiadać tagowi Release
- Cache sprawdzania aktualizacji: 1 godzina

