<?php

namespace EvLimma\ActionPack;

use EvLimma\ActionPack\DynamicColumns;

class DynamicFilter extends DynamicColumns
{
    private $terms;
    private $params;

    public function __construct()
    {
        $this->terms = null;
        $this->params = null;
    }

    function filterFind(string $field, string|array|null $inValue, ?string $operator = null, ?string $segundValueBetween = null): void
    {
        if (!empty($this->fields)) {
            $field = array_key_exists($field, $this->fields) ? $this->fields[$field] : $field;
        }
        
        if (!is_null($inValue)) {
            if ($operator === "BETWEEN") {
                $this->terms .= ' AND ' . $field . ' ' . $operator . ' :' . str_camel_case($field) . '1 AND :' . str_camel_case($field) . '2';
                $this->params .= '&' . str_camel_case($field) . "1={$inValue}&" . str_camel_case($field) . "2={$segundValueBetween}";
                return;
            }
            
            if ($operator === "NOT_LIKE" and $inValue <> "") {
                $this->terms .= " AND " . $field . " NOT LIKE '%{$inValue}%'";
                $this->params .= "";
                return;
            }

            if ($operator) {
                $this->terms .= ' AND ' . $field . ' ' . $operator . ' :' . str_camel_case($field);
                $this->params .= '&' . str_camel_case($field) . "={$inValue}";
                return;
            }
            
            if (is_string($inValue) and $inValue <> "") {
                $this->terms .= " AND " . $field . " LIKE '%{$inValue}%'";
                $this->params .= "";
                return;
            }

            if (is_array($inValue)) {
                $this->terms .= ' AND ' . $field . ' IN (:' . str_camel_case($field) . implode(',:' . str_camel_case($field), array_keys($inValue)) . ')';
                $this->params .= '&' . urldecode(http_build_query($inValue, str_camel_case($field)));
                return;
            }

        }
    }

    public function findByFilter(?array $dataArr = null): ?self
    {
        $data = (object) $dataArr;

        if (!isset($data->v)) {
            return null;
        }

        $this->filterFind("filters", $data->v);

        if(!empty($data->fieldsFilter)) {
            foreach ($data->fieldsFilter as $key => $value) {
                if (!empty($value)) {
                    $filterType = $data->filterType[$key] === "nao_contem" ? "NOT_LIKE" : null;
                    $this->filterFind($data->fieldsName[$key], $value, $filterType);
                }
            }
        }

        return $this;
    }

    function terms(): string
    {
        if (!$this->terms) {
            return "";
        }

        return substr($this->terms, 5, strlen($this->terms));
    }

    function params(): string
    {
        if (!$this->params) {
            return "";
        }
        
        return substr($this->params, 1, strlen($this->params));
    }

}
