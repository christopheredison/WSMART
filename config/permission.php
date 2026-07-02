<?php

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

        'role' => Spatie\Permission\Models\Role::class,
        'role' => App\Models\Role::class,
    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, //default 'role_id',
        'permission_pivot_key' => null, //default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`.
         */

        'team_foreign_key' => 'team_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     * Set this to false, if you want to implement custom logic for checking permissions.
     */

    'register_permission_check_method' => true,

    /*
     * When set to true the package implements teams using the 'team_foreign_key'. If you want
     * the migrations to register the 'team_foreign_key', you must set this to true
     * before doing the migration. If you already did the migration then you must make a new
     * migration to also add 'team_foreign_key' to 'roles', 'model_has_roles', and
     * 'model_has_permissions'(view the latest version of package's migration file)
     */

    'teams' => false,

    /*
     * When set to true, the required permission names are added to the exception
     * message. This could be considered an information leak in some contexts, so
     * the default setting is false here for optimum safety.
     */

    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to the exception
     * message. This could be considered an information leak in some contexts, so
     * the default setting is false here for optimum safety.
     */

    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     */

    'enable_wildcard_permission' => false,

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */

        'key' => 'spatie.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],

    'built_in_permissions' => [
        'project_divisi_list',
        'project_divisi_create',
        'project_divisi_edit',
        'project_divisi_delete',

        'project_sektor_list',
        'project_sektor_create',
        'project_sektor_edit',
        'project_sektor_delete',

        'project_list',
        'project_create',
        'project_edit',
        'project_delete',
        'project_admin_access',

        'project_periode_list',
        'project_periode_view',
        'project_periode_create',

        'project_risk_list',
        'project_risk_create',
        'project_risk_edit',
        'project_risk_reopen',
        'project_risk_delete',
        'project_risk_delete_admin',
        'project_risk_recalculate',

        'project_monitoring_list',
        'project_monitoring_edit',
        'project_monitoring_view',

        'project_monitoring_document_list',
        'project_monitoring_document_view',
        'project_monitoring_document_delete',
        'project_monitoring_document_create',

        'master_kri_list',
        'master_kri_create',
        'master_kri_edit',
        'master_kri_delete',

        'jenis_kontrol_eksisting_list',
        'jenis_kontrol_eksisting_create',
        'jenis_kontrol_eksisting_edit',
        'jenis_kontrol_eksisting_delete',

        'kontrol_eksisting_list',
        'kontrol_eksisting_create',
        'kontrol_eksisting_edit',
        'kontrol_eksisting_delete',

        'penilaian_efektivitas_kontrol_list',
        'penilaian_efektivitas_kontrol_create',
        'penilaian_efektivitas_kontrol_edit',
        'penilaian_efektivitas_kontrol_delete',

        'jenis_rencana_perlakuan_risiko_list',
        'jenis_rencana_perlakuan_risiko_create',
        'jenis_rencana_perlakuan_risiko_edit',
        'jenis_rencana_perlakuan_risiko_delete',

        'taksonomi_risiko_list',
        'taksonomi_risiko_create',
        'taksonomi_risiko_edit',
        'taksonomi_risiko_delete',

        'opsi_perlakuan_risiko_list',
        'opsi_perlakuan_risiko_create',
        'opsi_perlakuan_risiko_edit',
        'opsi_perlakuan_risiko_delete',

        'project_type_list',
        'project_type_create',
        'project_type_edit',
        'project_type_delete',

        'project_location_list',
        'project_location_create',
        'project_location_edit',
        'project_location_delete',

        'project_led_list',
        'project_led_create',
        'project_led_edit',
        'project_led_delete',

        'rmi_period_list',
        'rmi_period_view',
        'rmi_period_create',
        'rmi_period_edit',
        'rmi_period_delete',

        'question_list',
        'question_view',
        'question_create',
        'question_edit',
        'question_delete',

        'kuesioner',

        'sasaran_strategi_list',
        'sasaran_strategi_create',
        'sasaran_strategi_edit',
        'sasaran_strategi_delete',

        'backups.index',

        'risk_monitoring_edit',

        'risk_register_verification',
        'risk_register_reopen',
        'risk_register_validation',
        'risk_register_delete_admin',
        'jabatan_list',
        'jabatan_create',

        'corporate_risk_view',

        'corporate_dashboard_menu',
        'unit_dashboard_menu',
        'proyek_dashboard_menu',
        'proyek_konsolidasi_dashboard_menu',
        'ap_dashboard_menu',
        'kri_dashboard_menu',
        'corporate_menu',
        'unit_menu',
        'proyek_menu',
        'ap_menu',
        'rekomendasi_menu',
        'rmd_menu',
        'setting_menu',
        'ap_admin',
        'view_all_division',
        'view_all_project',
        'verification_mr',

        'ict_input',
        'ict_approval',

        'get_all_notification',

        'unit_risk_context',
        'project_risk_context',

        'kuesioner_responden',
        'risk_map_setting',

        'can_access_project_under_division',
        'report_consolidation',
    ],
];
