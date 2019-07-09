<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link http://cakephp.org CakePHP(tm) Project
 * @since 1.0.0
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Test\TestCase\Command;

use Cake\Console\Command;
use Cake\Routing\Router;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use Cake\TestSuite\StringCompareTrait;

/**
 * PolicyCommand test class
 */
class PolicyCommandTest extends ConsoleIntegrationTestCase
{
    use StringCompareTrait;

    /**
     * @var string
     */
    protected $generatedFile = '';

    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Router::reload();

        $this->comparisonDir = dirname(dirname(__DIR__)) . DS . 'comparisons' . DS;
        $this->useCommandRunner();
        $this->loadPlugins(['TestPlugin']);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->generatedFile && file_exists($this->generatedFile)) {
            unlink($this->generatedFile);
            $this->generatedFile = '';
        }
    }

    public function testMainDefaultToEntity()
    {
        $this->generatedFile = APP . 'Policy/BookmarkPolicy.php';

        $this->exec('bake policy Bookmark');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
    }

    public function testMainEntityType()
    {
        $this->generatedFile = APP . 'Policy/BookmarkPolicy.php';

        $this->exec('bake policy --type entity Bookmark');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(
            $this->comparisonDir . 'BookmarkEntityPolicy.php',
            file_get_contents($this->generatedFile)
        );
    }

    public function testMainObjectType()
    {
        $this->generatedFile = APP . 'Policy/ThingPolicy.php';

        $this->exec('bake policy --type object Thing');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(
            $this->comparisonDir . 'ThingPolicy.php',
            file_get_contents($this->generatedFile)
        );
    }

    public function testMainTableType()
    {
        $this->generatedFile = APP . 'Policy/BookmarksTablePolicy.php';

        $this->exec('bake policy --type table Bookmarks');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(
            $this->comparisonDir . 'BookmarksTablePolicy.php',
            file_get_contents($this->generatedFile)
        );
    }

    public function testMainPluginEntity()
    {
        $this->generatedFile = ROOT . 'Plugin/TestPlugin/src/Policy/UserPolicy.php';

        $this->exec('bake policy TestPlugin.User');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(
            $this->comparisonDir . 'TestPluginUserEntityPolicy.php',
            file_get_contents($this->generatedFile)
        );
    }

    public function testMainPluginTable()
    {
        $this->generatedFile = ROOT . 'Plugin/TestPlugin/src/Policy/UsersTablePolicy.php';

        $this->exec('bake policy --type table TestPlugin.Users');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Creating file ' . $this->generatedFile);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(
            $this->comparisonDir . 'TestPluginUsersTablePolicy.php',
            file_get_contents($this->generatedFile)
        );
    }
}
