<?php

namespace EvLimma\ActionPack;

use EvLimma\ActionPack\OrderColItem;
use EvLimma\ActionPack\Message;
use EvLimma\ActionPack\DynamicColumns;

trait ActionPack
{
    protected $message;
    protected array $addfields;

    public function message(): ?Message
    {
        return $this->message;
    }

    public function addfields(): ?object
    {
        $filter = new DynamicFilter();

        foreach ($this->addfields ?? [] as $key => $value) {
            $filter->createCol($key, $value);
        }

        $filter->createColConcat("filters", (new OrderColItem())->listFields(extractRight(__CLASS__, "\\", 2)));

        return $filter;
    }

    public function listEntity(): ?array
    {
        $filter = $this->addfields();
        $find = parent::find($filter->terms(), $filter->params(), $filter->columns());

        return $find->fetch(true);
    }

    public function findByPag(?array $dataArr = null, ?int $itensPorPag = ITEMS_PER_PAGE, int $itemInicio = 1, ?array $filterExtra = null): ?object
    {
        $filter = $this->addfields();
        $filter->findByFilter($dataArr);

        foreach ($filterExtra ?? [] as $key => $value) {
            $filter->filterFind($key, $value, "=");
        }

        $find = parent::find($filter->terms(), $filter->params(), $filter->columns());

        $findCount = $find->count();
        $findFetch = $find->limit($itensPorPag)->offset($itemInicio - 1)->order($this->orderDefault)->fetch(true);

        return (object) ["findCount" => $findCount, "findFetch" => $findFetch];
    }

    public function findByActive(): ?array
    {
        $filter = $this->addfields();
        $filter->filterFind("status", 1, "=");
        $find = parent::find($filter->terms(), $filter->params(), $filter->columns());
        return $find->fetch(true);
    }

    public function findByKey(int $id): ?self
    {
        $filter = $this->addfields();
        $filter->filterFind($this->primary, $id, "=");
        return parent::find($filter->terms(), $filter->params(), $filter->columns())->fetch();
    }

    public function findByFields(?array $filter): ?array
    {
        $filter = $this->addfields();

        foreach ($filter ?? [] as $key => $value) {
            $filter->filterFind($key, $value, "=");
        }

        return parent::find($filter->terms(), $filter->params(), $filter->columns())->fetch(true);
    }
}
