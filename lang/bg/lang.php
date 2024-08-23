<?php

return [
    'app' => [
        'name' => 'Winter CMS',
        'tagline' => 'Връщане към основите'
    ],
    'directory' => [
        'create_fail' => 'Не може да създаде директория(папка): :name'
    ],
    'file' => [
        'create_fail' => 'Не може да създаде файл: :name'
    ],
    'combiner' => [
        'not_found' => "Комбинираният файл ':name' не е намерен."
    ],
    'page' => [
        'invalid_token' => [
            'label' => 'Невалиден код за сигурност (token)',
        ],
    ],
    'system' => [
        'name' => 'Система',
        'menu_label' => 'Система',
        'categories' => [
            'cms' => 'CMS',
            'misc' => 'Разни',
            'logs' => 'Логове',
            'mail' => 'Имейл',
            'shop' => 'Магазин',
            'team' => 'Екип',
            'users' => 'Потребители',
            'system' => 'Система',
            'social' => 'Социален',
            'events' => 'Събития',
            'customers' => 'Клиенти',
            'my_settings' => 'Моите настройки'
        ]
    ],
    'theme' => [
        'label' => 'Тема',
        'unnamed' => 'Тема без име',
        'name' => [
            'label' => 'Име на тема',
            'help' => 'Именувайте темата с уникално име. Например: Winter.Vanilla'
        ],
    ],
    'themes' => [
        'install' => 'Инсталиране на теми',
        'search' => 'потърси тема за инсталиране...',
        'installed' => 'Инсталирани теми',
        'no_themes' => 'Няма инсталирани теми от маркета.',
        'recommended' => 'Препоръчано',
        'remove_confirm' => 'Наистина ли искате да премахнете тази тема?'
    ],
    'plugin' => [
        'label' => 'Добавки',
        'unnamed' => 'Без име добавка',
        'name' => [
            'label' => 'Име на добавка',
            'help' => 'Именувайте добавката с уникално име. Например: Winter.Blog'
        ]
    ],
    'plugins' => [
        'manage' => 'Управление на добавки',
        'enable_or_disable' => 'Включване или изключване',
        'enable_or_disable_title' => 'Включване или изключване на добавки',
        'install' => 'Инсталиране на добавки',
        'install_products' => 'Инсталиране на продукти',
        'search' => 'потърси добавка за инсталиране...',
        'installed' => 'Инсталирани добавки',
        'no_plugins' => 'Няма инсталирани добавки от маркета',
        'recommended' => 'Препоръчано',
        'remove' => 'Премахни',
        'refresh' => 'Презареди',
        'disabled_label' => 'Изключен',
        'disabled_help' => 'Добавки които са изключени се игнорират от приложението',
        'frozen_label' => 'Замрази обновяването',
        'frozen_help' => 'Добавки които са замразени, ще бъдат игнорирани при обновяване.',
        'selected_amount' => 'Избрани добавки: :amount',
        'remove_confirm' => 'Наистина ли искате да премахнете тази добавка?',
        'remove_success' => 'Успешно премахнати добавки от системата.',
        'refresh_confirm' => 'Сигурни ли сте че искате да ги презаредите?',
        'refresh_success' => 'Успешно обновени добавки в системата.',
        'disable_confirm' => 'Наистина ли искате да ги изключите?',
        'disable_success' => 'Успешно изключени на добавките.',
        'enable_success' => 'Успешно включване на добавките.',
        'unknown_plugin' => 'Добавката е премахната от системата.'
    ],
    'project' => [
        'name' => 'Проект',
        'owner_label' => 'Собственик',
        'attach' => 'Прикрепете Проект',
        'detach' => 'Премахнете Проект',
        'none' => 'Нито един',
        'id' => [
            'label' => 'Проект ID',
            'help' => 'Как да намерите своя Проект ID',
            'missing' => 'Моля, задайте Проект ID за да го използвате.'
        ],
        'detach_confirm' => 'Сигурни ли сте, че искате да премахнете този Проект?',
        'unbind_success' => 'Този Проект е премахнат успешно.'
    ],
    'settings' => [
        'menu_label' => 'Настройки',
        'not_found' => 'Неуспешно намиране на конкретните Настройки.',
        'missing_model' => 'Страницата за Настройки липсва дефиниция на модел.',
        'update_success' => 'Настройките за :name бяха успешно променени.',
        'return' => 'Връщане към системните настройки.',
        'search' => 'Търсене'
    ],
    'mail' => [
        'log_file' => 'Регистрационен файл',
        'menu_label' => 'Имейл настройка',
        'menu_description' => 'Управление на конфигурацията на имейли.',
        'general' => 'Общи',
        'method' => 'Имейл метод',
        'sender_name' => 'Име на подателя',
        'sender_email' => 'Имейл на подателя',
        'php_mail' => 'PHP имейл',
        'smtp' => 'SMTP',
        'smtp_address' => 'SMTP адрес',
        'smtp_authorization' => 'SMTP изисква потвърждение.',
        'smtp_authorization_comment' => 'Отбележете тази отметка ако вашия SMTP сървър изисква потвърждение.',
        'smtp_username' => 'Потребител',
        'smtp_password' => 'парола',
        'smtp_port' => 'SMTP порт',
        'sendmail' => 'Sendmail',
        'sendmail_path' => 'Sendmail директория',
        'sendmail_path_comment' => 'Моля, посочете директория на Sendmail програмата.',
    ],
    'mail_templates' => [
        'menu_label' => 'Имейл шаблони',
        'menu_description' => 'Modify the mail templates that are sent to users and administrators, manage email layouts.',
        'new_template' => 'Нов шаблони',
        'new_layout' => 'Ново оформление',
        'template' => 'Шаблон',
        'templates' => 'Шаблони',
        'menu_layouts_label' => 'Имейл оформления',
        'layout' => 'Оформление',
        'layouts' => 'Оформления',
        'no_layout' => '-- Няма оформление --',
        'name' => 'Име',
        'name_comment' => 'Уникален име за обозначаване на този шаблон',
        'code' => 'Код',
        'code_comment' => 'Уникаленен код  за обозначаване на този шаблон',
        'subject' => 'Тема',
        'subject_comment' => 'Тема на имейла',
        'description' => 'Описание',
        'content_html' => 'HTML',
        'content_css' => 'CSS',
        'content_text' => 'Обикновен текст',
        'test_send' => 'Изпрати тестово съобщение',
        'test_success' => 'Тестово съобщение е изпратено успешно.',
        'test_confirm' => 'Тестово съобщение ще бъде изпратено до :email. Продължавам?',
        'creating' => 'Създаване на Шаблон...',
        'creating_layout' => 'Създаване на Оформление...',
        'saving' => 'Запазване на Шаблон...',
        'saving_layout' => 'Запазване на Оформление...',
        'delete_confirm' => 'Наистина ли искате да изтриете този Шаблон?',
        'delete_layout_confirm' => 'Наистина ли искате да изтриете това Оформление?',
        'deleting' => 'Изтриване на Шаблон...',
        'deleting_layout' => 'Изтриване на Оформление...',
        'sending' => 'Изпращане на тестово съобщение...',
        'return' => 'Назад към списъка с шаблон'
    ],
    'install' => [
        'project_label' => 'Закачете за Проект',
        'plugin_label' => 'Инсталиране на Добавка',
        'theme_label' => 'Инсталиране на Тема',
        'missing_plugin_name' => 'Моля, укажете име на Добавката за инсталиране.',
        'missing_theme_name' => 'Моля, укажете име на Темата за инсталиране.',
        'install_completing' => 'Завършване на инсталационния процес.',
        'install_success' => 'Добавката е инсталирана успешно.'
    ],
    'updates' => [
        'title' => 'Управление Актуализации',
        'name' => 'Софтуерни Актуализации',
        'menu_label' => 'Актуализация',
        'menu_description' => 'Актуализиране на системата, управление и инсталиране на добавки и теми.',
        'return_link' => 'Назад към система за актуализация',
        'check_label' => 'Провери за актуализации',
        'retry_label' => 'Опитай отново',
        'plugin_name' => 'Име',
        'plugin_code' => 'Код',
        'plugin_description' => 'Описание',
        'plugin_version' => 'Версия',
        'plugin_author' => 'Автор',
        'plugin_not_found' => 'Plugin not found',
        'core_current_build' => 'Текуща изработка',
        'core_build' => 'Изработка :build',
        'core_build_help' => 'Последни изработка е на разположение.',
        'core_downloading' => 'Изтеглянето на файловете на приложението',
        'core_extracting' => 'Разопаковане на файловете на приложението',
        'plugins' => 'Добавки',
        'themes' => 'Теми',
        'disabled' => 'Изключено',
        'plugin_downloading' => 'Изтегляне на Добавка: :name',
        'plugin_extracting' => 'Разопаковане на Добавка: :name',
        'plugin_version_none' => 'Нова Добавка',
        'plugin_current_version' => 'Текуща версия',
        'theme_new_install' => 'Нова инсталация на Тема.',
        'theme_downloading' => 'Изтегляне на Тема: :name',
        'theme_extracting' => 'Разопаковане на Тема: :name',
        'update_label' => 'актуализация на софтуер',
        'update_completing' => 'Завършване на инсталационния процес',
        'update_loading' => 'Зареждане на наличните актуализации...',
        'update_success' => 'Процесът на актуализация е извършена успешно.',
        'update_failed_label' => 'Актуализация се провали',
        'force_label' => 'Принудителна актуализация',
        'found' => [
            'label' => 'Намерени нови актуализации!',
            'help' => 'Кликнете "Софтуерна Актуализация", за да започнете процеса на актуализиране.'
        ],
        'none' => [
            'label' => 'Няма актуализации',
            'help' => 'Няма намерени нови актуализации.'
        ],
        'important_action' => [
            'empty' => 'Изберете действие',
            'confirm' => 'Потвърждаване на актуализация',
            'skip' => 'Пропусни тази добавка (само веднъж)',
            'ignore' => 'Пропусни тази добавка (винаги)',
        ],
        'important_action_required' => 'Изисква действие',
        'important_view_guide' => 'Вижте ръководство за актуализация',
        'important_alert_text' => 'Някои актуализации на които трябва да обърнете внимание.',
        'details_title' => 'Детайли за добавка',
        'details_view_homepage' => 'Виж начална страница',
        'details_readme' => 'Документация',
        'details_readme_missing' => 'Не е предоставена документация.',
        'details_upgrades' => 'Ръководство За Ъпгрейд',
        'details_upgrades_missing' => 'Все още няма предоставени инструкции за ъпгрейд.',
        'details_current_version' => 'Текуща версия',
        'details_author' => 'Автор',
    ],
    'server' => [
        'connect_error' => 'Грешка при свързването със сървъра.',
        'response_not_found' => 'Сървърът за актуализация не може да бъде намерен.',
        'response_invalid' => 'Невалиден отговор от сървъра.',
        'response_empty' => 'Празен отговор от сървъра.',
        'file_error' => 'Сървър не успя да достави пакета.',
        'file_corrupt' => 'Файл от сървъра е развален (разрушен).'
    ],
    'behavior' => [
        'missing_property' => 'Класът :class трябва да има свойството $:property използвано :behavior поведение.'
    ],
    'config' => [
        'not_found' => 'Не може да се намери конфигурационен файл :file определен за :location.',
        'required' => "Конфигурация използва в :location трябва да предостави стойност ':property'."
    ],
    'zip' => [
        'extract_failed' => "Не може да се разопакова (извлече) ядрото на файл ':file'."
    ],
    'event_log' => [
        'hint' => 'Този дневник (регистър) показва списък на потенциални грешки, които възникват в приложението, като изключения и информацията за грешки.',
        'menu_label' => 'Дневник (регистъра) на събития',
        'menu_description' => 'Вижте съобщения в системните дневници с времето на записване и детайлите.',
        'empty_link' => 'Изтрии събитията в регистъра',
        'empty_loading' => 'Изтриване на събитията в регистъра...',
        'empty_success' => 'Успешно изчистване дневника (регистъра) на събитията.',
        'return_link' => 'Назад към регистъра на събитията',
        'id' => 'ID',
        'id_label' => 'Събитие ID',
        'created_at' => 'Дата и час',
        'message' => 'Съобщение',
        'level' => 'Ниво'
    ],
    'request_log' => [
        'hint' => 'Този дневник показва списък с заявките на браузъра, които могат да изискват внимание. Например, ако един посетител отваря CMS страница, която не може да се намери, се създава запис с код за грешка 404.',
        'menu_label' => 'Дневник (регистър) на заявките',
        'menu_description' => 'Преглед на лоши или пренасочени заявки, като например "страницата не е намерена (404)".',
        'empty_link' => 'Изтрии заявките в регистъра',
        'empty_loading' => 'Изтриване на заявките в регистъра...',
        'empty_success' => 'Успешно изчистване дневника (регистъра) на заявките..',
        'return_link' => 'Назад към регистъра на заявките',
        'id' => 'ID',
        'id_label' => 'Входно ID',
        'count' => 'Брояч',
        'referer' => 'Референти',
        'url' => 'URL',
        'status_code' => 'Статус'
    ],
    'permissions' => [
        'name' => 'Система',
        'manage_system_settings' => 'Управление на системните настройки',
        'manage_software_updates' => 'Управление на софтуерни актуализации',
        'access_logs' => 'Вижте системните логове',
        'manage_mail_templates' => 'Управление на шаблони за електронна поща',
        'manage_mail_settings' => 'Управление на настройките за поща',
        'manage_other_administrators' => 'Управление на други администратори',
        'manage_preferences' => 'Управление на предпочитанията на админ панела',
        'manage_editor' => 'Управление на предпочитанията на редактор на код',
        'view_the_dashboard' => 'Преглед на таблото',
        'manage_branding' => 'Персонализиране на админ панела'
    ],
    'media' => [
        'invalid_path' => "Невалиден път за файл е посочено: ':path'.",
        'folder_size_items' => 'предмет(и)',
    ],
];
