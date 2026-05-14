<?php
namespace App;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;

/**
 * Universal Zero-Duplication Model (v2)
 * The Lightweight Challenger to Laravel's Complexity
 */
class BaseModel extends EloquentModel {
    protected $guarded = [];
    public $timestamps = false; 

    // Memory Cache for Schema
    protected static $schemaCache = null;

    protected static function getSchema() {
        if (self::$schemaCache === null) {
            self::$schemaCache = require BASE_PATH . '/app/database/schema.php';
        }
        return self::$schemaCache;
    }

    /**
     * Dynamic Relationship Resolver
     * We teach Laravel: Relationships should be automatic!
     */
    public function __call($method, $parameters) {
        $schema = self::getSchema();
        $tableName = $this->getTable();

        if (isset($schema[$tableName]['relations'][$method])) {
            $relation = $schema[$tableName]['relations'][$method];
            $type = $relation[0];
            $targetTable = $relation[1];
            $foreignKey = $relation[2];
            
            $targetClass = "App\\Models\\" . Str::studly(Str::singular($targetTable));
            return $this->$type($targetClass, $foreignKey);
        }

        return parent::__call($method, $parameters);
    }

    public function __get($key) {
        if (!array_key_exists($key, $this->attributes) && $this->hasSchemaRelation($key)) {
            return $this->getRelationValue($key);
        }
        return parent::__get($key);
    }

    private function hasSchemaRelation($key) {
        $schema = self::getSchema();
        return isset($schema[$this->getTable()]['relations'][$key]);
    }
}
