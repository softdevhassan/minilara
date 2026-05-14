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
    public static function migrate($output = null, $force = false) {
        if ($force) self::wipe($output);

        // 1. Apply Schema from schema.php
        $schemaPath = BASE_PATH . '/app/database/schema.php';
        if (file_exists($schemaPath)) {
            $schema = require $schemaPath;
            foreach ($schema as $table => $definition) {
                if (isset($definition['sql'])) {
                    self::pdo()->exec($definition['sql']);
                    if ($output) $output->writeln("- Schema Created: $table");
                }
            }
        }
    }

    public static function seed($output = null) {
        $seederPath = BASE_PATH . '/app/database/seeder.php';
        if (file_exists($seederPath)) {
            $db = self::pdo();
            require $seederPath;
        }
    }

    public static function wipe($output = null) {
        self::pdo()->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = self::pdo()->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            self::pdo()->exec("DROP TABLE IF EXISTS `$t` ");
            if ($output) $output->writeln("- Dropped: $t");
        }
        self::pdo()->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public static function getStatus() {
        $tables = self::pdo()->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $status = [];
        foreach ($tables as $t) {
            $status[$t] = self::table($t)->count();
        }
        return $status;
    }
}
