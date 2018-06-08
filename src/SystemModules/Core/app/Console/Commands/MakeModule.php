<?php

namespace SystemModules\Core\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use SystemModules\Core\App\Facades\ModulesManager;
use SystemModules\Core\App\Models\Module;

class MakeModule extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;
    
    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        
        $this->files = $files;
    }
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:module';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';
    
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->argument('modules') as $module) {
            $module = ucfirst(strtolower($module));
            
            if (!$this->generate($module))
                continue;
            
            $this->info("Module $module generated successfully!");
        }
    }
    
    /**
     * Generate base module structure and config.
     *
     * @param string $module
     * @return bool
     */
    private function generate($module)
    {
        $relativePath = "modules/$module/";
        $path = base_path($relativePath);
        
        $fileExist = $this->files->exists($path);
        $installed = !empty(Module::findAlias($module));
        
        if ($fileExist && $installed) {
            $this->error("Module $module is already installed.");
            return false;
        } elseif ($fileExist) {
            $this->error("Module $module's files exist but not installed.");
            return false;
        } elseif ($installed) {
            $this->error("Module $module is installed but files doesn't exist.");
            return false;
        }
        
        $this->makeDirectory($path);
        $this->makeDefaultConfig($module, $path);
        
        ModulesManager::install($relativePath, true);
        
        $this->makeDirectory($path . 'routes');
        $this->makeRoutes($path);
        
        return true;
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    private function getConfigStub()
    {
        return __DIR__ . '/stubs/module_config.stub';
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return array
     */
    private function getRoutesStub()
    {
        return [
            'api' => __DIR__ . '/stubs/route_api.stub',
            'channels' => __DIR__ . '/stubs/route_channels.stub',
            'console' => __DIR__ . '/stubs/route_console.stub',
            'web' => __DIR__ . '/stubs/route_web.stub',
        ];
    }
    
    /**
     * Replace the name for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    private function replaceName(&$stub, $name)
    {
        $stub = str_replace('DummyName', $name, $stub);
        
        return $this;
    }
    
    /**
     * Replace the name for the given stub.
     *
     * @param  string $stub
     * @param  string $alias
     * @return $this
     */
    private function replaceAlias(&$stub, $alias)
    {
        $stub = str_replace('DummyAlias', $alias, $stub);
        
        return $this;
    }
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['modules', InputArgument::IS_ARRAY, 'The list of modules to create'],
        ];
    }
    
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fill', null, InputOption::VALUE_OPTIONAL, 'Fill the module with example stuff'],
        ];
    }
    
    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    private function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
        
        return $path;
    }
    
    private function makeDefaultConfig($module, $path)
    {
        $stub = $this->files->get($this->getConfigStub());
        $this->replaceAlias($stub, strtolower($module))->replaceName($stub, $module);
        
        $this->files->put($path . '/module.json', $stub);
    }
    
    private function makeRoutes($path)
    {
        $stubsPath = $this->getRoutesStub();
        
        foreach ($stubsPath as $route => $stubPath) {
            $stub = $this->files->get($stubPath);
            $this->files->put($path . "routes/$route.php", $stub);
        }
    }
}
