<?php

namespace LongAoDai\Repositories\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Class CreatePatternCommand
 *
 * @package LongAoDai\Repositories\Console
 *
 * @author vochilong <vochilong.work@gmail.com>
 */
class CreatePatternCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'setup:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';

    /**
     * Path default repository
     */
    const PATH_REPOSITORY = 'Repositories';
    /**
     * Type interface
     */
    const TYPE_INTERFACE = 'interface';
    /**
     * Type class
     */
    const TYPE_CLASS = 'class';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;
    /**
     * The namespace of class and interface
     *
     * @var mixed
     */
    private $namespace;
    /**
     * Path directory repository created
     *
     * @var mixed
     */
    private $pathDirectoryRepository;
    /**
     * Path get interface repository stub
     *
     * @var mixed
     */
    private $interfaceRepositoryStub;
    /**
     * Path get class repository stub
     *
     * @var mixed
     */
    private $implementRepositoryStub;
    /**
     * Path get interface provider stub
     *
     * @var mixed
     */
    private $providerRepositoryStub;

    /**
     * @param Filesystem $files
     *
     * @return void
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get stubs
     *
     * @return void
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function getStub()
    {
        $this->interfaceRepositoryStub = __DIR__ . '/stubs/interface.repository.stub';
        $this->implementRepositoryStub = __DIR__ . '/stubs/implement.repository.stub';
        $this->providerRepositoryStub = __DIR__ . '/stubs/provider.repository.stub';
    }

    /**
     * Handle execute
     *
     * @return void
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    public function handle()
    {
        $this->getStub();
        $this->setNamespace();
        $this->setPathDirectoryRepository();
        $this->createRepositoryFile();
        $this->createServiceProvider();
        exec('composer dump-autoload > /dev/null 2>&1');
        $this->info("Repository {$this->getNameInput()} created successfully !!!");
        $this->info('Implement: ' . $this->namespace . 'EloquentRepository.php');
        $this->info('Interface: ' . $this->namespace . 'RepositoryInterface.php');
    }

    /**
     * Handle create base service provider for repository bind
     *
     * @return void
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    protected function createServiceProvider()
    {
        $pathRepositoryProvider = $this->laravel['path'] . '/Providers/RepositoryServiceProvider.php';

        if (!$this->files->exists($pathRepositoryProvider)) {
            $stub = $this->files->get($this->providerRepositoryStub);
        } else {
            $stub = $this->files->get($pathRepositoryProvider);
        }

        $this->files->put($pathRepositoryProvider, $this->prepareStub($stub));
    }

    /**
     * Create repository file class and interface
     *
     * @return void
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function createRepositoryFile()
    {
        $folderRepository = $this->makeDirectory($this->pathDirectoryRepository);
        $fileRepositoryClass = $folderRepository . '/' . $this->getNameInput() . 'EloquentRepository.php';
        $fileRepositoryInterface = $folderRepository . '/' . $this->getNameInput() . 'RepositoryInterface.php';

        $this->files->put($fileRepositoryClass, $this->buildFileContent(self::TYPE_CLASS));
        $this->files->put($fileRepositoryInterface, $this->buildFileContent(self::TYPE_INTERFACE));
    }

    /**
     * Handle build content for file class and interface
     *
     * @param $type
     *
     * @return array|string|string[]
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function buildFileContent($type)
    {
        $pathFile = $this->implementRepositoryStub;

        if ($type == self::TYPE_INTERFACE) {
            $pathFile = $this->interfaceRepositoryStub;
        }

        $stub = $this->files->get($pathFile);

        return $this->prepareStub($stub);
    }

    /**
     * Prepare variable in stubs file
     *
     * @param $stub
     *
     * @return array|string|string[]
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function prepareStub($stub)
    {
        $repositoryInterfaceName = $this->getNameInput() . 'RepositoryInterface';
        $repositoryClassName = $this->getNameInput() . 'EloquentRepository';
        $interfaceNamespace = 'use ' . $this->namespace . '\\' . $repositoryInterfaceName . ';' . "\n" . '#InterfaceNamespace';
        $classNamespace = 'use ' . $this->namespace . '\\' . $repositoryClassName . ';' . "\n" . '#ClassNamespace';
        $interfaceSingletonProvider = '$this->app->singleton(' .
            ($repositoryInterfaceName . '::class') .
            ', ' .
            ($repositoryClassName . '::class') .
            ');' . "\n\t\t" . '#Singleton';
        $interfaceProvider = ($repositoryInterfaceName . '::class') . ',' . "\n\t\t\t" . '#InterfaceProvides';

        return str_replace(
            ['#Namespace', '#InterfaceNamespace', '#ClassNamespace', '#RepositoryInterfaceName', '#RepositoryClassName', '#InterfaceProvides', '#Singleton'],
            [
                $this->namespace,
                $interfaceNamespace,
                $classNamespace,
                $repositoryInterfaceName,
                $repositoryClassName,
                $interfaceProvider,
                $interfaceSingletonProvider,
            ],
            $stub
        );
    }

    /**
     * Get name input -> Name file
     *
     * @return string
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Make directory repository
     *
     * @param $path
     *
     * @return mixed
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    protected function makeDirectory($path)
    {
        if ($this->files->exists($path)) {
            $this->error("Repository {$this->getNameInput()} already exist !");

            exit();
        }

        $this->files->makeDirectory($path, 0777, true, true);

        return $path;
    }

    /**
     * Get path directory base. Where directory created
     *
     * @return string
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    protected function getPathDirectoryBase()
    {
        return config('pattern.path_repository', self::PATH_REPOSITORY) . '/';
    }

    /**
     * Prepare name space by path
     *
     * @param $folder
     *
     * @return string
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function prepareNamespaceByPath($folder)
    {
        return implode('\\', explode('/', $folder));
    }

    /**
     * Set path directory repository
     *
     * @return $this
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function setPathDirectoryRepository()
    {
        $this->pathDirectoryRepository = $this->laravel['path'] . '/' . $this->getPathDirectoryBase() . $this->getNameInput();

        return $this;
    }

    /**
     * Set namespace for file class and interface
     *
     * @return $this
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    private function setNamespace()
    {
        $this->namespace = $this->laravel->getNamespace() . $this->prepareNamespaceByPath($this->getPathDirectoryBase()) . $this->getNameInput();

        return $this;
    }
}
