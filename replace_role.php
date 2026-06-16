<?php

$files = [
    'resources/js/Pages/Polizas/Index.vue',
    'resources/js/Pages/Polizas/Show.vue',
    'app/Http/Controllers/PolizaController.php',
    'database/seeders/RoleSeeder.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Realizamos los reemplazos
        $content = str_replace('Asesor Prefectura', 'Prefecto/a', $content);
        $content = str_replace('esAsesor', 'esPrefecto', $content);
        $content = str_replace('bandeja_asesor', 'bandeja_prefecto', $content);
        
        file_put_contents($path, $content);
        echo "Modificado: $file\n";
    } else {
        echo "No encontrado: $file\n";
    }
}
