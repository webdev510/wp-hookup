<?php

class MPG_CoreModel
{
    public static function mpg_get_all_tepmlates_ids()
    {
        global $wpdb;

        $cache = wp_cache_get('get_all_tepmlates_ids');
        if ($cache) {
            return $cache;
        }

        $templates_ids = [];

        $all_projects_data = $wpdb->get_results("SELECT template_id FROM " . $wpdb->prefix . MPG_Constant::MPG_PROJECTS_TABLE);


        if ($all_projects_data) {
            foreach ($all_projects_data as $project_object) {

                if ((int) $project_object->template_id) {
                    $templates_ids[] = (int) $project_object->template_id;
                }
            }
        }

        wp_cache_add('get_all_tepmlates_ids', $templates_ids);
        return $templates_ids;
    }

    // replace shortcodes in head section if exist
    public static function multipage_replace_data($html)
    {

        $path = MPG_Helper::mpg_get_request_uri();
        $metadata_array = self::mpg_get_redirect_rules($path);

        $template_post = get_post($metadata_array['template_id']);
        $current_post = get_post();

        if ($template_post->ID == $current_post->ID) {

            $project_id = $metadata_array['project_id'];
            return self::mpg_shortcode_replacer($html, $project_id);
        }
    }


    public static function mpg_get_redirect_rules($needed_path)
    {

        global $wpdb;
        // array of multi URLs
        $redirect_rules = [];
        $projects = $wpdb->get_results("SELECT id, template_id, url_mode, apply_condition, urls_array FROM " .  $wpdb->prefix . MPG_Constant::MPG_PROJECTS_TABLE);

        $is_pro = mpg_app()->is_premium();

        foreach ($projects as $project) {
            if ($project->urls_array) {

                $url_match_condition = false;

                foreach (json_decode($project->urls_array) as $iteration => $raw_single_url) {
                    $single_url = urldecode($raw_single_url);

                    switch ($project->url_mode) {
                        case 'with-trailing-slash':
                            if (!MPG_Helper::mpg_string_end_with($_SERVER['REQUEST_URI'], '/')) {
                                if ($single_url === $needed_path) {
                                    wp_safe_redirect($_SERVER['REQUEST_URI'] . '/', 302);
                                    break;
                                }
                            }

                            $url_match_condition = defined('AMPFORWP_VERSION') ? in_array($needed_path, [$single_url, $single_url . 'amp/']) : $single_url === $needed_path;
                            if ($url_match_condition) {
                                define('MPG_IS_AMP_PAGE', $needed_path === ($single_url . 'amp/')); // Set  true\false to constant if requested page is AMP
                            }
                            break;

                        case 'without-trailing-slash':
                            if (MPG_Helper::mpg_string_end_with($_SERVER['REQUEST_URI'], '/')) {
                                if ($single_url === $needed_path) {
                                    wp_safe_redirect(rtrim($_SERVER['REQUEST_URI'], '/'), 302);
                                    break;
                                }
                            }

                            // Вот эта часть 'amp/' - логически не очень точна, т.к. по условию, тут не надо слеша вконце, но поскольку $needed_path всегда приходит со слешем,
                            // то приходится придумывать обходной путь, но вообще это надо переделать.
                            $url_match_condition = defined('AMPFORWP_VERSION') ? in_array($needed_path, [$single_url, $single_url . 'amp/']) : $single_url === $needed_path;
                            if ($url_match_condition) {
                                define('MPG_IS_AMP_PAGE', $needed_path === ($single_url . 'amp/'));
                            }
                            break;

                        default: //both
                            $url_match_condition = defined('AMPFORWP_VERSION') ? in_array($needed_path, [$single_url, $single_url . 'amp', $single_url . 'amp/']) : $single_url === $needed_path;
                            if ($url_match_condition) {
                                define('MPG_IS_AMP_PAGE', in_array($needed_path, [$single_url . 'amp/', $single_url . 'amp']));
                            }
                    }

                    $lang_str = $project->apply_condition;
                    // it's important to check is position eqal to false, but not a 0 or any other numbers.

                    if ($url_match_condition) {

                        if (is_string($lang_str) &&  strpos($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $lang_str) === false) {
                            return [];
                        }

                        $redirect_rules = [
                            'template_id' => $project->template_id,
                            'project_id' => $project->id
                        ];

                        global $wp_object_cache;

                        // In this way solving the problem with mess in generated pages with Redis Object caching enabled.
                        if (defined('WP_REDIS_VERSION') && method_exists($wp_object_cache, 'redis_instance')) {
                            $wp_object_cache->redis_instance()->del(str_replace('_', '', $wpdb->prefix) . ':posts:' . $project->template_id);
                        }


                        if (defined('MPG_EXPERIMENTAL_FEATURES') && MPG_EXPERIMENTAL_FEATURES === true) {

                            if (extension_loaded('memcached')) {

                                if (defined(__NAMESPACE__ . '\PLUGIN_SLUG') && __NAMESPACE__ . '\PLUGIN_SLUG' === 'sg-cachepress') {

                                    $memcache = new \Memcached();
                                    if (defined('MPG_MEMCACHED_HOST') && defined('MPG_MEMCACHED_PORT')) {
                                        $memcache->addServer(MPG_MEMCACHED_HOST, MPG_MEMCACHED_PORT);
                                    } else {
                                        $memcache->addServer('127.0.0.1', '11211');
                                    }

                                    $keys_list = $memcache->getAllKeys();
                                    if ($keys_list) {
                                        foreach ($keys_list as $index => $key) {
                                            if (strpos($key, ':posts:' . $project->template_id) !== false) {
                                                $memcache->delete($keys_list[$index]);
                                            }
                                        }
                                    }
                                }
                            }
                        }


                        break 2; // Останавливаем весь цикл. Ведь один УРЛ найден.
                    }

                    // Если пользователь не Pro, и у него в файле > 50 рядов, то отдаем только первых 50.
                    if (!$is_pro && $iteration > 50) {
                        break 2;
                    }
                }
            }
        }

        return $redirect_rules;
    }

    // Принимает html код страницы, и id преокта
    // Задача функции - заменить {{шорткоды}} на реальные значения
    public static function mpg_shortcode_replacer($content, $project_id)
    {

        // Если во входящей строке нет шорткодов, то и нет смысла ее обрабатывать дальше.
        preg_match_all('/{{mpg_\S+}}/m', $content, $matches, PREG_SET_ORDER, 0);

        if (empty($matches)) {
            return $content;
        }

        // Заменяем [mpg ...]...[/mpg] на статическое приложение, просто как заглшука,
        // чтобы значения в шорткодах не заменялись значениями из датасета.

        $get_shortcodes_regexp = '/\[mpg.*?\[\/mpg]/s';

        preg_match_all($get_shortcodes_regexp, $content, $mpg_shortcodes, PREG_SET_ORDER, 0);

        $placeholers = [];
        foreach ($mpg_shortcodes as $index => $shortcode) {
            $placeholers[] = '(placeholder_replacer_' . $index . ')';
        }

        $mpg_shortcodes = MPG_Helper::array_flatten($mpg_shortcodes);

        $content = str_replace($mpg_shortcodes, $placeholers, $content);

        $url_path = MPG_Helper::mpg_get_request_uri();
        $project = MPG_ProjectModel::mpg_get_project_by_id($project_id);
        $dataset_path = $project[0]->source_path;

        $dataset_array = MPG_Helper::mpg_get_dataset_array($dataset_path, $project_id);

        // do action with short codes
        $headers = $project[0]->headers;
        if (!$headers) {
            // Закидывать в лог
            throw new Exception(__('Headers is empty. Try to upload dataset again', 'mpg'));
        }

        // Узнаем, в каком столбце (номер) находятся URL'ы
        $url_column_index = null;
        foreach (json_decode($headers, true) as $index => $header) {
            if ($header === 'mpg_url') {
                $url_column_index = $index;
            }
        }

        $short_codes = self::mpg_shortcodes_composer(json_decode($headers));

        $urls_array = $project[0]->urls_array ? json_decode($project[0]->urls_array) : [];

        $strings = null;

        // Узнаем, в каком ряду (по счету) находится тот URL, который пользователь запросил через браузер
        foreach ($urls_array as $index => $row) {

            $url_match_condition = defined('AMPFORWP_VERSION') ? in_array($url_path, [$row, $row . 'amp/']) : $row === $url_path;

            if ($url_match_condition) {
                // +1 чтобы пропустить ряд с заголовками. Да, можно сделать array_shift, но это затратная операция по CPU.
                $strings = $dataset_array[$index + 1];

                // В столбце с УРЛом - относительный адрес, типа /new-york/  и если пользователь впишет [mpg]{{mpg_url}}[/mpg]
                // то в случае, если у него wp установлен в поддерикторию (sub), адрес получится domain.com/new-york/, а не domain.com/sub/new-york
                // Поэтому, подменяем УРЛ таки образом, чтобы он был правильным. 
                // В случае, если в датасете пользователя нет столба mpg_url, то он такой шорткод и не напишет, и в этих заменах нет смысла. Т.е все логично

                if ($url_column_index !== null) {
                    $strings[$url_column_index] = MPG_CoreModel::mpg_prepare_mpg_url($project, $urls_array, $index);
                }

                break;
            }
        }

        // Эта строка заменяет шорткоды, которые просто стоят в тексте, и не обернуты в [mpg][/mpg]
        $content = preg_replace($short_codes, $strings, $content);

        // А тут делается обратная замена - заглушек на [mpg ...] {{}} [/mpg].
        // Это все для того, чтобы работала выдача всех (а не одного) ряда, если есть условие where.

        $get_placeholders_regexp = '/\(placeholder_replacer_\d{1,3}\)/s';
        preg_match_all($get_placeholders_regexp, $content, $mpg_placeholders, PREG_SET_ORDER, 0);
        $mpg_placeholders = MPG_Helper::array_flatten($mpg_placeholders);

        return str_replace($mpg_placeholders, $mpg_shortcodes, $content);
    }

    public static function mpg_shortcodes_composer($headers)
    {
        $short_codes = [];
        foreach ($headers as $raw_header) {
            $short_code = '';

            if (strpos($raw_header, 'mpg_') === 0) {
                $short_code = "/{{" . str_replace('/', '\/', strtolower($raw_header)) . "}}/"; // create template for preg_replace function
            } else {
                $short_code = "/{{mpg_" . str_replace('/', '\/', strtolower($raw_header)) . "}}/"; // create template for preg_replace function
            }

            $short_code = str_replace(' ', '_', $short_code);

            array_push($short_codes, $short_code);
        }
        return $short_codes;
    }


    public static function mpg_processing_href_matches($content, $short_codes, $href_matches, $strings, $space_replacer, $placeholders, $url_column_index, $base_url)
    {
        $temp_content = $content;

        // Поскольку в href уже стоят заглушки, то меняем шорткоды на реальные значения (не боясь "повредить" то что в href)
        $temp_content =  preg_replace($short_codes, $strings, $temp_content);

        // Теперь соберем одномерный массив с тем, что изначально было в href (скорее всего - шорткоды)

        $original_href_content = array_map(function ($match) use ($base_url) {
            return str_replace('href="', 'href="' . $base_url, $match[0]);
        }, $href_matches);

        // var_dump($original_href_content);
        // Теперь меняем массив на массив: заглушки на шорткоды
        $temp_content = str_replace($placeholders, $original_href_content, $temp_content);

        foreach ($strings as $index => $ceil) {
            // Это для того, чтобы пропустить ячейку, в которой URL. Чтобы ее не "коробило", не резало слеши... Её выводим как есть.
            if ($index !== $url_column_index) {
                $strings[$index] =  MPG_ProjectModel::mpg_processing_special_chars($ceil, $space_replacer);
            }
        }

        return preg_replace($short_codes, $strings, $temp_content);
    }


    public static function mpg_header_handler($project_id, $path)
    {


        $current_cache_type = MPG_CacheModel::mpg_get_current_caching_type($project_id);

        switch ($current_cache_type) {
            case 'disk':

                $cache_path = WP_CONTENT_DIR . '/mpg-cache/' . $project_id;
                $cache_file_name = ltrim(rtrim(strtolower($path), '/'), '/') . '.html';

                if (file_exists($cache_path . '/' . $cache_file_name)) {
                    $html = file_get_contents($cache_path . '/' . $cache_file_name);

                    echo MPG_CoreModel::mpg_shortcode_replacer($html, $project_id);
                    exit;
                }
                break;
            case 'database':

                $cached_string = MPG_CacheModel::mpg_get_row_from_database_cache($project_id, $path);
                if ($cached_string) {

                    echo MPG_CoreModel::mpg_shortcode_replacer($cached_string, $project_id);
                    exit;
                }
                break;
        }


        ob_start(function ($buffer) use ($project_id) {
            return MPG_CoreModel::mpg_shortcode_replacer($buffer, $project_id);
        });
    }

    public static function mpg_footer_handler($project_id, $path)
    {

        // Если пользователь залогинен, значит у него есть админ-бар, и ссылки типа "Ввойти", уже будут "Выйти".
        // Потом эта страница попадает в кеш, и будет видна обычным пользователям.
        if (is_user_logged_in()) {
            ob_end_flush();
        } else {

            $current_cache_type = MPG_CacheModel::mpg_get_current_caching_type($project_id);

            $html_code =  ob_get_contents();

            switch ($current_cache_type) {
                case 'disk':

                    $cache_path = WP_CONTENT_DIR . '/mpg-cache/' . $project_id;
                    $cache_file_name = ltrim(rtrim(strtolower($path), '/'), '/') . '.html';

                    if (!is_dir($cache_path)) {
                        if (!mkdir($cache_path)) {
                            throw new Exception('Creating forler for caching is failed. Please, check permissions');
                        }

                        // Создадим пустой файл, чтобы через браузер нельзя было посмотреть что в папке.
                        fwrite(fopen($cache_path . '/index.php', 'w+'), '');
                    }

                    if (!file_exists($cache_path . '/' . $cache_file_name)) {
                        fwrite(fopen($cache_path . '/' . $cache_file_name, 'w+'), $html_code);
                    }
                    break;

                case 'database':

                    MPG_CacheModel::mpg_set_row_to_database_cache($project_id, $path, $html_code);
                    break;
            }

            // Очищает буфер и выводит его содержимое на экран. 
            // Если вклчюен кеш - кидаем данные в него, а потом выводим содержимое буфера.
            ob_end_flush();
        }
    }



    public static function mpg_get_ceil_value_by_header($current_project, $dataset_array, $header_value)
    {

        $url_path = MPG_Helper::mpg_get_request_uri();

        $urls_array = $current_project[0]->urls_array ? json_decode($current_project[0]->urls_array) : [];

        // Узнаем, в каком ряду (по счету) находится тот URL, который пользователь запросил через браузер
        $url_index = array_search($url_path, $urls_array);

        // +1 чтобы пропустить ряд с заголовками. Да, можно сделать array_shift, но это затратная операция по CPU.
        $strings = $dataset_array[$url_index + 1];
        // Из какого по счету столбца брать значение для замены шорткода, который введен в where
        $dataset_array[0] = array_map(function ($header) {

            if (strpos($header, 'mpg_') !== 0) {
                $header = 'mpg_' . $header;
            }
            return strtolower(str_replace(' ', '_', $header));
        }, $dataset_array[0]);

        $shortcode_column_index = array_search(str_replace(['{{', '}}'], '',  $header_value), $dataset_array[0]);

        return  $strings[$shortcode_column_index];
    }

    public static function mpg_prepare_where_condition($project, $where_params, $dataset_array, $column_names)
    {
        $where_storage = [];
        foreach ($where_params as $condition) {

            $column_value_pair = explode('=', $condition);
            $column_name = strtolower(trim($column_value_pair[0])); // column name
            $column_index = array_search($column_name, $column_names);

            $column_value = $column_value_pair[1];

            if (isset($column_value)) {

                preg_match_all('/{{.*?}}/m', $column_value, $matches, PREG_SET_ORDER, 0);
                // Этот блок для того, чтобы работали конструкции типа where="mpg_state_id={{mpg_state_id}};mpg_county_name=Kitsap"
                if (!empty($matches)) {

                    $url_path = MPG_Helper::mpg_get_request_uri();
                    $urls_array = $project[0]->urls_array ? json_decode($project[0]->urls_array) : [];

                    // Узнаем, в каком ряду (по счету) находится тот URL, который пользователь запросил через браузер
                    $url_index = array_search($url_path, $urls_array);

                    if (!$url_index) { //false если не найден  (надо этот случай расследовать более детально)
                        // throw new Exception(__('Current page URL was not found in project. Please, check is project-id attribue in [mpg] shortcode is correct.', 'mpg'));
                    }
                    // +1 чтобы пропустить ряд с заголовками. Да, можно сделать array_shift, но это затратная операция по CPU.
                    $strings = $dataset_array[$url_index + 1];

                    // Из какого по счету столбца брать значение для замены шорткода, который введен в where
                    $shortcode_column_index = array_search(str_replace(['{{', '}}'], '',  $column_value), $dataset_array[0]);

                    if (MPG_Helper::mpg_string_start_with($column_value_pair[1], '^') && MPG_Helper::mpg_string_end_with($column_value_pair[1], '$')) {

                        $column_value = '^' . $strings[$shortcode_column_index] . '$';
                    } elseif (MPG_Helper::mpg_string_start_with($column_value_pair[1], '^') && !MPG_Helper::mpg_string_end_with($column_value_pair[1], '$')) {

                        $column_value =  '^' . $strings[$shortcode_column_index];
                    } elseif (!MPG_Helper::mpg_string_start_with($column_value_pair[1], '^')  &&  MPG_Helper::mpg_string_end_with($column_value_pair[1], '$')) {

                        $column_value = $strings[$shortcode_column_index] . '$';
                    } elseif (!MPG_Helper::mpg_string_start_with($column_value_pair[1], '^') && !MPG_Helper::mpg_string_end_with($column_value_pair[1], '$')) {

                        $column_value = $strings[$shortcode_column_index];
                    }
                }

                array_push($where_storage, [$column_index => strtolower($column_value)]); // value for search
            }
        }

        return $where_storage;
    }

    public static function mpg_order($source_data, $column_names, $direction, $order_by)
    {

        $column = [];
        $column_index = array_search($order_by, $column_names);

        if ($direction === 'asc' || $direction === 'desc') {

            foreach ($source_data as $key => $row) {

                $column[$key] = isset($row['row']) ? $row['row'][$column_index] : $row[$column_index];
            }

            array_multisort($column, $direction === 'asc' ? SORT_ASC : SORT_DESC, $source_data);
        } elseif ($direction === 'random') {
            shuffle($source_data);
        } else {
        }

        return $source_data;
    }

    public static function mpg_prepare_mpg_url($project, $urls_array, $index)
    {
        if (constant('MPG_IS_AMP_PAGE')) {
            $trail_slash = $project[0]->url_mode === 'without-trailing-slash' ? '' : '/'; //в режимах both || with_trailing_slash слеш в конце будет.

            if (substr(MPG_Helper::mpg_get_site_url() . $urls_array[$index], 0, 1) === '/') {
                return MPG_Helper::mpg_get_domain() . MPG_Helper::mpg_get_site_url() .  $urls_array[$index] . 'amp' . $trail_slash;
            } else {
                return MPG_Helper::mpg_get_domain() . '/' . MPG_Helper::mpg_get_site_url() .  $urls_array[$index] . 'amp' . $trail_slash;
            }
        } else {
            if (substr(MPG_Helper::mpg_get_site_url() . $urls_array[$index], 0, 1) === '/') {
                return MPG_Helper::mpg_get_domain() . MPG_Helper::mpg_get_site_url() .  $urls_array[$index];
            } else {
                return MPG_Helper::mpg_get_domain() . '/' . MPG_Helper::mpg_get_site_url() .  $urls_array[$index];
            }
        }
    }
}
