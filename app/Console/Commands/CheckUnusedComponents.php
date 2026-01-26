<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CheckUnusedComponents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-unused-components
                            {--detailed : Show detailed output with file paths and line numbers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for unused, mismatched and undeveloped views, controllers, models, tasks, jobs, routes, services, commands, APIs, and blade templates';

    /**
     * Statistics tracking
     */
    private array $stats = [
        'controllers' => ['total' => 0, 'unused' => 0, 'methods_unused' => 0],
        'models' => ['total' => 0, 'unused' => 0],
        'views' => ['total' => 0, 'unused' => 0],
        'routes' => ['total' => 0, 'broken' => 0],
        'services' => ['total' => 0, 'unused' => 0],
        'jobs' => ['total' => 0, 'unused' => 0],
        'commands' => ['total' => 0, 'unused' => 0],
        'api_controllers' => ['total' => 0, 'unused' => 0],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Starting comprehensive component analysis...');
        $this->newLine();

        try {
            // Analyze each component type
            $this->analyzeControllers();
            $this->analyzeModels();
            $this->analyzeViews();
            $this->analyzeRoutes();
            $this->analyzeServices();
            $this->analyzeJobs();
            $this->analyzeCommands();
            $this->analyzeApiControllers();

            // Display statistics
            $this->displayStatistics();

            $this->newLine();
            $this->info('✅ Analysis completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ An error occurred during analysis: ' . $e->getMessage());
            if ($this->option('detailed')) {
                $this->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Analyze controllers for unused classes and methods
     */
    private function analyzeControllers(): void
    {
        $this->info('📋 Analyzing Controllers...');

        $controllersPath = app_path('Http/Controllers');
        if (! File::exists($controllersPath)) {
            $this->warn('Controllers directory not found');

            return;
        }

        $controllers = $this->getPhpFiles($controllersPath);
        $this->stats['controllers']['total'] = count($controllers);

        $routeFiles = ['web.php', 'api.php'];
        $routeContent = '';
        foreach ($routeFiles as $routeFile) {
            $routePath = base_path("routes/{$routeFile}");
            if (File::exists($routePath)) {
                $routeContent .= File::get($routePath);
            }
        }

        $unusedControllers = [];
        $controllersWithUnusedMethods = [];

        foreach ($controllers as $controller) {
            $className = $this->getClassName($controller);
            $namespace = $this->getNamespace($controller);
            $fullClassName = $namespace . '\\' . $className;

            // Check if controller is referenced in routes
            $isUsed = $this->isControllerUsedInRoutes($className, $fullClassName, $routeContent);

            if (! $isUsed) {
                $unusedControllers[] = $controller;
                $this->stats['controllers']['unused']++;
            } else {
                // Check for unused methods
                $unusedMethods = $this->findUnusedControllerMethods($controller, $routeContent);
                if (! empty($unusedMethods)) {
                    $controllersWithUnusedMethods[$controller] = $unusedMethods;
                    $this->stats['controllers']['methods_unused'] += count($unusedMethods);
                }
            }
        }

        // Display results
        if (! empty($unusedControllers)) {
            $this->warn("  ⚠️  Found {$this->stats['controllers']['unused']} unused controller(s):");
            foreach ($unusedControllers as $controller) {
                $this->line('    - ' . $this->getRelativePath($controller));
            }
        } else {
            $this->info('  ✓ All controllers are being used');
        }

        if (! empty($controllersWithUnusedMethods)) {
            $this->warn('  ⚠️  Found controller(s) with unused methods:');
            foreach ($controllersWithUnusedMethods as $controller => $methods) {
                $this->line('    - ' . $this->getRelativePath($controller) . ':');
                foreach ($methods as $method) {
                    $this->line("      • {$method}");
                }
            }
        }

        $this->newLine();
    }

    /**
     * Analyze models for unused classes
     */
    private function analyzeModels(): void
    {
        $this->info('📦 Analyzing Models...');

        $modelsPath = app_path('Models');
        if (! File::exists($modelsPath)) {
            $this->warn('Models directory not found');

            return;
        }

        $models = $this->getPhpFiles($modelsPath);
        $this->stats['models']['total'] = count($models);

        $searchPaths = [
            app_path('Http/Controllers'),
            app_path('Console/Commands'),
            app_path('Jobs'),
            app_path('Services'),
            app_path('Listeners'),
            app_path('Mail'),
            app_path('Policies'),
        ];

        $unusedModels = [];

        foreach ($models as $model) {
            $className = $this->getClassName($model);

            // Skip base models and traits
            if (in_array($className, ['Model', 'Authenticatable', 'Pivot'])) {
                $this->stats['models']['total']--;

                continue;
            }

            $isUsed = false;

            foreach ($searchPaths as $searchPath) {
                if (! File::exists($searchPath)) {
                    continue;
                }

                $files = $this->getPhpFiles($searchPath);
                foreach ($files as $file) {
                    if ($file === $model) {
                        continue;
                    }

                    $content = File::get($file);

                    // Check for various usage patterns
                    if ($this->isModelUsed($className, $content)) {
                        $isUsed = true;
                        break 2;
                    }
                }
            }

            // Also check in routes
            foreach (['web.php', 'api.php'] as $routeFile) {
                $routePath = base_path("routes/{$routeFile}");
                if (File::exists($routePath)) {
                    $routeContent = File::get($routePath);
                    if ($this->isModelUsed($className, $routeContent)) {
                        $isUsed = true;
                        break;
                    }
                }
            }

            if (! $isUsed) {
                $unusedModels[] = $model;
                $this->stats['models']['unused']++;
            }
        }

        // Display results
        if (! empty($unusedModels)) {
            $this->warn("  ⚠️  Found {$this->stats['models']['unused']} unused model(s):");
            foreach ($unusedModels as $model) {
                $this->line('    - ' . $this->getRelativePath($model));
            }
        } else {
            $this->info('  ✓ All models are being used');
        }

        $this->newLine();
    }

    /**
     * Analyze views/blade templates for unused files
     */
    private function analyzeViews(): void
    {
        $this->info('👁️  Analyzing Views/Blade Templates...');

        $viewsPath = resource_path('views');
        if (! File::exists($viewsPath)) {
            $this->warn('Views directory not found');

            return;
        }

        $views = $this->getBladeFiles($viewsPath);
        $this->stats['views']['total'] = count($views);

        $searchPaths = [
            app_path('Http/Controllers'),
            app_path('Mail'),
            app_path('Notifications'),
        ];

        $allContent = '';
        foreach ($searchPaths as $searchPath) {
            if (File::exists($searchPath)) {
                $files = $this->getPhpFiles($searchPath);
                foreach ($files as $file) {
                    $allContent .= File::get($file) . "\n";
                }
            }
        }

        $unusedViews = [];

        foreach ($views as $view) {
            $viewName = $this->getViewName($view, $viewsPath);

            // Skip layout files and partials that are typically included
            if (Str::startsWith(basename($view), '_') ||
                Str::contains($view, ['/layouts/', '/components/', '/partials/'])) {
                $this->stats['views']['total']--;

                continue;
            }

            // Check for view() or View::make() or Inertia::render()
            $escapedViewName = preg_quote($viewName, '/');
            $patterns = [
                "/view\(['\"]" . $escapedViewName . "['\"]/",
                "/View::make\(['\"]" . $escapedViewName . "['\"]/",
                "/Inertia::render\(['\"]" . $escapedViewName . "['\"]/",
            ];

            $isUsed = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $allContent)) {
                    $isUsed = true;
                    break;
                }
            }

            if (! $isUsed) {
                $unusedViews[] = $view;
                $this->stats['views']['unused']++;
            }
        }

        // Display results
        if (! empty($unusedViews)) {
            $this->warn("  ⚠️  Found {$this->stats['views']['unused']} unused view(s):");
            foreach ($unusedViews as $view) {
                $this->line('    - ' . $this->getRelativePath($view));
            }
        } else {
            $this->info('  ✓ All views are being used');
        }

        $this->newLine();
    }

    /**
     * Analyze routes for broken references
     */
    private function analyzeRoutes(): void
    {
        $this->info('🛣️  Analyzing Routes...');

        $brokenRoutes = [];

        foreach (['web.php', 'api.php'] as $routeFile) {
            $routePath = base_path("routes/{$routeFile}");
            if (! File::exists($routePath)) {
                continue;
            }

            $content = File::get($routePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                // Match Route::method([Controller::class, 'method'])
                if (preg_match('/Route::\w+\([^,]+,\s*\[\\\\?([^:]+)::class,\s*[\'"](\w+)[\'"]\]/', $line, $matches)) {
                    $this->stats['routes']['total']++;
                    $controllerClass = trim($matches[1]);
                    $method = $matches[2];

                    // Clean up the class name - remove leading backslash if present
                    $fullClassName = ltrim($controllerClass, '\\');

                    // If not already namespaced, add default namespace
                    if (! Str::startsWith($fullClassName, 'App\\')) {
                        $fullClassName = $this->resolveClassName($controllerClass, $content);
                    }

                    $controllerPath = $this->classToPath($fullClassName);

                    if (! File::exists($controllerPath)) {
                        $brokenRoutes[] = [
                            'file' => $routeFile,
                            'line' => $lineNumber + 1,
                            'issue' => "Controller not found: {$fullClassName}",
                            'code' => trim($line),
                        ];
                        $this->stats['routes']['broken']++;
                    } elseif (! $this->methodExistsInFile($controllerPath, $method)) {
                        $brokenRoutes[] = [
                            'file' => $routeFile,
                            'line' => $lineNumber + 1,
                            'issue' => "Method '{$method}' not found in {$fullClassName}",
                            'code' => trim($line),
                        ];
                        $this->stats['routes']['broken']++;
                    }
                }

                // Match Route::method('Controller@method')
                if (preg_match('/Route::\w+\([^,]+,\s*[\'"]([^@]+)@(\w+)[\'"]/', $line, $matches)) {
                    $this->stats['routes']['total']++;
                    $controllerClass = trim($matches[1]);
                    $method = $matches[2];

                    $fullClassName = 'App\\Http\\Controllers\\' . $controllerClass;
                    $controllerPath = $this->classToPath($fullClassName);

                    if (! File::exists($controllerPath)) {
                        $brokenRoutes[] = [
                            'file' => $routeFile,
                            'line' => $lineNumber + 1,
                            'issue' => "Controller not found: {$fullClassName}",
                            'code' => trim($line),
                        ];
                        $this->stats['routes']['broken']++;
                    } elseif (! $this->methodExistsInFile($controllerPath, $method)) {
                        $brokenRoutes[] = [
                            'file' => $routeFile,
                            'line' => $lineNumber + 1,
                            'issue' => "Method '{$method}' not found in {$fullClassName}",
                            'code' => trim($line),
                        ];
                        $this->stats['routes']['broken']++;
                    }
                }
            }
        }

        // Display results
        if (! empty($brokenRoutes)) {
            $this->error("  ❌ Found {$this->stats['routes']['broken']} broken route(s):");
            foreach ($brokenRoutes as $route) {
                $this->line("    - {$route['file']}:{$route['line']} - {$route['issue']}");
                if ($this->option('detailed')) {
                    $this->line("      Code: {$route['code']}");
                }
            }
        } else {
            $this->info('  ✓ All routes are properly configured');
        }

        $this->newLine();
    }

    /**
     * Analyze services for unused classes
     */
    private function analyzeServices(): void
    {
        $this->info('⚙️  Analyzing Services...');

        $servicesPath = app_path('Services');
        if (! File::exists($servicesPath)) {
            $this->warn('Services directory not found');

            return;
        }

        $services = $this->getPhpFiles($servicesPath);
        $this->stats['services']['total'] = count($services);

        $searchPaths = [
            app_path('Http/Controllers'),
            app_path('Console/Commands'),
            app_path('Jobs'),
            app_path('Listeners'),
        ];

        $unusedServices = [];

        foreach ($services as $service) {
            $className = $this->getClassName($service);
            $namespace = $this->getNamespace($service);
            $fullClassName = $namespace . '\\' . $className;
            $isUsed = false;

            foreach ($searchPaths as $searchPath) {
                if (! File::exists($searchPath)) {
                    continue;
                }

                $files = $this->getPhpFiles($searchPath);
                foreach ($files as $file) {
                    if ($file === $service) {
                        continue;
                    }

                    $content = File::get($file);

                    // Check for various usage patterns similar to isModelUsed
                    if ($this->isClassUsed($className, $fullClassName, $content)) {
                        $isUsed = true;
                        break 2;
                    }
                }
            }

            // Check in service providers
            $providersPath = app_path('Providers');
            if (! $isUsed && File::exists($providersPath)) {
                $providers = $this->getPhpFiles($providersPath);
                foreach ($providers as $provider) {
                    $content = File::get($provider);
                    if ($this->isClassUsed($className, $fullClassName, $content)) {
                        $isUsed = true;
                        break;
                    }
                }
            }

            if (! $isUsed) {
                $unusedServices[] = $service;
                $this->stats['services']['unused']++;
            }
        }

        // Display results
        if (! empty($unusedServices)) {
            $this->warn("  ⚠️  Found {$this->stats['services']['unused']} unused service(s):");
            foreach ($unusedServices as $service) {
                $this->line('    - ' . $this->getRelativePath($service));
            }
        } else {
            $this->info('  ✓ All services are being used');
        }

        $this->newLine();
    }

    /**
     * Analyze jobs for unused classes
     */
    private function analyzeJobs(): void
    {
        $this->info('💼 Analyzing Jobs...');

        $jobsPath = app_path('Jobs');
        if (! File::exists($jobsPath)) {
            $this->warn('Jobs directory not found');

            return;
        }

        $jobs = $this->getPhpFiles($jobsPath);
        $this->stats['jobs']['total'] = count($jobs);

        $searchPaths = [
            app_path('Http/Controllers'),
            app_path('Console/Commands'),
            app_path('Services'),
            app_path('Listeners'),
        ];

        $unusedJobs = [];

        foreach ($jobs as $job) {
            $className = $this->getClassName($job);
            $isUsed = false;

            foreach ($searchPaths as $searchPath) {
                if (! File::exists($searchPath)) {
                    continue;
                }

                $files = $this->getPhpFiles($searchPath);
                foreach ($files as $file) {
                    if ($file === $job) {
                        continue;
                    }

                    $content = File::get($file);

                    // Check for dispatch() calls or job class usage
                    if (preg_match("/\b{$className}\b/", $content) ||
                        preg_match("/dispatch\s*\(\s*new\s+{$className}/", $content)) {
                        $isUsed = true;
                        break 2;
                    }
                }
            }

            if (! $isUsed) {
                $unusedJobs[] = $job;
                $this->stats['jobs']['unused']++;
            }
        }

        // Display results
        if (! empty($unusedJobs)) {
            $this->warn("  ⚠️  Found {$this->stats['jobs']['unused']} unused job(s):");
            foreach ($unusedJobs as $job) {
                $this->line('    - ' . $this->getRelativePath($job));
            }
        } else {
            $this->info('  ✓ All jobs are being used');
        }

        $this->newLine();
    }

    /**
     * Analyze console commands for unused classes
     */
    private function analyzeCommands(): void
    {
        $this->info('⌨️  Analyzing Console Commands...');

        $commandsPath = app_path('Console/Commands');
        if (! File::exists($commandsPath)) {
            $this->warn('Commands directory not found');

            return;
        }

        $commands = $this->getPhpFiles($commandsPath);
        $this->stats['commands']['total'] = count($commands);

        foreach ($commands as $command) {
            $className = $this->getClassName($command);
            $commandContent = File::get($command);

            // Extract signature
            preg_match('/protected\s+\$signature\s*=\s*[\'"]([^\'"]+)[\'"]/', $commandContent, $matches);
            $signature = $matches[1] ?? null;

            // Skip if no signature found
            if (! $signature) {
                continue;
            }

            // Commands are auto-discovered in Laravel 5.5+
            // All commands with signatures are automatically registered
        }

        // Display results - commands are auto-discovered in Laravel, so this is informational
        $this->info('  ✓ All commands appear to be properly registered (auto-discovery enabled)');

        $this->newLine();
    }

    /**
     * Analyze API controllers for unused classes
     */
    private function analyzeApiControllers(): void
    {
        $this->info('🔌 Analyzing API Controllers...');

        $apiControllersPath = app_path('Http/Controllers/Api');
        if (! File::exists($apiControllersPath)) {
            $this->warn('API Controllers directory not found');

            return;
        }

        $apiControllers = $this->getPhpFiles($apiControllersPath);
        $this->stats['api_controllers']['total'] = count($apiControllers);

        $apiRoutePath = base_path('routes/api.php');
        if (! File::exists($apiRoutePath)) {
            $this->warn('API routes file not found');

            return;
        }

        $apiRouteContent = File::get($apiRoutePath);
        $unusedApiControllers = [];

        foreach ($apiControllers as $controller) {
            $className = $this->getClassName($controller);
            $namespace = $this->getNamespace($controller);
            $fullClassName = $namespace . '\\' . $className;

            // Check if controller is referenced in API routes
            $isUsed = $this->isControllerUsedInRoutes($className, $fullClassName, $apiRouteContent);

            if (! $isUsed) {
                $unusedApiControllers[] = $controller;
                $this->stats['api_controllers']['unused']++;
            }
        }

        // Display results
        if (! empty($unusedApiControllers)) {
            $this->warn("  ⚠️  Found {$this->stats['api_controllers']['unused']} unused API controller(s):");
            foreach ($unusedApiControllers as $controller) {
                $this->line('    - ' . $this->getRelativePath($controller));
            }
        } else {
            $this->info('  ✓ All API controllers are being used');
        }

        $this->newLine();
    }

    /**
     * Display comprehensive statistics
     */
    private function displayStatistics(): void
    {
        $this->info('📊 Analysis Statistics:');
        $this->newLine();

        $headers = ['Component', 'Total', 'Unused', 'Usage Rate'];
        $rows = [];

        foreach ($this->stats as $component => $data) {
            $total = $data['total'];
            $unused = $data['unused'] ?? 0;
            $used = $total - $unused;
            $usageRate = $total > 0 ? round(($used / $total) * 100, 1) . '%' : 'N/A';

            $componentName = ucfirst(str_replace('_', ' ', $component));
            $rows[] = [
                $componentName,
                $total,
                $unused > 0 ? "<fg=yellow>{$unused}</>" : $unused,
                $usageRate,
            ];

            // Add additional row for method stats if available
            if (isset($data['methods_unused']) && $data['methods_unused'] > 0) {
                $rows[] = [
                    '  └─ Unused Methods',
                    '-',
                    "<fg=yellow>{$data['methods_unused']}</>",
                    '-',
                ];
            }
        }

        $this->table($headers, $rows);

        // Summary
        $totalComponents = array_sum(array_column($this->stats, 'total'));
        $totalUnused = array_sum(array_column($this->stats, 'unused'));
        $totalBroken = $this->stats['routes']['broken'];

        $this->newLine();
        if ($totalUnused > 0 || $totalBroken > 0) {
            $this->warn("⚠️  Found {$totalUnused} unused components and {$totalBroken} broken routes out of {$totalComponents} total components.");
            $this->line('   Consider reviewing and removing unused code to improve maintainability.');
        } else {
            $this->info("✅ Excellent! All {$totalComponents} components are being actively used.");
        }
    }

    /**
     * Get all PHP files from a directory recursively
     */
    private function getPhpFiles(string $directory): array
    {
        if (! File::exists($directory)) {
            return [];
        }

        $files = [];
        $items = File::allFiles($directory);

        foreach ($items as $item) {
            if ($item->getExtension() === 'php') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get all Blade files from a directory recursively
     */
    private function getBladeFiles(string $directory): array
    {
        if (! File::exists($directory)) {
            return [];
        }

        $files = [];
        $items = File::allFiles($directory);

        foreach ($items as $item) {
            if (Str::endsWith($item->getFilename(), '.blade.php')) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get class name from file
     */
    private function getClassName(string $filePath): string
    {
        $content = File::get($filePath);

        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        return basename($filePath, '.php');
    }

    /**
     * Get namespace from file
     */
    private function getNamespace(string $filePath): string
    {
        $content = File::get($filePath);

        if (preg_match('/namespace\s+([\w\\\\]+);/', $content, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Check if controller is used in routes
     */
    private function isControllerUsedInRoutes(string $className, string $fullClassName, string $routeContent): bool
    {
        // Check for [ClassName::class usage
        if (preg_match("/\[{$className}::class/", $routeContent)) {
            return true;
        }

        // Check for 'ClassName@method' usage (old Laravel style)
        if (preg_match("/['\"].*{$className}@/", $routeContent)) {
            return true;
        }

        // Check for use statements
        $usePattern = "/use\s+" . preg_quote($fullClassName, '/') . ';/';
        if (preg_match($usePattern, $routeContent)) {
            return true;
        }

        return false;
    }

    /**
     * Find unused methods in a controller
     */
    private function findUnusedControllerMethods(string $controllerPath, string $routeContent): array
    {
        $content = File::get($controllerPath);
        $unusedMethods = [];

        // Find all public methods
        preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches);
        $methods = $matches[1];

        // Filter out magic methods and common Laravel methods
        $excludeMethods = ['__construct', '__invoke', 'middleware', 'authorize', 'callAction'];

        foreach ($methods as $method) {
            if (in_array($method, $excludeMethods)) {
                continue;
            }

            // Check if method is referenced in routes
            $patterns = [
                "/['\"]" . preg_quote($method, '/') . "['\"]/",
                "/{$method}\s*\(/",
            ];

            $isUsed = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $routeContent)) {
                    $isUsed = true;
                    break;
                }
            }

            if (! $isUsed) {
                $unusedMethods[] = $method;
            }
        }

        return $unusedMethods;
    }

    /**
     * Check if class is used in content (for services, jobs, etc.)
     */
    private function isClassUsed(string $className, string $fullClassName, string $content): bool
    {
        $escapedClassName = preg_quote($className, '/');
        $escapedFullClassName = preg_quote($fullClassName, '/');

        // Check for various usage patterns
        $patterns = [
            "/\b{$escapedClassName}::/",                         // Static calls
            "/new\s+{$escapedClassName}\s*\(/",                  // Instantiation
            "/\b{$escapedClassName}\s+\$/",                      // Type hints
            "/use\s+" . str_replace('\\\\', '\\\\', $escapedFullClassName) . ';/',  // Use statements
            "/@var\s+{$escapedClassName}/",                      // PHPDoc
            "/@param\s+{$escapedClassName}/",                    // PHPDoc
            "/@return\s+{$escapedClassName}/",                   // PHPDoc
            "/:\s*{$escapedClassName}\s*[\|\)]/",                // Return types
            "/dispatch\s*\(\s*new\s+{$escapedClassName}/",       // Job dispatch
            "/{$escapedClassName}::dispatch/",                   // Job static dispatch
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if model is used in content
     */
    private function isModelUsed(string $modelName, string $content): bool
    {
        $escapedModelName = preg_quote($modelName, '/');

        // Check for various usage patterns
        $patterns = [
            "/\b{$escapedModelName}::/",                    // Static calls
            "/new\s+{$escapedModelName}\s*\(/",             // Instantiation
            "/\b{$escapedModelName}\s+\$/",                 // Type hints
            "/use\s+[^;]*\\\\{$escapedModelName};/",        // Use statements
            "/@var\s+{$escapedModelName}/",                 // PHPDoc
            "/@param\s+{$escapedModelName}/",               // PHPDoc
            "/@return\s+{$escapedModelName}/",              // PHPDoc
            "/:\s*{$escapedModelName}\s*[\|\)]/",           // Return types
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get view name from file path
     */
    private function getViewName(string $viewPath, string $basePath): string
    {
        $relativePath = str_replace($basePath . '/', '', $viewPath);
        $relativePath = str_replace('.blade.php', '', $relativePath);

        return str_replace('/', '.', $relativePath);
    }

    /**
     * Convert class name to file path
     */
    private function classToPath(string $className): string
    {
        $className = str_replace('App\\', '', $className);
        $className = str_replace('\\', '/', $className);

        return app_path($className . '.php');
    }

    /**
     * Check if method exists in file
     */
    private function methodExistsInFile(string $filePath, string $methodName): bool
    {
        if (! File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);

        return (bool) preg_match("/function\s+{$methodName}\s*\(/", $content);
    }

    /**
     * Resolve class name from use statements
     */
    private function resolveClassName(string $className, string $content): string
    {
        // If already fully qualified
        if (Str::startsWith($className, 'App\\')) {
            return $className;
        }

        // Try to find in use statements
        $escapedClassName = preg_quote($className, '/');
        $pattern = "/use\s+([^;]*\\\\{$escapedClassName});/";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }

        // Try common namespace
        return 'App\\Http\\Controllers\\' . $className;
    }

    /**
     * Get relative path from base path
     */
    private function getRelativePath(string $path): string
    {
        return str_replace(base_path() . '/', '', $path);
    }
}
