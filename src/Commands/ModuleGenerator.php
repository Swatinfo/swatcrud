<?php

namespace Ibex\CrudGenerator\Commands;

use Illuminate\Support\Facades\File;

class ModuleGenerator
{
    protected string $moduleName;
    protected string $modulePath;

    public function __construct(string $moduleName, bool $isDryRun = false, $command = null) 
    {
        $this->moduleName = ucfirst($moduleName);
        $this->command = $command;
        $this->modulePath = base_path('Modules/' . $this->moduleName);
    }

    public function generate(): void
    {
        $isDryRun = $this->isDryRun ?? false;
        $basePath = $isDryRun ? base_path('DryRun/Modules/') : base_path('Modules/');
        $this->modulePath = $basePath . $this->moduleName;

        $this->createDirectoryStructure()
            ->createModuleJson()
            ->createServiceProvider()
            ->createRouteFiles();

        if ($isDryRun) {
            $this->command->info("Module structure would be created at: {$this->modulePath}");
        }
    }

    protected function createDirectoryStructure(): self
    {
        $directories = [
            'Config',
            'Database/Migrations',
            'Database/Seeders',
            'Http/Controllers',
            'Http/Controllers/Api',
            'Http/Middleware',
            'Http/Requests',
            'Http/Resources',
            'Models',
            'Providers',
            'Resources/views',
            'Resources/assets',
            'Routes',
        ];

        foreach ($directories as $directory) {
            File::makeDirectory($this->modulePath . '/' . $directory, 0755, true, true);
        }

        return $this;
    }

    protected function createModuleJson(): self
    {
        $content = [
            'name' => $this->moduleName,
            'alias' => strtolower($this->moduleName),
            'description' => '',
            'keywords' => [],
            'priority' => 0,
            'providers' => [
                "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider"
            ],
            'files' => []
        ];

        File::put(
            $this->modulePath . '/module.json',
            json_encode($content, JSON_PRETTY_PRINT)
        );

        return $this;
    }

    // Add to createServiceProvider() method:

    protected function createServiceProvider(): self
    {
        // Create main service provider
        $stub = File::get(__DIR__ . '/stubs/module-provider.stub');
        $content = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $stub
        );

        File::put(
            $this->modulePath . "/Providers/{$this->moduleName}ServiceProvider.php",
            $content
        );

        // Create route service provider
        $routeStub = File::get(__DIR__ . '/stubs/route-provider.stub');
        $routeContent = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $routeStub
        );

        File::put(
            $this->modulePath . "/Providers/RouteServiceProvider.php",
            $routeContent
        );

        return $this;
    }

    protected function createRouteFiles(): self
    {
        $files = ['web.php', 'api.php'];
        foreach ($files as $file) {
            File::put(
                $this->modulePath . '/Routes/' . $file,
                "<?php\n\nuse Illuminate\Support\Facades\Route;\n"
            );
        }

        return $this;
    }
}
