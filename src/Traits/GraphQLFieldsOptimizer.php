<?php

namespace BBSLab\GraphqlFieldsOptimizer\Traits;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait GraphQLFieldsOptimizer
{
    /**
     * Apply optimized field and relationship selection to a query based on GraphQL ResolveInfo.
     * This method initiates the process by extracting fields and relationships at the top level
     * and delegating deeper levels to a recursive function.
     *
     * @param Builder $query
     * @param ResolveInfo $resolveInfo
     * @param array $overrideFields
     * @return Builder
     * @throws Exception
     */
    public function scopeWithGraphQLFields(Builder $query, ResolveInfo $resolveInfo, array $overrideFields = []): Builder
    {
        $fields = $this->getSelectedFields($resolveInfo, 20);
        $selectedFields = array_merge($fields['fields'], $overrideFields);
        $query->select($this->validateFieldNames($selectedFields));

        foreach ($fields['relations'] as $relation => $details) {
            $foreignKey = $this->getForeignKeyForRelation($relation);
            $subFieldsWithForeignKey = array_merge($details['fields'], ["{$relation}.{$foreignKey}"]);

            $query->with([$relation => function ($query) use ($subFieldsWithForeignKey) {
                $query->select($subFieldsWithForeignKey);
            }]);
        }

        return $query;
    }

    /**
     * Extract fields and relationships from GraphQL ResolveInfo.
     * This is the entry point for field extraction which handles top-level fields and initializes
     * recursive exploration of relations.
     *
     * @param ResolveInfo $resolveInfo
     * @param int $depth Default value to specify the depth of relationships being processed.
     * @return array
     */
    protected function getSelectedFields(ResolveInfo $resolveInfo, int $depth = 1): array
    {
        $fieldSelection = $resolveInfo->getFieldSelection($depth);
        $fields = [];
        $relations = [];

        foreach ($fieldSelection as $field => $subFields) {
            if (is_array($subFields) && count($subFields) > 0) {
                $relations[$field] = $this->getSelectedFieldsRecursive($subFields, $field);
            } else {
                $fields[] = $field;
            }
        }

        $fields = $this->validateFieldNames($fields);

        return [
            'fields' => $fields,
            'relations' => $relations
        ];
    }

    /**
     * Recursively processes nested fields and relationships.
     * This function is called for each relation and continues to call itself for deeper nested relations.
     *
     * @param array $subFields Fields and sub-relations for the current entity.
     * @return array
     */
    private function getSelectedFieldsRecursive(array $subFields): array
    {
        $fields = [];
        $relations = [];

        foreach ($subFields as $field => $subSubFields) {
            if (is_array($subSubFields) && count($subSubFields) > 0) {
                $relations[$field] = $this->getSelectedFieldsRecursive($subSubFields, $field);
            } else {
                $fields[] = $field;
            }
        }

        return [
            'fields' => $fields,
            'relations' => $relations
        ];
    }

    /**
     * Validate field names against model's database columns to prevent SQL injection.
     * This function uses a configuration setting to enable or disable security checks.
     *
     * @param array $fields
     * @return array
     */
    protected function validateFieldNames(array $fields): array
    {
        if (!config('graphql_security.enable_security')) {
            return $fields;
        }

        $validFields = $this->getValidFieldNames();
        return array_intersect($fields, $validFields);
    }

    /**
     * Get valid field names from cache or database schema.
     *
     * @return array
     */
    protected function getValidFieldNames(): array
    {
        $cacheKey = 'valid_fields_' . $this->getTable();
        return Cache::remember($cacheKey, now()->addDay(), function () {
            return Schema::getColumnListing($this->getTable());
        });
    }

    /**
     * Retrieve the foreign key name for a given relation. This method first checks if there is a
     * custom method defined in the model specific to the relation to get the foreign key name.
     * If such a method exists, it uses this custom method. Otherwise, it defaults to using
     * Laravel's built-in method to get the foreign key name from the relationship definition.
     *
     * This approach allows for customization of foreign key names on a per-relation basis,
     * which can be particularly useful in applications where database schema conventions
     * do not follow Laravel's default expectations.
     *
     * @param string $relation The name of the relation for which to get the foreign key name.
     * @return string The foreign key name.
     * @throws Exception
     */
    protected function getForeignKeyForRelation(string $relation): string
    {
        try {
            $method = $relation . 'ForeignKey';
            if (method_exists($this, $method)) {
                return $this->$method();
            }

            return $this->$relation()->getForeignKeyName();
        } catch (Exception $e) {
            Log::error("Failed to retrieve foreign key for relation {$relation}: {$e->getMessage()}");
            throw $e; // Rethrow or handle gracefully
        }
    }
}