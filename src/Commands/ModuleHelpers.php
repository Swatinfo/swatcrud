<?php

namespace Ibex\CrudGenerator\Commands;

if (!function_exists('module_path')) {
    /**
     * Get the path to a module folder.
     */
    function module_path(string $moduleName, ?string $path = ''): string
    {
        $basePath = base_path('Modules/' . $moduleName);
        return $path ? $basePath . '/' . $path : $basePath;
    }
}