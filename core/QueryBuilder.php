<?php

namespace Core;

use PDO;

class QueryBuilder {
    private $pdo;
    private $table;
    private $wheres = [];
    private $bindings = [];
    private $selects = ['*'];
    private $joins = [];
    private $orderBy = [];
    private $groupBy = [];
    private $limit = null;
    private $offset = null;
    
    public function __construct(PDO $pdo, $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }
    
    public function select(...$columns) {
        $this->selects = $columns;
        return $this;
    }
    
    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function orWhere($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        
        return $this;
    }
    
    public function whereIn($column, array $values) {
        $this->wheres[] = [
            'type' => 'whereIn',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function whereNull($column) {
        $this->wheres[] = [
            'type' => 'whereNull',
            'column' => $column,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function whereLike($column, $value) {
        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => 'LIKE',
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function join($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    public function leftJoin($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }
    
    public function groupBy(...$columns) {
        $this->groupBy = array_merge($this->groupBy, $columns);
        return $this;
    }
    
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    public function get() {
        $sql = $this->buildSelectQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
    
    public function first() {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }
    
    public function find($id) {
        return $this->where('id', $id)->first();
    }
    
    public function paginate($perPage = 15, $page = 1) {
        $total = $this->count();
        $offset = ($page - 1) * $perPage;
        
        $items = $this->limit($perPage)->offset($offset)->get();
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    public function insert(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update(array $data) {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set";
        
        $bindings = array_values($data);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    public function delete() {
        $sql = "DELETE FROM {$this->table}";
        
        $bindings = [];
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($this->bindings);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetch()['count'];
    }
    
    private function buildSelectQuery() {
        $columns = implode(', ', $this->selects);
        $sql = "SELECT $columns FROM {$this->table}";
        
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($this->bindings);
        }
        
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    private function buildWhereClause(&$bindings) {
        $conditions = [];
        
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : $where['boolean'] . ' ';
            
            if ($where['type'] === 'where') {
                $conditions[] = "$boolean{$where['column']} {$where['operator']} ?";
                $bindings[] = $where['value'];
            } elseif ($where['type'] === 'whereIn') {
                $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                $conditions[] = "$boolean{$where['column']} IN ($placeholders)";
                $bindings = array_merge($bindings, $where['values']);
            } elseif ($where['type'] === 'whereNull') {
                $conditions[] = "$boolean{$where['column']} IS NULL";
            }
        }
        
        return implode(' ', $conditions);
    }
}