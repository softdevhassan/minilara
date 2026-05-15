<?php
namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Industrial Grade Database Facade
 * With Medoo Backward Compatibility Layer (Enhanced)
 */
class DB {
    public static function table($table) {
        return Capsule::table($table);
    }

    public static function pdo() {
        return Capsule::connection()->getPdo();
    }

    public static function getInstance() {
        return new self();
    }

    public function id() {
        return Capsule::connection()->getPdo()->lastInsertId();
    }

    public function count($table, $join = null, $where = null) {
        $query = Capsule::table($table);
        if (is_array($join) && count($join) > 0 && str_contains(array_keys($join)[0], '[')) {
            $this->applyJoins($query, $join, $table);
            $this->applyWhere($query, $where);
        } else {
            $this->applyWhere($query, $join);
        }
        return $query->count();
    }

    private function prepareColumns($columns) {
        if (!is_array($columns)) return [$columns];
        $prepared = [];
        foreach ($columns as $col) {
            // Detect Medoo-style aliases: "col(alias)"
            if (preg_match('/(?<real>.*)\((?<alias>.*)\)/', $col, $m)) {
                $real = trim($m['real']);
                $alias = trim($m['alias']);
                // If it's a function like COUNT(id), it already has parens, 
                // but Medoo alias syntax is often "id(total)" or "COUNT(id)(total)"
                // Our regex above would catch "COUNT(id)(total)" as real="COUNT(id)" and alias="total"
                $prepared[] = Capsule::raw("$real as $alias");
            } elseif (str_contains($col, '(') || str_contains($col, ' ') || str_contains($col, 'CASE')) {
                // It's a RAW expression or already has an "AS"
                $prepared[] = Capsule::raw($col);
            } else {
                $prepared[] = $col;
            }
        }
        return $prepared;
    }

    public function get($table, $columns = '*', $where = []) {
        if (is_array($columns) && count($columns) > 0 && str_contains(array_keys($columns)[0], '[')) {
            $joins = $columns;
            $cols = $this->prepareColumns($where);
            $finalWhere = func_num_args() > 3 ? func_get_arg(3) : [];
            
            $query = Capsule::table($table);
            $this->applyJoins($query, $joins, $table);
            $this->applyWhere($query, $finalWhere);
            $res = $query->first($cols);
            return $res ? (array)$res : null;
        }

        $query = Capsule::table($table);
        $this->applyWhere($query, is_array($columns) ? $where : $columns);
        $res = $query->first($this->prepareColumns($columns));
        return $res ? (array)$res : null;
    }

    public function select($table, $columns = '*', $where = []) {
        $query = Capsule::table($table);
        
        if (is_array($columns) && count($columns) > 0 && str_contains(array_keys($columns)[0], '[')) {
            $joins = $columns;
            $cols = $this->prepareColumns($where);
            $finalWhere = func_num_args() > 3 ? func_get_arg(3) : [];
            
            $this->applyJoins($query, $joins, $table);
            $this->applyWhere($query, $finalWhere);
            $res = $query->get($cols);
        } else {
            $this->applyWhere($query, $where);
            $res = $query->get($this->prepareColumns($columns));
        }

        return $res->map(fn($item) => (array)$item)->all();
    }

    public function insert($table, $data) {
        return Capsule::table($table)->insert($data);
    }

    public function update($table, $data, $where = []) {
        $query = Capsule::table($table);
        $this->applyWhere($query, $where);
        return $query->update($data);
    }

    public function delete($table, $where = []) {
        $query = Capsule::table($table);
        $this->applyWhere($query, $where);
        return $query->delete();
    }

    private function applyJoins($query, $joins, $mainTable) {
        foreach ($joins as $join => $on) {
            if (!preg_match('/\[(?<type>.*)\](?<table>.*)/', $join, $match)) continue;

            $type = $match['type'];
            $tableRaw = trim($match['table']);
            
            // Handle table(alias) or table as alias
            $table = $tableRaw;
            $alias = null;
            if (preg_match('/(?<table>.*)\((?<alias>.*)\)/', $tableRaw, $m)) {
                $table = trim($m['table']);
                $alias = trim($m['alias']);
            } elseif (preg_match('/(?<table>.*)\s+as\s+(?<alias>.*)/i', $tableRaw, $m)) {
                $table = trim($m['table']);
                $alias = trim($m['alias']);
            }

            $tableWithAlias = $alias ? "$table as $alias" : $table;
            $target = $alias ?: $table;

            $local = array_keys($on)[0];
            $foreign = array_values($on)[0];
            
            // Check if local already has a table prefix
            $localQualified = str_contains($local, '.') ? $local : "$mainTable.$local";
            $foreignQualified = str_contains($foreign, '.') ? $foreign : "$target.$foreign";

            switch ($type) {
                case '>': // Medoo Left Join [>]
                case '>]':
                    $query->leftJoin($tableWithAlias, $localQualified, '=', $foreignQualified);
                    break;
                case '<': // Medoo Right Join [<]
                case '[<':
                    $query->rightJoin($tableWithAlias, $localQualified, '=', $foreignQualified);
                    break;
                case '<>': // Medoo Inner Join [<>]
                case '><': // Medoo Full Join (approximated as Inner)
                    $query->join($tableWithAlias, $localQualified, '=', $foreignQualified);
                    break;
                default:
                    $query->leftJoin($tableWithAlias, $localQualified, '=', $foreignQualified);
            }
        }
    }

    private function buildWhere($query, $where, $type = 'AND') {
        foreach ($where as $column => $value) {
            if ($column === 'ORDER' || $column === 'LIMIT' || $column === 'GROUP') continue;

            if ($column === 'OR' || $column === 'AND') {
                $method = ($column === 'OR') ? 'orWhere' : 'where';
                $query->{$method}(function($q) use ($value, $column) {
                    $this->buildWhere($q, $value, $column);
                });
                continue;
            }

            $currentMethod = ($type === 'OR') ? 'orWhere' : 'where';

            // Handle [LIKE], [>], [<], etc.
            $operator = '=';
            $cleanCol = $column;
            
            if (preg_match('/(?<col>.*)\[(?<op>.*)\]/', $column, $m)) {
                $cleanCol = trim($m['col']);
                $op = strtoupper($m['op']);
                $operator = ($op === 'LIKE') ? 'LIKE' : $op;
            }

            if (is_array($value) && $operator === '=') {
                $query->{$currentMethod . 'In'}($cleanCol, $value);
            } elseif (is_array($value) && $operator === 'LIKE') {
                $query->{$currentMethod}(function($q) use ($cleanCol, $value) {
                    foreach ($value as $v) {
                        $q->where($cleanCol, 'LIKE', $v);
                    }
                });
            } else {
                $query->{$currentMethod}($cleanCol, $operator, $value);
            }
        }
    }

    private function applyWhere($query, $where) {
        if (!is_array($where) || empty($where)) return;
        
        $this->buildWhere($query, $where);
        
        if (isset($where['ORDER'])) {
            foreach ($where['ORDER'] as $col => $dir) {
                $query->orderBy($col, $dir);
            }
        }

        if (isset($where['LIMIT'])) {
            if (is_array($where['LIMIT'])) {
                $query->offset($where['LIMIT'][0])->limit($where['LIMIT'][1]);
            } else {
                $query->limit($where['LIMIT']);
            }
        }
    }

    public static function __callStatic($method, $args) {
        return Capsule::$method(...$args);
    }

    /**
     * Database Lifecycle Management (Consolidated)
     */
    public static function migrate($fresh = false) {
        if ($fresh) MigrationManager::wipe();
        return MigrationManager::migrate();
    }

    public static function seed() {
        return SeederManager::seed();
    }

    public static function wipe() {
        return MigrationManager::wipe();
    }

    public static function getStatus() {
        $tables = self::pdo()->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $status = [];
        foreach ($tables as $t) {
            $status[$t] = self::table($t)->count();
        }
        return $status;
    }

    public static function isInstalled() {
        try {
            $tables = self::pdo()->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $core = array_map('trim', explode(',', $_ENV['CORE_TABLES'] ?? 'users, settings'));
            foreach ($core as $t) if (!in_array($t, $tables)) return false;
            return true;
        } catch (\Exception $e) { return false; }
    }
}

/**
 * Universal Zero-Duplication Model Base
 */
class BaseModel extends \Illuminate\Database\Eloquent\Model {
    protected $guarded = []; public $timestamps = false; 
    public function __call($m, $p) {
        $t = $this->getTable(); $sP = BASE_PATH . '/app/database/schema.php';
        if (file_exists($sP)) {
            $s = require $sP;
            if (isset($s[$t]['relations'][$m])) {
                $r = $s[$t]['relations'][$m];
                $target = "App\\Models\\" . \Illuminate\Support\Str::studly(\Illuminate\Support\Str::singular($r[1]));
                return $this->{$r[0]}($target, $r[2]);
            }
        }
        return parent::__call($m, $p);
    }
}

/**
 * Migration & Seeding Engine
 */
abstract class Migration {
    protected $schema;
    public function __construct() { $this->schema = \Illuminate\Database\Capsule\Manager::schema(); }
    abstract public function up();
    abstract public function down();
}

abstract class Seeder {
    abstract public function run();
    protected function table($name) { return \Illuminate\Database\Capsule\Manager::table($name); }
}

class MigrationManager {
    public static function init() {
        if (!\Illuminate\Database\Capsule\Manager::schema()->hasTable('migrations')) {
            \Illuminate\Database\Capsule\Manager::schema()->create('migrations', function ($table) {
                $table->id(); $table->string('migration'); $table->integer('batch');
            });
        }
    }
    public static function migrate() {
        self::init();
        $files = glob(BASE_PATH . '/app/database/migrations/*.php'); sort($files);
        $ran = \Illuminate\Database\Capsule\Manager::table('migrations')->pluck('migration')->toArray();
        $batch = \Illuminate\Database\Capsule\Manager::table('migrations')->max('batch') + 1;
        $count = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php'); if (in_array($name, $ran)) continue;
            $m = require $file; $m->up();
            \Illuminate\Database\Capsule\Manager::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
            $count++;
        }
        return $count;
    }
    public static function rollback() {
        $batch = \Illuminate\Database\Capsule\Manager::table('migrations')->max('batch'); if (!$batch) return 0;
        $rows = \Illuminate\Database\Capsule\Manager::table('migrations')->where('batch', $batch)->orderBy('id', 'desc')->get();
        foreach ($rows as $r) {
            $file = BASE_PATH . "/app/database/migrations/{$r->migration}.php";
            if (file_exists($file)) (require $file)->down();
            \Illuminate\Database\Capsule\Manager::table('migrations')->where('id', $r->id)->delete();
        }
        return count($rows);
    }
    public static function wipe() { 
        $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $t) $pdo->exec("DROP TABLE IF EXISTS `$t` ");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

class SeederManager {
    public static function seed($class = null) {
        $files = $class ? [BASE_PATH . "/app/database/seeds/$class.php"] : glob(BASE_PATH . '/app/database/seeds/*.php');
        foreach ($files as $file) if (file_exists($file)) (require $file)->run();
        return count($files);
    }
}
