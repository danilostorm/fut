<?php
/**
 * Plugin Name: Hermes Connector
 * Description: API REST completa para controle total do WordPress via agentes de IA
 * Version: 1.0.0
 * Author: HostStorm
 * Author URI: https://hoststorm.net
 * Text Domain: hermes-connector
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 */

// Prohibit direct access
if (!defined('ABSPATH')) exit;

// ============================================================
// CONFIGURATION
// ============================================================
define('HERMES_VERSION', '1.0.0');
define('HERMES_API_KEY', 'hmsk_hermes2026stormarenaX9z'); // CHANGE THIS
define('HERMES_API_NAMESPACE', 'hermes/v1');
define('HERMES_LOG_FILE', WP_CONTENT_DIR . '/hermes-connector.log');

// ============================================================
// HELPERS
// ============================================================
function hermes_auth($request) {
    $key = $request->get_header('X-Hermes-Key');
    if (!$key || $key !== HERMES_API_KEY) {
        return new WP_Error(
            'unauthorized',
            'API key inválida ou não fornecida. Use header: X-Hermes-Key: ' . HERMES_API_KEY,
            ['status' => 401]
        );
    }
    return true;
}

function hermes_json($data, $status = 200) {
    return new WP_REST_Response($data, $status);
}

function hermes_error($message, $code = 'error', $status = 400) {
    return new WP_Error($code, $message, ['status' => $status]);
}

function hermes_log($message, $level = 'INFO') {
    $entry = date('Y-m-d H:i:s') . " [$level] $message\n";
    @file_put_contents(HERMES_LOG_FILE, $entry, FILE_APPEND);
}

// ============================================================
// 1. HEALTH CHECK
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/health', [
        'methods' => 'GET',
        'callback' => function ($request) {
            hermes_log("Health check OK");
            return hermes_json([
                'status' => 'ok',
                'plugin' => 'Hermes Connector',
                'version' => HERMES_VERSION,
                'site' => get_bloginfo('name'),
                'url' => home_url(),
                'wp_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'authenticated' => hermes_auth($request) === true,
                'endpoints' => [
                    'health', 'posts', 'posts/<id>', 'posts/create',
                    'media', 'media/upload', 'categories', 'tags',
                    'pages', 'pages/<id>', 'themes', 'themes/install',
                    'themes/activate', 'plugins', 'plugins/install',
                    'plugins/activate', 'plugins/deactivate', 'options',
                    'users', 'search', 'comments', 'settings',
                    'system', 'dashboard', 'security/salt'
                ]
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
});

// ============================================================
// 2. POSTS
// ============================================================
add_action('rest_api_init', function () {
    // List posts
    register_rest_route(HERMES_API_NAMESPACE, '/posts', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $args = [
                'post_type' => 'post',
                'posts_per_page' => $request->get_param('per_page') ?? 20,
                'paged' => $request->get_param('page') ?? 1,
                'post_status' => $request->get_param('status') ?? 'publish',
                's' => $request->get_param('search') ?? '',
                'orderby' => $request->get_param('orderby') ?? 'date',
                'order' => $request->get_param('order') ?? 'DESC',
            ];

            if ($cat = $request->get_param('category')) {
                $args['cat'] = is_numeric($cat) ? (int)$cat : get_cat_ID($cat);
            }

            $query = new WP_Query($args);
            $posts = [];

            foreach ($query->posts as $p) {
                $posts[] = [
                    'id' => $p->ID,
                    'title' => html_entity_decode(get_the_title($p)),
                    'slug' => $p->post_name,
                    'status' => $p->post_status,
                    'date' => $p->post_date,
                    'modified' => $p->post_modified,
                    'author' => get_the_author_meta('display_name', $p->post_author),
                    'excerpt' => get_the_excerpt($p),
                    'word_count' => str_word_count(strip_tags($p->post_content)),
                    'comment_count' => $p->comment_count,
                    'categories' => wp_get_post_categories($p->ID, ['fields' => 'names']),
                    'tags' => wp_get_post_tags($p->ID, ['fields' => 'names']),
                    'featured_image' => get_the_post_thumbnail_url($p->ID, 'full'),
                    'link' => get_permalink($p),
                    'seo_title' => get_post_meta($p->ID, '_yoast_wpseo_title', true) ?: '',
                    'seo_desc' => get_post_meta($p->ID, '_yoast_wpseo_metadesc', true) ?: '',
                ];
            }

            hermes_log("Listou {$query->found_posts} posts");
            return hermes_json([
                'posts' => $posts,
                'total' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
                'current_page' => $args['paged'],
            ]);
        },
        'permission_callback' => function ($request) {
            $auth = hermes_auth($request);
            return $auth === true;
        },
    ]);

    // Get single post
    register_rest_route(HERMES_API_NAMESPACE, '/posts/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $id = (int)$request->get_param('id');
            $p = get_post($id);
            if (!$p) return hermes_error('Post não encontrado', 'not_found', 404);

            return hermes_json([
                'id' => $p->ID,
                'title' => html_entity_decode(get_the_title($p)),
                'slug' => $p->post_name,
                'content' => $p->post_content,
                'status' => $p->post_status,
                'date' => $p->post_date,
                'modified' => $p->post_modified,
                'author' => get_the_author_meta('display_name', $p->post_author),
                'excerpt' => $p->post_excerpt,
                'categories' => wp_get_post_categories($p->ID),
                'tags' => wp_get_post_tags($p->ID),
                'featured_image' => get_the_post_thumbnail_url($p->ID, 'full'),
                'link' => get_permalink($p),
                'acf' => function_exists('get_fields') ? get_fields($p->ID) : [],
                'seo_title' => get_post_meta($p->ID, '_yoast_wpseo_title', true) ?: '',
                'seo_desc' => get_post_meta($p->ID, '_yoast_wpseo_metadesc', true) ?: '',
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    // Update post
    register_rest_route(HERMES_API_NAMESPACE, '/posts/(?P<id>\d+)', [
        'methods' => ['POST', 'PUT', 'PATCH'],
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $id = (int)$request->get_param('id');
            $p = get_post($id);
            if (!$p) return hermes_error('Post não encontrado', 'not_found', 404);

            $data = $request->get_json_params();
            $post_data = ['ID' => $id];

            $fields_map = [
                'title' => 'post_title',
                'content' => 'post_content',
                'excerpt' => 'post_excerpt',
                'status' => 'post_status',
                'slug' => 'post_name',
                'date' => 'post_date',
                'author' => 'post_author',
            ];

            foreach ($fields_map as $key => $wp_field) {
                if (isset($data[$key])) {
                    if ($key === 'title') $data[$key] = wp_strip_all_tags($data[$key]);
                    if ($key === 'author') {
                        if (is_numeric($data[$key])) $post_data['post_author'] = (int)$data[$key];
                        continue;
                    }
                    if (isset($data[$key])) $post_data[$wp_field] = $data[$key];
                }
            }

            $result = wp_update_post($post_data, true);

            if (is_wp_error($result)) return $result;

            // Categories
            if (isset($data['categories'])) {
                $cat_ids = [];
                foreach ((array)$data['categories'] as $cat) {
                    if (is_numeric($cat)) {
                        $cat_ids[] = (int)$cat;
                    } else {
                        $cat_ids[] = get_cat_ID($cat);
                    }
                }
                wp_set_post_categories($id, $cat_ids);
            }

            // Tags
            if (isset($data['tags'])) {
                wp_set_post_tags($id, (array)$data['tags']);
            }

            // Featured image
            if (!empty($data['featured_image_url'])) {
                hermes_set_featured_image($id, $data['featured_image_url']);
            }

            // SEO
            if (isset($data['seo_title'])) {
                update_post_meta($id, '_yoast_wpseo_title', sanitize_text_field($data['seo_title']));
            }
            if (isset($data['seo_desc'])) {
                update_post_meta($id, '_yoast_wpseo_metadesc', sanitize_text_field($data['seo_desc']));
            }

            hermes_log("Post $id atualizado");
            return hermes_json(['success' => true, 'post_id' => $id, 'updated' => true]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    // Delete post
    register_rest_route(HERMES_API_NAMESPACE, '/posts/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $id = (int)$request->get_param('id');
            $result = wp_delete_post($id, $force = false);

            if (!$result) return hermes_error('Post não pode ser excluído', 'delete_failed', 400);

            hermes_log("Post $id deletado");
            return hermes_json(['success' => true, 'deleted' => $id]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    // Create post
    register_rest_route(HERMES_API_NAMESPACE, '/posts/create', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();

            $post_data = [
                'post_type' => !empty($data['type']) ? sanitize_key($data['type']) : 'post',
                'post_title' => isset($data['title']) ? wp_strip_all_tags($data['title']) : 'Sem título',
                'post_content' => $data['content'] ?? '',
                'post_excerpt' => $data['excerpt'] ?? '',
                'post_status' => $data['status'] ?? 'draft',
                'post_date' => !empty($data['date']) ? sanitize_text_field($data['date']) : current_time('mysql'),
                'post_name' => !empty($data['slug']) ? sanitize_title($data['slug']) : '',
            ];

            if (!empty($data['author'])) {
                $post_data['post_author'] = is_numeric($data['author']) ? (int)$data['author'] : 1;
            }

            $id = wp_insert_post($post_data, true);

            if (is_wp_error($id)) return $id;

            // Categories
            if (isset($data['categories'])) {
                $cat_ids = [];
                foreach ((array)$data['categories'] as $cat) {
                    $cat_ids[] = is_numeric($cat) ? (int)$cat : get_cat_ID($cat);
                }
                wp_set_post_categories($id, $cat_ids);
            }

            // Tags
            if (isset($data['tags'])) {
                wp_set_post_tags($id, (array)$data['tags']);
            }

            // Featured image
            if (!empty($data['featured_image_url'])) {
                hermes_set_featured_image($id, $data['featured_image_url']);
            }

            // SEO
            if (!empty($data['seo_title'])) {
                update_post_meta($id, '_yoast_wpseo_title', sanitize_text_field($data['seo_title']));
            }
            if (!empty($data['seo_desc'])) {
                update_post_meta($id, '_yoast_wpseo_metadesc', sanitize_text_field($data['seo_desc']));
            }

            hermes_log("Post $id criado: {$post_data['post_title']}");
            return hermes_json([
                'success' => true,
                'post_id' => $id,
                'url' => get_permalink($id),
            ], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 3. MEDIA
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/media', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $query = new WP_Query([
                'post_type' => 'attachment',
                'posts_per_page' => $request->get_param('per_page') ?? 20,
                'paged' => $request->get_param('page') ?? 1,
                'post_status' => 'inherit',
                's' => $request->get_param('search') ?? '',
            ]);

            $media = [];
            foreach ($query->posts as $m) {
                $media[] = [
                    'id' => $m->ID,
                    'title' => $m->post_title,
                    'url' => wp_get_attachment_url($m->ID),
                    'thumbnail' => wp_get_attachment_thumbnail_url($m->ID),
                    'mime_type' => $m->post_mime_type,
                    'file' => get_attached_file($m->ID),
                    'date' => $m->post_date,
                    'size' => filesize(get_attached_file($m->ID)),
                ];
            }

            return hermes_json([
                'media' => $media,
                'total' => $query->found_posts,
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/media/upload', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $params = $request->get_json_params();
            $image_url = $params['url'] ?? '';
            $filename = $params['filename'] ?? '';

            if (empty($image_url)) {
                return hermes_error('URL da imagem é obrigatória', 'missing_url', 400);
            }

            // Download image
            $response = wp_remote_get($image_url, ['timeout' => 30]);
            if (is_wp_error($response)) {
                return hermes_error('Falha ao baixar imagem: ' . $response->get_error_message(), 'download_failed', 500);
            }

            $body = wp_remote_retrieve_body($response);
            $type = wp_remote_retrieve_header($response, 'content-type');
            $ext = hermes_get_ext_from_mime($type);
            $filename = $filename ?: md5($image_url) . ".$ext";

            // Upload via WP filesystem
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $upload = wp_upload_bits($filename, null, $body);
            if ($upload['error']) {
                return hermes_error('Upload falhou: ' . $upload['error'], 'upload_failed', 500);
            }

            $attachment = [
                'post_mime_type' => $type,
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit',
            ];

            $attach_id = wp_insert_attachment($attachment, $upload['file']);

            if (!is_wp_error($attach_id)) {
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }

            hermes_log("Media $attach_id upload: $filename");
            return hermes_json([
                'success' => true,
                'id' => $attach_id,
                'url' => wp_get_attachment_url($attach_id),
                'file' => $upload['file'],
            ], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 4. CATEGORIES & TAGS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/categories', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $cats = get_categories(['hide_empty' => false]);
            return hermes_json([
                'categories' => array_map(fn($c) => [
                    'id' => $c->term_id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'count' => $c->count,
                    'parent' => $c->parent,
                    'description' => $c->description,
                ], $cats),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/categories/create', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();
            $result = wp_insert_term(
                $data['name'],
                'category',
                ['description' => $data['description'] ?? '', 'parent' => $data['parent'] ?? 0, 'slug' => $data['slug'] ?? '']
            );

            if (is_wp_error($result)) return $result;

            hermes_log("Categoria criada: {$data['name']}");
            return hermes_json(['success' => true, 'id' => $result['term_id']], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/tags', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $tags = get_tags(['hide_empty' => false]);
            return hermes_json([
                'tags' => array_map(fn($t) => ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'count' => $t->count], $tags),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/tags/create', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();
            $result = wp_insert_term($data['name'], 'post_tag', ['slug' => $data['slug'] ?? '']);

            if (is_wp_error($result)) return $result;

            hermes_log("Tag criada: {$data['name']}");
            return hermes_json(['success' => true, 'id' => $result['term_id']], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 5. PAGES
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/pages', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $query = new WP_Query(['post_type' => 'page', 'posts_per_page' => 100, 'post_status' => 'any']);
            $pages = [];
            foreach ($query->posts as $p) {
                $pages[] = [
                    'id' => $p->ID,
                    'title' => html_entity_decode(get_the_title($p)),
                    'slug' => $p->post_name,
                    'status' => $p->post_status,
                    'link' => get_permalink($p),
                ];
            }
            return hermes_json(['pages' => $pages]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/pages/(?P<id>\d+)', [
        'methods' => ['GET', 'POST', 'PUT', 'PATCH'],
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $id = (int)$request->get_param('id');
            $p = get_post($id);
            if (!$p) return hermes_error('Página não encontrada', 'not_found', 404);

            if ($request->get_method() === 'GET') {
                return hermes_json([
                    'id' => $p->ID,
                    'title' => html_entity_decode(get_the_title($p)),
                    'content' => $p->post_content,
                    'status' => $p->post_status,
                    'slug' => $p->post_name,
                    'link' => get_permalink($p),
                ]);
            }

            $data = $request->get_json_params();
            $post_data = ['ID' => $id];
            if (isset($data['title'])) $post_data['post_title'] = wp_strip_all_tags($data['title']);
            if (isset($data['content'])) $post_data['post_content'] = $data['content'];
            if (isset($data['status'])) $post_data['post_status'] = $data['status'];
            if (isset($data['slug'])) $post_data['post_name'] = sanitize_title($data['slug']);

            $result = wp_update_post($post_data, true);
            if (is_wp_error($result)) return $result;

            hermes_log("Página $id atualizada");
            return hermes_json(['success' => true, 'page_id' => $id]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/pages/create', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();
            $id = wp_insert_post([
                'post_type' => 'page',
                'post_title' => isset($data['title']) ? wp_strip_all_tags($data['title']) : 'Nova Página',
                'post_content' => $data['content'] ?? '',
                'post_status' => $data['status'] ?? 'draft',
                'post_name' => !empty($data['slug']) ? sanitize_title($data['slug']) : '',
            ], true);

            if (is_wp_error($id)) return $id;

            hermes_log("Página $id criada");
            return hermes_json(['success' => true, 'id' => $id, 'url' => get_permalink($id)], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 6. THEMES
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/themes', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $themes = wp_get_themes();
            $active = wp_get_theme();

            return hermes_json([
                'active' => [
                    'name' => $active->get('Name'),
                    'version' => $active->get('Version'),
                    'stylesheet' => $active->get_stylesheet(),
                ],
                'themes' => array_map(fn($t) => [
                    'name' => $t->get('Name'),
                    'version' => $t->get('Version'),
                    'stylesheet' => $t->get_stylesheet(),
                    'active' => $t->get_stylesheet() === $active->get_stylesheet(),
                ], array_values($themes)),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/themes/activate', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!current_user_can('switch_themes')) {
                return hermes_error('Sem permissão para trocar tema', 'forbidden', 403);
            }

            $data = $request->get_json_params();
            $stylesheet = $data['stylesheet'] ?? '';

            if (empty($stylesheet)) return hermes_error('Stylesheet obrigatório', 'missing', 400);

            switch_theme($stylesheet);
            hermes_log("Tema ativado: $stylesheet");

            return hermes_json(['success' => true, 'activated' => $stylesheet]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/themes/install', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!current_user_can('install_themes')) {
                return hermes_error('Sem permissão para instalar temas', 'forbidden', 403);
            }

            $data = $request->get_json_params();
            $url = $data['url'] ?? '';
            $zip_url = $data['zip_url'] ?? '';

            if (empty($zip_url)) return hermes_error('zip_url obrigatório', 'missing', 400);

            // Download theme
            $zip_path = download_url($zip_url, 120);
            if (is_wp_error($zip_path)) {
                return hermes_error('Download falhou: ' . $zip_path->get_error_message(), 'download_failed', 500);
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/theme.php';

            $result = unzip_file($zip_path, get_theme_root());
            @unlink($zip_path);

            if (is_wp_error($result)) {
                return hermes_error('Unzip falhou: ' . $result->get_error_message(), 'unzip_failed', 500);
            }

            hermes_log("Tema instalado de: $zip_url");
            return hermes_json(['success' => true, 'message' => 'Tema instalado com sucesso']);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 7. PLUGINS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/plugins', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();
            $active = get_option('active_plugins', []);

            return hermes_json([
                'plugins' => array_map(function ($p, $f) use ($active) {
                    return [
                        'name' => $p['Name'],
                        'version' => $p['Version'],
                        'file' => $f,
                        'active' => in_array($f, $active),
                    ];
                }, $plugins, array_keys($plugins)),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/plugins/activate', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!current_user_can('activate_plugins')) {
                return hermes_error('Sem permissão', 'forbidden', 403);
            }

            $data = $request->get_json_params();
            $plugin = $data['plugin'] ?? '';

            if (empty($plugin)) return hermes_error('Plugin é obrigatório', 'missing', 400);

            $result = activate_plugin($plugin);
            if (is_wp_error($result)) return $result;

            hermes_log("Plugin ativado: $plugin");
            return hermes_json(['success' => true, 'activated' => $plugin]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/plugins/deactivate', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!current_user_can('deactivate_plugins')) {
                return hermes_error('Sem permissão', 'forbidden', 403);
            }

            $data = $request->get_json_params();
            $plugin = $data['plugin'] ?? '';

            deactivate_plugins($plugin);
            hermes_log("Plugin desativado: $plugin");

            return hermes_json(['success' => true, 'deactivated' => $plugin]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/plugins/install', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            if (!current_user_can('install_plugins')) {
                return hermes_error('Sem permissão para instalar plugins', 'forbidden', 403);
            }

            $data = $request->get_json_params();
            $zip_url = $data['zip_url'] ?? '';

            if (empty($zip_url)) return hermes_error('zip_url obrigatório', 'missing', 400);

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $zip_path = download_url($zip_url, 120);
            if (is_wp_error($zip_path)) {
                return hermes_error('Download falhou: ' . $zip_path->get_error_message(), 'download_failed', 500);
            }

            $result = unzip_file($zip_path, WP_PLUGIN_DIR);
            @unlink($zip_path);

            if (is_wp_error($result)) {
                return hermes_error('Unzip falhou: ' . $result->get_error_message(), 'unzip_failed', 500);
            }

            hermes_log("Plugin instalado de: $zip_url");
            return hermes_json(['success' => true, 'message' => 'Plugin instalado com sucesso']);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 8. COMMENTS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/comments', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $comments = get_comments([
                'number' => $request->get_param('per_page') ?? 20,
                'status' => $request->get_param('status') ?? 'approve',
                'post_id' => $request->get_param('post_id') ?? 0,
            ]);

            return hermes_json([
                'comments' => array_map(fn($c) => [
                    'id' => $c->comment_ID,
                    'post_id' => $c->comment_post_ID,
                    'author' => $c->comment_author,
                    'content' => $c->comment_content,
                    'date' => $c->comment_date,
                    'approved' => $c->comment_approved === '1',
                ], $comments),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/comments/create', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();
            $result = wp_insert_comment([
                'comment_post_ID' => (int)$data['post_id'],
                'comment_content' => sanitize_textarea_field($data['content']),
                'comment_author' => $data['author'] ?? 'Hermes',
                'comment_author_email' => $data['email'] ?? 'hermes@hoststorm.net',
                'comment_approved' => $data['approved'] ?? 1,
            ]);

            if (is_wp_error($result)) return $result;

            hermes_log("Comentário criado no post {$data['post_id']}");
            return hermes_json(['success' => true, 'id' => $result], 201);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 9. OPTIONS & SETTINGS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/options', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $option = $request->get_param('name');
            if ($option) {
                $value = get_option($option);
                return hermes_json(['name' => $option, 'value' => $value]);
            }

            // Return all public options
            global $wpdb;
            $rows = $wpdb->get_results("SELECT option_name FROM {$wpdb->options} WHERE autoload='yes' LIMIT 200");
            return hermes_json(['options' => array_column($rows, 'option_name')]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);

    register_rest_route(HERMES_API_NAMESPACE, '/options/update', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $data = $request->get_json_params();
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? '';
            $autoload = $data['autoload'] ?? 'yes';

            if (empty($name)) return hermes_error('name é obrigatório', 'missing', 400);

            update_option($name, $value, $autoload);
            hermes_log("Option atualizada: $name");

            return hermes_json(['success' => true, 'name' => $name]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 10. USERS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/users', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $users = get_users(['number' => 100]);
            return hermes_json([
                'users' => array_map(fn($u) => [
                    'id' => $u->ID,
                    'login' => $u->user_login,
                    'email' => $u->user_email,
                    'name' => $u->display_name,
                    'roles' => $u->roles,
                    'registered' => $u->user_registered,
                ], $users),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 11. SEARCH
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/search', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $q = $request->get_param('q') ?? '';
            $type = $request->get_param('type') ?? 'all';

            if (empty($q)) return hermes_error('q (query) é obrigatória', 'missing', 400);

            $results = hermes_search($q);

            return hermes_json([
                'query' => $q,
                'results' => array_map(fn($p) => [
                    'id' => $p->ID,
                    'title' => html_entity_decode(get_the_title($p)),
                    'type' => $p->post_type,
                    'link' => get_permalink($p),
                ], $results),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 12. SETTINGS
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/settings', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            return hermes_json([
                'blogname' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
                'wpurl' => get_bloginfo('wpurl'),
                'admin_email' => get_option('admin_email'),
                'permalink' => get_option('permalink_structure'),
                'timezone' => wp_timezone_string(),
                'posts_per_page' => get_option('posts_per_page'),
                'users_can_register' => get_option('users_can_register'),
                'default_role' => get_option('default_role'),
                'home' => get_option('home'),
                'siteurl' => get_option('siteurl'),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 13. SYSTEM INFO
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/system', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            global $wpdb;
            $db_size = $wpdb->get_row("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = DATABASE()");

            return hermes_json([
                'php_version' => PHP_VERSION,
                'php_memory_limit' => ini_get('memory_limit'),
                'php_max_execution_time' => ini_get('max_execution_time'),
                'wp_version' => get_bloginfo('version'),
                'db_size_mb' => $db_size->size ?? 'unknown',
                'uploads_dir' => wp_upload_dir()['basedir'],
                'disk_free' => @disk_free_space('/') ? round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . ' GB' : 'unknown',
                'active_theme' => wp_get_theme()->get_stylesheet(),
                'plugin_count' => count(get_plugins()),
                'post_count' => wp_count_posts('post')->publish,
                'page_count' => wp_count_posts('page')->publish,
                'comment_count' => wp_count_comments()->approved,
                'user_count' => count_users()['total_users'],
                'php_extensions' => [
                    'curl' => function_exists('curl_init'),
                    'gd' => function_exists('imagecreatetruecolor'),
                    'mbstring' => function_exists('mb_send_mail'),
                    'zip' => class_exists('ZipArchive'),
                    'openssl' => function_exists('openssl_encrypt'),
                ],
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 14. DASHBOARD
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/dashboard', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            global $wpdb;

            $recent_posts = wp_get_recent_posts(['numberposts' => 5]);
            $popular_posts = $wpdb->get_results(
                "SELECT ID, post_title, post_date, comment_count FROM {$wpdb->posts} 
                 WHERE post_status='publish' AND post_type='post' 
                 ORDER BY comment_count DESC LIMIT 5"
            );
            $recent_comments = get_comments(['number' => 5, 'status' => 'approve']);

            return hermes_json([
                'site' => get_bloginfo('name'),
                'url' => home_url(),
                'posts' => [
                    'total' => wp_count_posts('post')->publish,
                    'draft' => wp_count_posts('post')->draft,
                ],
                'pages' => ['total' => wp_count_posts('page')->publish],
                'comments' => [
                    'approved' => wp_count_comments()->approved,
                    'pending' => wp_count_comments()->moderated,
                    'spam' => wp_count_comments()->spam,
                ],
                'categories' => wp_count_terms('category'),
                'tags' => wp_count_terms('post_tag'),
                'users' => count_users()['total_users'],
                'recent_posts' => array_map(fn($p) => [
                    'id' => $p['ID'],
                    'title' => html_entity_decode($p['post_title']),
                    'date' => $p['post_date'],
                ], $recent_posts),
                'popular_posts' => array_map(fn($p) => [
                    'id' => $p->ID,
                    'title' => html_entity_decode($p->post_title),
                    'comments' => $p->comment_count,
                ], $popular_posts),
                'recent_comments' => array_map(fn($c) => [
                    'author' => $c->comment_author,
                    'content' => wp_trim_words($c->comment_content, 10),
                    'date' => $c->comment_date,
                ], $recent_comments),
                'active_theme' => wp_get_theme()->get('Name'),
                'active_plugins' => get_option('active_plugins'),
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// 15. SECURITY — GENERATE SALT
// ============================================================
add_action('rest_api_init', function () {
    register_rest_route(HERMES_API_NAMESPACE, '/security/salt', [
        'methods' => 'GET',
        'callback' => function ($request) {
            $auth = hermes_auth($request);
            if (is_wp_error($auth)) return $auth;

            $salts = [
                'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
                'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
            ];

            $result = [];
            foreach ($salts as $salt_name) {
                $result[$salt_name] = wp_generate_password(64, true, true);
            }

            return hermes_json([
                'salts' => $result,
                'instructions' => 'Cole estas definições no seu wp-config.php, substituindo as linhas existentes.',
            ]);
        },
        'permission_callback' => function ($request) {
            return hermes_auth($request) === true;
        },
    ]);
});

// ============================================================
// HELPER FUNCTIONS
// ============================================================
function hermes_get_ext_from_mime($type) {
    $map = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
        'image/webp' => 'webp', 'image/svg+xml' => 'svg', 'image/avif' => 'avif',
        'video/mp4' => 'mp4', 'video/webm' => 'webm', 'application/pdf' => 'pdf',
    ];
    return $map[$type] ?? 'bin';
}

function hermes_search($q) {
    return get_posts([
        's' => $q,
        'posts_per_page' => 20,
        'post_status' => 'publish',
    ]);
}

function hermes_set_featured_image($post_id, $image_url) {
    // Download image
    $image_data = file_get_contents($image_url);
    if (!$image_data) return false;

    $ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $filename = "featured-$post_id.$ext";
    $upload = wp_upload_bits($filename, null, $image_data);
    if ($upload['error']) return false;

    $wp_filetype = wp_check_filetype($upload['file']);
    $attachment = [
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => get_the_title($post_id),
        'post_content' => '',
        'post_status' => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
    if (!is_wp_error($attach_id)) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }
    return $attach_id;
}

// ============================================================
// ACTIVATION / DEACTIVATION
// ============================================================
register_activation_hook(__FILE__, function () {
    hermes_log("Plugin ativado", 'INFO');
});

register_deactivation_hook(__FILE__, function () {
    hermes_log("Plugin desativado", 'INFO');
});
