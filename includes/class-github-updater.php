<?php
/**
 * GitHub Updater dla Alex EFPP
 * 
 * Pozwala na automatyczną aktualizację wtyczki z repozytorium GitHub
 * przez WordPress dashboard.
 */

if (!defined('ABSPATH')) exit;

class Alex_EFPP_GitHub_Updater {

    /**
     * URL repozytorium GitHub (bez .git)
     * Można ustawić przez stałą ALEX_EFPP_GITHUB_REPO_URL lub filtr
     */
    private $github_repo_url = '';
    
    /**
     * Nazwa użytkownika GitHub
     */
    private $github_username = '';
    
    /**
     * Nazwa repozytorium
     */
    private $github_repo = '';
    
    /**
     * Ścieżka do pliku głównego wtyczki
     */
    private $plugin_file;
    
    /**
     * Slug wtyczki
     */
    private $plugin_slug;
    
    /**
     * Aktualna wersja wtyczki
     */
    private $current_version;
    
    /**
     * Cache dla informacji o wersji
     */
    private $version_cache_key = 'alex_efpp_github_version';
    private $version_cache_time = 3600; // 1 godzina

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        
        // Pobierz aktualną wersję z nagłówka wtyczki
        $plugin_data = get_file_data($plugin_file, ['Version' => 'Version', 'Plugin URI' => 'Plugin URI'], 'plugin');
        $this->current_version = $plugin_data['Version'];
        
        // Automatyczne wykrywanie URL repozytorium (w kolejności priorytetu):
        // 1. Stała ALEX_EFPP_GITHUB_REPO_URL
        // 2. Filtr alex_efpp_github_repo_url
        // 3. Pole "Plugin URI" z nagłówka wtyczki
        // 4. Automatyczne wykrycie z .git/config
        if (defined('ALEX_EFPP_GITHUB_REPO_URL')) {
            $this->github_repo_url = ALEX_EFPP_GITHUB_REPO_URL;
        } elseif (($filtered_url = apply_filters('alex_efpp_github_repo_url', '')) !== '') {
            $this->github_repo_url = $filtered_url;
        } elseif (!empty($plugin_data['Plugin URI'])) {
            $this->github_repo_url = $plugin_data['Plugin URI'];
        } else {
            // Spróbuj wykryć z .git/config
            $this->github_repo_url = $this->detect_github_url_from_git();
        }
        
        // Jeśli nie ma URL, nie inicjalizuj updatera
        if (empty($this->github_repo_url)) {
            return;
        }
        
        // Parsuj URL repozytorium
        $this->parse_github_url();
        
        // Hooki WordPress
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates'], 10, 1);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);
        add_action('upgrader_process_complete', [$this, 'clear_version_cache'], 10, 2);
        
        // Dodaj link "Sprawdź aktualizacje" w wierszu wtyczki
        add_filter('plugin_row_meta', [$this, 'add_check_update_link'], 10, 2);
        
        // AJAX endpoint do wymuszenia sprawdzenia aktualizacji
        add_action('wp_ajax_alex_efpp_check_update', [$this, 'ajax_check_update']);
    }
    
    /**
     * Próbuje automatycznie wykryć URL GitHub z pliku .git/config
     */
    private function detect_github_url_from_git() {
        $plugin_dir = dirname($this->plugin_file);
        $git_config = $plugin_dir . '/.git/config';
        
        // Sprawdź czy plik .git/config istnieje
        if (!file_exists($git_config)) {
            // Sprawdź w katalogu nadrzędnym (jeśli wtyczka jest w podfolderze)
            $parent_git_config = dirname($plugin_dir) . '/.git/config';
            if (file_exists($parent_git_config)) {
                $git_config = $parent_git_config;
            } else {
                return '';
            }
        }
        
        // Odczytaj plik .git/config
        $config_content = file_get_contents($git_config);
        if (empty($config_content)) {
            return '';
        }
        
        // Wyciągnij URL z sekcji [remote "origin"]
        if (preg_match('/\[remote\s+"origin"\][^\[]*url\s*=\s*(.+)/i', $config_content, $matches)) {
            $url = trim($matches[1]);
            
            // Konwertuj SSH URL na HTTPS jeśli potrzeba
            // git@github.com:username/repo.git -> https://github.com/username/repo
            if (preg_match('/git@github\.com:(.+?)\.git$/', $url, $ssh_matches)) {
                $url = 'https://github.com/' . $ssh_matches[1];
            }
            
            // Usuń .git z końca jeśli istnieje
            $url = rtrim($url, '.git');
            $url = rtrim($url, '/');
            
            // Sprawdź czy to GitHub URL
            if (strpos($url, 'github.com') !== false) {
                return $url;
            }
        }
        
        return '';
    }

    /**
     * Parsuje URL repozytorium GitHub
     */
    private function parse_github_url() {
        if (empty($this->github_repo_url)) {
            return;
        }
        
        // Usuń .git jeśli jest
        $url = rtrim($this->github_repo_url, '.git');
        $url = rtrim($url, '/');
        
        // Wyciągnij username i repo z URL
        if (preg_match('#github\.com/([^/]+)/([^/]+)#', $url, $matches)) {
            $this->github_username = $matches[1];
            $this->github_repo = $matches[2];
        }
    }

    /**
     * Sprawdza dostępność aktualizacji
     */
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Jeśli updater nie jest skonfigurowany, nie sprawdzaj aktualizacji
        if (empty($this->github_username) || empty($this->github_repo)) {
            return $transient;
        }

        $latest_version = $this->get_latest_version();
        
        if ($latest_version && version_compare($this->current_version, $latest_version, '<')) {
            $plugin_data = [
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_slug,
                'new_version' => $latest_version,
                'url' => $this->github_repo_url,
                'package' => $this->get_download_url($latest_version),
            ];
            
            $transient->response[$this->plugin_slug] = (object) $plugin_data;
        }

        return $transient;
    }

    /**
     * Pobiera najnowszą wersję z GitHub Releases
     */
    private function get_latest_version() {
        // Sprawdź cache
        $cached_version = get_transient($this->version_cache_key);
        if ($cached_version !== false) {
            return $cached_version;
        }

        $api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        );

        $response = wp_remote_get($api_url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress-Plugin-Updater',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['tag_name'])) {
            return false;
        }

        // Usuń 'v' z początku tagu jeśli istnieje
        $version = ltrim($data['tag_name'], 'v');
        
        // Zapisz w cache
        set_transient($this->version_cache_key, $version, $this->version_cache_time);

        return $version;
    }

    /**
     * Pobiera URL do pobrania wersji (zip z GitHub)
     */
    private function get_download_url($version) {
        // GitHub Releases - pobierz zip z tagu
        // Format: https://github.com/username/repo/archive/refs/tags/v1.0.0.zip
        // Lub: https://github.com/username/repo/archive/refs/tags/1.0.0.zip
        $tag = 'v' . $version;
        
        // Sprawdź czy tag z 'v' istnieje, jeśli nie użyj bez 'v'
        $url_with_v = sprintf(
            'https://github.com/%s/%s/archive/refs/tags/v%s.zip',
            $this->github_username,
            $this->github_repo,
            $version
        );
        
        $url_without_v = sprintf(
            'https://github.com/%s/%s/archive/refs/tags/%s.zip',
            $this->github_username,
            $this->github_repo,
            $version
        );
        
        // Zwróć URL z 'v' (standardowy format GitHub Releases)
        return $url_with_v;
    }

    /**
     * Zwraca informacje o wtyczce dla WordPress
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        // Jeśli updater nie jest skonfigurowany, zwróć domyślny wynik
        if (empty($this->github_username) || empty($this->github_repo)) {
            return $result;
        }

        $latest_version = $this->get_latest_version();
        
        if (!$latest_version) {
            return $result;
        }

        // Pobierz szczegóły release z GitHub
        $api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        );

        $response = wp_remote_get($api_url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress-Plugin-Updater',
            ],
        ]);

        if (is_wp_error($response)) {
            return $result;
        }

        $body = wp_remote_retrieve_body($response);
        $release_data = json_decode($body, true);

        // Przygotuj dane dla WordPress
        $plugin_info = [
            'name' => 'Alex EFPP - Elementor Form Publish Post',
            'slug' => $this->plugin_slug,
            'version' => ltrim($release_data['tag_name'] ?? $latest_version, 'v'),
            'author' => '<a href="' . esc_url($this->github_repo_url) . '">Alex Scar</a>',
            'homepage' => $this->github_repo_url,
            'requires' => '5.0',
            'tested' => get_bloginfo('version'),
            'last_updated' => isset($release_data['published_at']) ? date('Y-m-d', strtotime($release_data['published_at'])) : '',
            'download_link' => $this->get_download_url($latest_version),
            'sections' => [
                'description' => isset($release_data['body']) ? $this->parse_markdown($release_data['body']) : 'Aktualizacja dostępna z GitHub.',
                'changelog' => isset($release_data['body']) ? $this->parse_markdown($release_data['body']) : '',
            ],
        ];

        return (object) $plugin_info;
    }

    /**
     * Konwertuje Markdown na HTML (podstawowa konwersja)
     */
    private function parse_markdown($text) {
        if (empty($text)) {
            return '';
        }

        // Podstawowa konwersja Markdown do HTML
        $text = esc_html($text);
        
        // Nagłówki
        $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
        
        // Listy
        $text = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
        
        // Linki
        $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);
        
        // Paragrafy
        $text = '<p>' . str_replace("\n\n", '</p><p>', $text) . '</p>';
        
        return wp_kses_post($text);
    }

    /**
     * Po instalacji - zmień nazwę folderu na właściwą
     */
    public function post_install($response, $hook_extra, $result) {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_slug) {
            return $response;
        }

        global $wp_filesystem;
        
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->plugin_slug);
        $temp_folder = $result['destination'];
        
        // GitHub zwraca zip z nazwą repo-version, więc folder może mieć inną nazwę
        // Sprawdź czy folder docelowy istnieje i ma właściwą nazwę
        if ($temp_folder !== $plugin_folder && $wp_filesystem->exists($temp_folder)) {
            // Pobierz listę plików z folderu tymczasowego
            $files = $wp_filesystem->dirlist($temp_folder);
            
            if ($files) {
                // Jeśli folder docelowy istnieje, usuń go
                if ($wp_filesystem->exists($plugin_folder)) {
                    $wp_filesystem->rmdir($plugin_folder, true);
                }
                
                // Przenieś pliki z folderu tymczasowego do docelowego
                $wp_filesystem->move($temp_folder, $plugin_folder);
                $result['destination'] = $plugin_folder;
            }
        }

        // Aktywuj wtyczkę jeśli była aktywna przed aktualizacją
        if (is_plugin_active($this->plugin_slug)) {
            activate_plugin($this->plugin_slug);
        }

        return $result;
    }

    /**
     * Czyści cache po aktualizacji
     */
    public function clear_version_cache($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            delete_transient($this->version_cache_key);
        }
    }

    /**
     * Ustawia URL repozytorium GitHub (do konfiguracji)
     */
    public function set_github_repo($url) {
        $this->github_repo_url = $url;
        $this->parse_github_url();
    }
    
    /**
     * Dodaje link "Sprawdź aktualizacje" w wierszu wtyczki
     */
    public function add_check_update_link($links, $file) {
        if ($file !== $this->plugin_slug) {
            return $links;
        }
        
        // Jeśli updater nie jest skonfigurowany, nie dodawaj linku
        if (empty($this->github_username) || empty($this->github_repo)) {
            return $links;
        }
        
        $check_url = wp_nonce_url(
            admin_url('admin-ajax.php?action=alex_efpp_check_update'),
            'alex_efpp_check_update',
            'nonce'
        );
        
        $links[] = sprintf(
            '<a href="%s" class="alex-efpp-check-update" data-plugin="%s">%s</a>',
            esc_url($check_url),
            esc_attr($this->plugin_slug),
            esc_html__('Sprawdź aktualizacje', 'alex-efpp')
        );
        
        return $links;
    }
    
    /**
     * AJAX handler do wymuszenia sprawdzenia aktualizacji
     */
    public function ajax_check_update() {
        check_ajax_referer('alex_efpp_check_update', 'nonce');
        
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(['message' => __('Brak uprawnień do sprawdzania aktualizacji.', 'alex-efpp')]);
        }
        
        // Wyczyść cache
        delete_transient($this->version_cache_key);
        delete_site_transient('update_plugins');
        
        // Wymuś sprawdzenie aktualizacji
        $latest_version = $this->get_latest_version();
        
        if (!$latest_version) {
            wp_send_json_error([
                'message' => __('Nie udało się sprawdzić aktualizacji. Sprawdź konfigurację repozytorium GitHub.', 'alex-efpp')
            ]);
        }
        
        $has_update = version_compare($this->current_version, $latest_version, '<');
        
        if ($has_update) {
            wp_send_json_success([
                'message' => sprintf(
                    __('Dostępna jest nowa wersja: %s (aktualna: %s). Odśwież stronę, aby zobaczyć przycisk aktualizacji.', 'alex-efpp'),
                    $latest_version,
                    $this->current_version
                ),
                'latest_version' => $latest_version,
                'current_version' => $this->current_version,
            ]);
        } else {
            wp_send_json_success([
                'message' => sprintf(
                    __('Wtyczka jest aktualna. Aktualna wersja: %s', 'alex-efpp'),
                    $this->current_version
                ),
                'latest_version' => $latest_version,
                'current_version' => $this->current_version,
            ]);
        }
    }
}

