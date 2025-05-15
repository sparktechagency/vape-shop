<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepoServiceCommand extends Command
{
    protected $signature = 'make:repository {name}';

    protected $description = 'Create Interface, Repository and Service files at once';

    public function handle()
    {
        $inputName = $this->argument('name');

        // Convert backslash to directory separator
        $pathParts = explode('\\', $inputName);

        // Keep the last part of the class name
        $className = array_pop($pathParts);

        // Build the path for subfolder
        $subFolderPath = implode(DIRECTORY_SEPARATOR, $pathParts);

        // Interface folder and file paths
        $interfaceDir = app_path('Repositories' . DIRECTORY_SEPARATOR . 'Contracts' . ($subFolderPath ? DIRECTORY_SEPARATOR . $subFolderPath : ''));
        $repoDir = app_path('Repositories' . ($subFolderPath ? DIRECTORY_SEPARATOR . $subFolderPath : ''));
        $serviceDir = app_path('Services' . ($subFolderPath ? DIRECTORY_SEPARATOR . $subFolderPath : ''));

        if (!File::exists($interfaceDir)) File::makeDirectory($interfaceDir, 0755, true);
        if (!File::exists($repoDir)) File::makeDirectory($repoDir, 0755, true);
        if (!File::exists($serviceDir)) File::makeDirectory($serviceDir, 0755, true);

        $interfaceFile = $interfaceDir . DIRECTORY_SEPARATOR . $className . 'Interface.php';
        $repoFile = $repoDir . DIRECTORY_SEPARATOR . $className . 'Repository.php';
        $serviceFile = $serviceDir . DIRECTORY_SEPARATOR . $className . 'Service.php';

        // Build the path for subfolder
        $interfaceNamespace = 'App\Interfaces' . ($pathParts ? '\\' . implode('\\', $pathParts) : '');
        $repoNamespace = 'App\Repositories' . ($pathParts ? '\\' . implode('\\', $pathParts) : '');
        $serviceNamespace = 'App\Services' . ($pathParts ? '\\' . implode('\\', $pathParts) : '');

        // Use namespace in interface content
        $interfaceContent = "<?php

namespace {$interfaceNamespace};

interface {$className}Interface
{
    public function all();
    public function find(\$id);
    public function create(array \$data);
    public function update(\$id, array \$data);
    public function delete(\$id);
}
";

        // Repository content
        $repoContent = "<?php

namespace {$repoNamespace};

use {$interfaceNamespace}\\{$className}Interface;

class {$className}Repository implements {$className}Interface
{
    public function all()
    {
        // Implement all() method
    }

    public function find(\$id)
    {
        // Implement find() method
    }

    public function create(array \$data)
    {
        // Implement create() method
    }

    public function update(\$id, array \$data)
    {
        // Implement update() method
    }

    public function delete(\$id)
    {
        // Implement delete() method
    }
}
";

        // Service content
        $serviceContent = "<?php

namespace {$serviceNamespace};

use {$interfaceNamespace}\\{$className}Interface;

class {$className}Service
{
    protected \$repository;

    public function __construct({$className}Interface \$repository)
    {
        \$this->repository = \$repository;
    }

    public function all()
    {
        return \$this->repository->all();
    }

    public function find(\$id)
    {
        return \$this->repository->find(\$id);
    }

    public function create(array \$data)
    {
        return \$this->repository->create(\$data);
    }

    public function update(\$id, array \$data)
    {
        return \$this->repository->update(\$id, \$data);
    }

    public function delete(\$id)
    {
        return \$this->repository->delete(\$id);
    }
}
";

        File::put($interfaceFile, $interfaceContent);
        File::put($repoFile, $repoContent);
        File::put($serviceFile, $serviceContent);

        $this->info("Interface, Repository, and Service for {$inputName} created successfully!");
    }
}
