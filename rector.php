<?php
declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\BooleanNot\NegatedAndsToPositiveOrsRector;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ArrayExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\Property\FixClassCaseSensitivityVarDocblockRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveDuplicatedReturnSelfDocblockRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveMixedDocblockOverruledByNativeTypeRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveParentDelegatingClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveReturnTagIncompatibleWithNativeTypeRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessUnionReturnDocblockRector;
use Rector\DeadCode\Rector\Property\RemoveDefaultValueFromAssignedPropertyRector;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveDeadInstanceOfAssertRector;
use Rector\DeadCode\Rector\Ternary\RemoveUselessTernaryRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\NotIdentical\MbStrContainsRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ScalarParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeFromAssertInstanceOfRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromVariableCallRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

$cacheDir = getenv('RECTOR_CACHE_DIR') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rector';

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests/TestCase',
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
        SetList::TYPE_DECLARATION,
    ])

    ->withSkip([
        ThrowWithPreviousExceptionRector::class,
        ExplicitReturnNullRector::class,
        OptionalParametersAfterRequiredRector::class,
        CompleteDynamicPropertiesRector::class,
        ForeachToInArrayRector::class,
        CompactToVariablesRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
        SimplifyIfReturnBoolRector::class,
        AbsolutizeRequireAndIncludePathRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        NewlineBeforeNewAssignSetRector::class,
        StrictArraySearchRector::class,
        VersionCompareFuncCallToConstantRector::class,
        FunctionFirstClassCallableRector::class,
        NewlineAfterStatementRector::class,
        TypedPropertyFromCreateMockAssignRector::class,
        ArrayExplicitBoolCompareRector::class,
        ObjectExplicitBoolCompareRector::class,
        RemoveUselessUnionReturnDocblockRector::class,
        RemoveMixedDocblockOverruledByNativeTypeRector::class,

        // Manual - only appliable for part of the code
        UseIdenticalOverEqualWithSameTypeRector::class,
        StringClassNameToClassConstantRector::class,
        ClassOnObjectRector::class,

        // Newly aggressive in rector 2.4 - keep the bump behavior-neutral:
        // adds declare(strict_types=1) to test fixtures/config (out of scope here),
        SafeDeclareStrictTypesRector::class,
        // and rewrites `$x ?: []` in ways that can change behavior on undefined/empty values.
        RemoveUselessTernaryRector::class,

        // New in rector 2.5 - skipped to keep the version bump behavior-neutral.
        // These mostly rewrite or remove docblocks. Whether to apply them is a
        // separate decision from getting CI green again.
        NegatedAndsToPositiveOrsRector::class,
        FixClassCaseSensitivityVarDocblockRector::class,
        RemoveDuplicatedReturnSelfDocblockRector::class,
        RemoveMixedDocblockOverruledByNativeTypeRector::class,
        RemoveParentDelegatingClassMethodRector::class,
        RemoveReturnTagIncompatibleWithNativeTypeRector::class,
        RemoveUselessUnionReturnDocblockRector::class,
        RemoveDefaultValueFromAssignedPropertyRector::class,
        RemoveDeadInstanceOfAssertRector::class,
        MbStrContainsRector::class,
        ArrayParamTypeByMethodCallTypeRector::class,
        ScalarParamTypeByMethodCallTypeRector::class,
        ClosureReturnTypeFromAssertInstanceOfRector::class,
        AddClosureParamTypeForArrayMapRector::class,
        AddClosureParamTypeFromVariableCallRector::class,
        RemoveUselessReturnTagRector::class,
    ]);
