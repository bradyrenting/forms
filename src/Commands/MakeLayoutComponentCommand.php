<?php

namespace Filament\Forms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeLayoutComponentCommand extends Command
{
    use Concerns\CanManipulateFiles;

    protected $description = 'Creates a form layout component class and view.';

    protected $signature = 'make:form-layout {name}';

    public function handle(): int
    {
        $component = (string) Str::of($this->argument('name'))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');
        $componentClass = (string) Str::of($component)->afterLast('\\');
        $componentNamespace = Str::of($component)->contains('\\') ?
            (string) Str::of($component)->beforeLast('\\') :
            '';

        $view = Str::of($component)
            ->prepend('forms\\components\\')
            ->explode('\\')
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');

        $path = app_path(
            (string) Str::of($component)
                ->prepend('Forms\\Components\\')
                ->replace('\\', '/')
                ->append('.php'),
        );
        $viewPath = resource_path(
            (string) Str::of($view)
                ->replace('.', '/')
                ->prepend('views/')
                ->append('.blade.php'),
        );

        if ($this->checkForCollision([
            $path,
        ])) {
            return static::INVALID;
        }

        $this->copyStubToApp('LayoutComponent', $path, [
            'class' => $componentClass,
            'namespace' => 'App\\Forms\\Components' . ($componentNamespace !== '' ? "\\{$componentNamespace}" : ''),
            'view' => $view,
        ]);

        if (! $this->fileExists($viewPath)) {
            $this->copyStubToApp('LayoutComponentView', $viewPath);
        }

        $this->info("Successfully created {$component}!");

        return static::SUCCESS;
    }
}
