<?php

namespace ErikFig\Commands\MakeModule\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name} {type=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('From HueBR to HueBR, don\'t speak portuguese? se vira!');

        $name = $this->argument('name');
        $type = $this->argument('type');
        
        $name_slug = Str::slug($name);
        $type_upper = ucfirst($type);

        $pathTemplate = __DIR__ . '/../../../templates/%s';
        $pathModule = base_path('modules/' . $type .  '/' . $name_slug);

        $path = sprintf($pathTemplate, $type);

        if (!file_exists($path) or !is_dir($path)) {
            $path = sprintf($pathTemplate, 'default');
        }

        $this->info('Mapeando diretórios');
        $module = $this->mapDirectory($path . '/module', '');
        $moduleDefault = $this->mapDirectory(sprintf($pathTemplate, 'default') . '/module', '');
        $module = array_merge($module, $moduleDefault);

        $this->info('Resolvendo conteúdos');
        $module = $this->resolveContent($module, $type, $type_upper, $name, $name_slug, $path, $pathTemplate);

        $this->info('Criando módulos');
        $this->createModule($module, $pathModule);
        $this->info('Módulo criado');

    }

    private function mapDirectory($root, $dir)
    {
        $mapped = [];
        $path = $root . '/' . $dir;
        $not_check = ['.', '..'];

        foreach (scandir($path) as $value) {
            $fileOrDir = $path . '/' . $value;
            if (is_dir($fileOrDir) and !in_array($value, $not_check)) {
                $mapped[$value] = $this->mapDirectory($path, $value);
            } else if (!in_array($value, $not_check)) {
                $content = file_get_contents($fileOrDir);
                $mapped[$value] = $content;
            }
        }

        return $mapped;
    }

    private function resolveContent($module, $type, $type_upper, $name, $name_slug, $path, $pathTemplate)
    {
        foreach ($module as $key=>$content) {
            if (is_array($content)) {
                $module[$key] = $this->resolveContent($module[$key], $type, $type_upper, $name, $name_slug, $path, $pathTemplate);
            } else {
                $searchFixed = ['{{name}}', '{{type}}', '{{type-upper}}', '{{name-slug}}'];
                $replaceFixed = [
                    'name' => $name,
                    'type' => $type,
                    'type-upper' => $type_upper,
                    'name-slug' => $name_slug
                ];

                $replaceFixed['type'] = $replaceFixed['type'] == 'default' ? 'core' : $replaceFixed['type'];
                $replaceFixed['type-upper'] = $replaceFixed['type-upper'] == 'Default' ? 'Core' : $replaceFixed['type-upper'];

                $not_check = ['.', '..'];
                $pathDefault = sprintf($pathTemplate, 'default');
                $pathVariables = $pathDefault . '/variables/';

                $searchVars = [];
                $replaceVars = [];
                foreach (scandir($pathVariables) as $value) {
                    if (!in_array($value, $not_check) and !is_dir($pathVariables . $value)) {
                        $variable = file_get_contents($pathVariables . $value);
                        $variable = str_replace($searchFixed, $replaceFixed, $variable);

                        $searchVars[$value] = '{{' . $value . '}}';
                        $replaceVars[$value] = $variable;
                    }
                }

                if ($pathDefault !==  $path) {
                    $pathVariables = $path . '/variables/';
                    foreach (scandir($pathVariables) as $value) {
                        if (!in_array($value, $not_check) and !is_dir($pathVariables . $value)) {
                            $variable = file_get_contents($pathVariables . $value);
                            $variable = str_replace($searchFixed, $replaceFixed, $variable);
    
                            $searchVars[$value] = '{{' . $value . '}}';
                            $replaceVars[$value] = $variable;
                        }
                    }
                }

                $search = array_merge($searchFixed, $searchVars);
                $replace = array_merge($replaceFixed, $replaceVars);

                $keyPlaced = str_replace($search, $replace, $key);
                unset($module[$key]);
                $module[$keyPlaced] = str_replace($search, $replace, $content);
            }
        }

        return $module;
    }

    private function createModule($module, $path)
    {
        mkdir($path, null, true);
        foreach ($module as $key => $content) {
            if (is_array($content)) {
                $this->createModule($module[$key], $path . '/' . $key);
            } else {
                $key = preg_replace('/\.template$/', '', $key);
                file_put_contents($path . '/' . $key, $content);
            }
        }
    }
}
