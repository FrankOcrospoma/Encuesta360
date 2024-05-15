<?php

return [

    // Habilitar todo el módulo
    'enable' => true,

    // Estilo RTL, si estás usando un idioma como persa o árabe, cámbialo a true
    'rtl_mode' => false,

    // Idioma del paquete
    'lang' => 'en',

    // Tu modelo de usuario
    'user_model' => file_exists(app_path('User.php')) ? App\User::class : App\Models\User::class,

    // Establecer el guard por defecto para autenticar administradores
    'auth_guard' => config('auth.defaults.guard') ?? 'web',

    // Cómo autenticar a un administrador
    // Puedes usar otras formas de autenticar a un administrador (tablas o ..) puedes gestionarlo con esta clase
    'auth_class' => \EasyPanel\Support\Auth\AdminIdentifier::class,

    // Con esta clase puedes gestionar cómo crear o eliminar un administrador.
    'admin_provider_class' => \EasyPanel\Support\User\UserProvider::class,

    // El namespace de la clase de gestión de idioma
    'lang_manager_class' => \EasyPanel\Services\LangService::class,

    // Es el lugar a donde se redirige un usuario si no está autenticado
    'redirect_unauthorized' => '/',

    // Prefijo de rutas del panel de administración
    'route_prefix' => 'admin', //  http://localhost/admin

    // Tus propios middlewares para las rutas del panel fácil.
    'additional_middlewares' => [],

    // Cantidad de paginación en listas CRUD
    'pagination_count' => 20,

    // Validación perezosa para componentes Livewire
    'lazy_mode' => true,
];
