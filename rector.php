<?php
declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector;

$cacheDir = getenv('RECTOR_CACHE_DIR') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rector';

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])

    ->withCache(
        cacheClass: FileCacheStorage::class,
        cacheDirectory: $cacheDir,
    )

    ->withPhpSets()
    ->withAttributesSets()

    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::TYPE_DECLARATION,
    ])

    ->withSkip([
        __DIR__ . '/tests/comparisons',
        ClassPropertyAssignToConstructorPromotionRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        ClosureToArrowFunctionRector::class,
        RemoveUselessReturnTagRector::class,
        CompactToVariablesRector::class,
        ReturnTypeFromStrictFluentReturnRector::class,
        SplitDoubleAssignRector::class,
        NewlineAfterStatementRector::class,
        ExplicitBoolCompareRector::class,
        TypedPropertyFromCreateMockAssignRector::class,

        // Parent SimpleBakeCommand::buildOptionParser() switched from public
        // to protected in cakephp/bake 3.6.4. Narrowing this override would
        // break installs on bake 3.2 - 3.6.3 since PHP forbids reducing a
        // parent's visibility. Keeping the override public is forward- and
        // backward-compatible (widening is always allowed).
        MakeInheritedMethodVisibilitySameAsParentRector::class => [
            __DIR__ . '/src/Command/PolicyCommand.php',
        ],
    ]);
