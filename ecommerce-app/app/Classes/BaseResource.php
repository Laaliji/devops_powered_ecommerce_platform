<?php

namespace App\Classes;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

class BaseResource extends Resource
{
    protected static ?string $navigationIcon = 'tabler-point-filled';

    public static function langFile(): string
    {
        return str(parent::getSlug())->explode('/')->last();
    }

    public static function getModelLabel(): string
    {
        return __(static::langFile() . '.titleSingle');
    }

    public static function getPluralModelLabel(): string
    {
        return __(static::langFile() . '.title');
    }

    // @phpstan-ignore-next-line
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // if (static::getModel()::isUsingSoftDelete()) {
        //     $query
        //         ->withoutGlobalScopes([
        //             SoftDeletingScope::class,
        //         ]);
        // }

        if (static::getModel()::isUsingActionBy()) {
            $query->with(['createdBy', 'updatedBy']);
        }

        return $query;
    }

    /**
     * Validate that searchable attributes exist in the database schema
     * This method should be used in tests to catch configuration errors early
     */
    public static function validateGlobalSearchableAttributes(): array
    {
        $errors = [];

        if (! static::canGloballySearch()) {
            return $errors;
        }

        $modelClass = static::getModel();
        if (! class_exists($modelClass)) {
            return ["Model class {$modelClass} does not exist"];
        }

        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();

        foreach (static::getGloballySearchableAttributes() as $attribute) {
            if (is_string($attribute) && str_contains($attribute, '.')) {
                // Validate relationship attributes (e.g., 'user.name')
                [$relation, $column] = explode('.', $attribute, 2);

                if (! method_exists($modelInstance, $relation)) {
                    $errors[] = "Relationship method '{$relation}' does not exist on model " . class_basename($modelClass);

                    continue;
                }

                try {
                    $relationship = $modelInstance->$relation();
                    $relatedModel = $relationship->getRelated();
                    $relatedTable = $relatedModel->getTable();

                    if (! Schema::hasColumn($relatedTable, $column)) {
                        $errors[] = "Column '{$column}' does not exist in related table '{$relatedTable}' for relationship '{$relation}'";
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Could not validate relationship '{$relation}': " . $e->getMessage();
                }
            } else {
                // Validate direct column attributes
                if (! Schema::hasColumn($tableName, $attribute)) {
                    $errors[] = "Column '{$attribute}' does not exist in table '{$tableName}'";
                }
            }
        }

        return $errors;
    }
}
