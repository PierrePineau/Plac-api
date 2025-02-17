<?php

namespace App\Core\Utils;

use DateTimeInterface;
use Exception;

class CustomQuery
{
    private string $from = "";
    private int $maxResults = 100;
    private int $offset;
    private array $tabSelect = [];
    private array $tabWhere = [];
    private string $ConcatWhere = "AND";
    private array $tabHaving = [];
    private array $tabLeftJoin = [];
    private array $tabOrderBy = [];
    private array $tabGroupBy = [];
    private array $parameters = [];
    public function __construct()
    {
        
    }
    public function constructCondition(string $expression,string $operateur,string $value ): string
    {
        return "{$expression} {$operateur} {$value}";
    }

    public function constructTabToString(array $tab, string $expression, ?string $concat = '' ): string
    {
        if(!isset($concat) || $concat == ''){
            $concat = "\n";
        }
        $nbElement = count($tab);
        $resultString = $expression;
        if ($nbElement > 0) {
            $i = 1;
            foreach ($tab as $element) {
                $resultString .= " " . $element;
                // Ajouter un AND/OR après chaque condition sauf si c'est le dernier
                if ($i < $nbElement) {
                    $resultString .= " " .  $concat;
                }
                $i++;
            }
            return $resultString;
        }else{
            return "";
        }
    }

    public function setFrom(string $from, ?string $alias): self
    {
        if (isset($alias) && $alias != '') {
            $from = $from . ' AS ' .$alias;
        }
        $this->from = $from;
        return $this;
    }
    public function getFrom(): string
    {
        if (isset($this->from) && $this->from != "") {
            return "FROM {$this->from}";
        }else{
            return "";
        }
    }

    public function setMaxResults(?int $limit): self
    {
        if (isset($limit) && $limit > 0) {
            $this->maxResults = $limit;
        }else{
            unset($this->maxResults);
        }
        return $this;
    }
    public function getMaxResults(): string
    {
        if (isset($this->maxResults)) {
            return "LIMIT {$this->maxResults}";
        }else{
            return "";
        }
    }

    public function setOffset(int $offset): self
    {
        if (isset($offset) && $offset > 1) {
            $this->offset = $offset;
        }else{
            unset($this->offset);
        }
        return $this;
    }
    public function getOffset(): mixed
    {
        if (isset($this->offset)) {
            return "OFFSET {$this->offset}";
        }else{
            return null;
        }
    }

    public function setFirstResult(int $offset): self
    {
        return $this->setOffset($offset);
    }

    public function setSelect(string $newSelect): self
    {
        // On vide les anciennes valeurs
        $this->tabSelect = [];
        array_push($this->tabSelect, $newSelect);
        return $this;
    }

    public function addSelect(string $newSelect): self
    {
        array_push($this->tabSelect, $newSelect);
        return $this;
    }
    public function getSelect(): string
    {
        return $this->constructTabToString($this->tabSelect, "SELECT", ",");
    }
    public function setConcatWhere(string $concat): self
    {
        if ($concat == "OR" || $concat == "AND") {
            $this->ConcatWhere = $concat;
        }else{
            $this->ConcatWhere = "AND";
        }
        return $this;
    }

    public function andWhere(string $condition): self
    {
        return $this->addWhere($condition);
    }

    public function addWhere(string $condition): self
    {
        if ($condition != '') {
            array_push($this->tabWhere, $condition);
        }
        return $this;
    }
    
    public function getWhere(bool $startWithWhere = true): string
    {
        $where = $startWithWhere ? "WHERE" : "";
        return $this->constructTabToString($this->tabWhere, $where, $this->ConcatWhere);
    }

    public function addGroupBy(string $expression): self
    {
        if ($expression != '') {
            array_push($this->tabGroupBy, $expression);
        }
        return $this;
    }
    public function getGroupBy(): string
    {
        return $this->constructTabToString($this->tabGroupBy, "GROUP BY", ",");
    }

    public function addHaving(string $condition ): self
    {
        if ($condition != '') {
            array_push($this->tabHaving, $condition);
        }
        return $this;
    }
    public function getHaving(): string
    {
        return $this->constructTabToString($this->tabHaving, "HAVING", "AND");
    }

    public function addLeftJoin(string $leftJoin): self
    {
        if ($leftJoin != '') {
            array_push($this->tabLeftJoin, $leftJoin);
        }
        return $this;
    }
    public function getLeftJoin(): string
    {
        return $this->constructTabToString($this->tabLeftJoin, "LEFT JOIN", "\nLEFT JOIN");
    }

    public function addOrderBy(string $expression,string $order): self
    {
        if ($order != 'ASC' && $order != 'DESC' && $order != 'RAND()') {
            $order = 'DESC';
        }
        $orderby = "{$expression} {$order}";
        array_push($this->tabOrderBy, $orderby);
        return $this;
    }
    public function getOrderBy(): string
    {
        return $this->constructTabToString($this->tabOrderBy, "ORDER BY", ",");
    }

    public function setParameter(string $name,mixed $value): self
    {
        if (is_array($value)) {
            $value = implode("','", $value);
        }elseif ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $this->parameters[$name] = $value;
        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getQuery(): string
    {
        $query = "";
        $tabRawSql = [
            "Select" => $this->getSelect(),
            "From" => $this->getFrom(),
            "LeftJoin" => $this->getLeftJoin(),
            "Where" => $this->getWhere(),
            "GroupBy" => $this->getGroupBy(),
            "Having" => $this->getHaving(),
            "Order" => $this->getOrderBy(),
            "MaxResults" => $this->getMaxResults(),
            "offset" => $this->getOffset(),
        ];
        foreach ($tabRawSql as $rawSqlParams) {
            if (isset($rawSqlParams) && $rawSqlParams != '') {
                $query = $query . "{$rawSqlParams}\n";
            }
        }
        return $query;
    }
}
