<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 1.0.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Test\TestCase\Policy\Exception;

use Authorization\Policy\Exception\MissingPolicyException;
use Cake\Datasource\QueryInterface;
use Cake\TestSuite\TestCase;
use TestApp\Model\Table\ArticlesTable;

class MissingPolicyExceptionTest extends TestCase
{
    public function testConstructQueryInstance(): void
    {
        $articles = new ArticlesTable();
        $query = $this->createMock(QueryInterface::class);
        $query->method('getRepository')
            ->willReturn($articles);
        $missingPolicyException = new MissingPolicyException($query);
        $needle = 'This resource looks like a Query, if you are using the OrmResolver, ' .
            'you might need to create a new policy class for your TestApp\Model\Table\ArticlesTable ' .
            'class in `src/Policy/`';
        $this->assertTextContains($needle, $missingPolicyException->getMessage());
    }

    public function testConstructAnotherInstance(): void
    {
        $missingPolicyException = new MissingPolicyException(new \stdClass());
        $needle = 'Policy for `stdClass` has not been defined.';
        $this->assertSame($needle, $missingPolicyException->getMessage());
    }
}
