<?php

namespace Ibex\CrudGenerator\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;


class ModuleGenerator
{
    use InteractsWithIO;

    protected string $moduleName;
    protected string $modulePath;
    protected string $modulePathProvider;
    protected string $controllerPathProvider;
    protected string $isDryRun;
    protected string $moduleDir;
    // protected string $command;
    protected string $moduleNameNameSpace;




    protected function getBasePath(): string
    {
        return $this->isDryRun ? base_path('DryRun') : base_path();
    }

    public function __construct(?string $moduleName = "", bool $isDryRun = false, ?string $moduleDir = "",  $command = null)
    {

        $this->output = new ConsoleOutput();

        // if (!$command) {
        //     $command = new \Illuminate\Console\Command();
        //     $command->setOutput(new ConsoleOutput());
        // }

        $this->moduleName = ucfirst($moduleName);
        $this->isDryRun = $isDryRun;
        // $this->command = $command;

        // echo $moduleDir;

        // $this->info("1...Dir...$moduleDir");

        if ($moduleDir != "") {
            $this->moduleDir = $moduleDir;
            $this->modulePath = $this->getBasePath() . '/Modules/' . $this->moduleDir . "/" . $this->moduleName;

            $this->modulePathProvider = ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleDir . "/" . $this->moduleName;

            $this->controllerPathProvider = ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleDir . "/" . $this->moduleName . "/Controllers";

            $this->moduleNameNameSpace =  ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleDir . "/" . $this->moduleName . "/Providers";
        } else {
            $this->moduleDir = "";
            $this->modulePath = $this->getBasePath() . '/Modules/' . $this->moduleName;

            $this->modulePathProvider = ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleDir . "/" . $this->moduleName;

            $this->controllerPathProvider = ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleDir . "/" . $this->moduleName . "/Controllers";;
            $this->moduleNameNameSpace =  ($this->isDryRun ? "DryRun" : "") . '/Modules/' . $this->moduleName . "/Providers";
        }

        $this->modulePath = str_replace("/", "\\", $this->modulePath);
        // $this->modulePathProvider = str_replace("/", "\\", $this->modulePathProvider);
        $this->controllerPathProvider = str_replace("/", "\\", $this->controllerPathProvider);
        $this->moduleNameNameSpace = str_replace("/", "\\", $this->moduleNameNameSpace);

        // echo "...Module Path 1...." . $this->modulePath;

        // $this->info("...Module Path 1....$this->modulePath");
    }

    public function generate(): void
    {
        $isDryRun = $this->isDryRun ?? false;
        $basePath = $isDryRun ? base_path('DryRun/Modules/') : base_path('Modules/');

        $basePath = str_replace("/", "\\", $basePath);

        // echo "...base Path 1...." . $basePath;

        // $this->info("...Base Path 1....$basePath");

        // $modulepath = $this->options['module'];
        if ($this->moduleDir != "") {
            $basePath = $basePath . $this->moduleDir . "/";
        }

        $basePath = str_replace("/", "\\", $basePath);

        // echo "...base Path 2...." . $basePath;

        // $this->info("...Base Path 2....$basePath");


        $this->modulePath = $basePath . $this->moduleName;

        $this->modulePath = str_replace("/", "\\", $this->modulePath);


        // echo "...Module Path 2...." . $this->modulePath;

        // $this->info("...Module Path 2....$this->modulePath");



        $this->createDirectoryStructure()
            ->createModuleJson()
            ->createServiceProvider()
            ->createRouteFiles();

        if ($isDryRun) {
            $this->info('Module generated in DryRun directory.');
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
        $provider = "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider";
        if ($this->moduleDir != "") {
            $provider = "Modules\\{$this->moduleDir}\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider";
        }
        $provider = $this->isDryRun ? 'DryRun\\' . $provider :  $provider;
        // echo "..." . $provider;

        // $this->info("...Provider....$provider");

        // echo "...Module Path 3...." . $this->modulePath;

        // $this->info("...Module Path 3....$this->modulePath");


        $content = [
            'name' => $this->moduleName,
            'alias' => strtolower($this->moduleName),
            'description' => '',
            'keywords' => [],
            'priority' => 0,
            'providers' => [
                $provider
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
            ['{{moduleNameNameSpace}}', '{{moduleName}}', '{{modulePath}}'],
            [$this->moduleNameNameSpace, $this->moduleName, $this->modulePathProvider],
            $stub
        );

        File::put(
            $this->modulePath . "/Providers/{$this->moduleName}ServiceProvider.php",
            $content
        );

        // Create route service provider
        $routeStub = File::get(__DIR__ . '/stubs/route-provider.stub');
        $routeContent = str_replace(
            ['{{moduleNameNameSpace}}', '{{moduleName}}', '{{controllerPath}}', '{{modulePath}}'],
            [$this->moduleNameNameSpace, $this->moduleName, $this->controllerPathProvider, $this->modulePathProvider],
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
