<?php

namespace EvLimma\ActionPack;

class DynamicColumns
{
    protected $columns;
    protected $fields;

    /**
     * // $columns = new DynamicColumns();
     * // $columns->createCol("filter2", "concat(EQUI_NOME, ' ', TIPO_NOME)");
     * // $columns->createColConcat("filter3", ["EQUI_NOME", "TIPO_NOME"]);
     */
    public function __construct()
    {
        $this->columns = null;
        $this->fields = [];
    }

    function createCol(string $field, string $value): void
    { 
        $this->fields[$field] = $value;

        if (is_null($value)) {
            $this->columns .= ", NULL as `{$field}`";
            return;
        }

        $this->columns .= ", {$value} as `{$field}`";
    }

    function createColConcat(string $field, ?array $array): void
    {
        if (!$array) {
            return;
        }

        $arrayFields = $this->fields;

        if ($arrayFields) {
            $array = array_map(function ($value) use ($arrayFields) {
                return array_key_exists($value, $arrayFields) ? $arrayFields[$value] : $value;
            }, $array);
        }

        $value = implode(", ' ', ", array_map(fn($item) => "IFNULL(CONVERT($item USING utf8) COLLATE UTF8_GENERAL_CI, '')", $array));
        $valConcat = "concat({$value})";
        $this->fields[$field] = $valConcat;
        $this->columns .= ", {$valConcat} as `{$field}`";
    }

    function columns(): string
    {
        return str_replace(array_fill(0, 10, "  "), " ", "*{$this->columns}");
    }

}
