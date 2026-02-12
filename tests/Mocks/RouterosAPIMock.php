<?php

namespace Tests\Mocks;

class RouterosAPIMock
{
    private $connected = false;
    private $data = [];

    public function __construct(array $config = [])
    {
        // Mock constructor
    }

    public function connect(): bool
    {
        $this->connected = true;
        return true;
    }

    public function disconnect(): void
    {
        $this->connected = false;
    }

    public function getMktRows(string $menu, array $filter = []): array
    {
        if (!$this->connected) {
            return [];
        }

        if (!isset($this->data[$menu])) {
            return [];
        }

        return array_filter($this->data[$menu], function ($row) use ($filter) {
            foreach ($filter as $key => $value) {
                if (!isset($row[$key]) || $row[$key] !== $value) {
                    return false;
                }
            }
            return true;
        });
    }

    public function editMktRow(string $menu, array $row, array $changes): bool
    {
        if (!$this->connected) {
            return false;
        }

        if (!isset($this->data[$menu])) {
            return false;
        }

        foreach ($this->data[$menu] as $key => $rowData) {
            if ($rowData['.id'] === $row['.id']) {
                $this->data[$menu][$key] = array_merge($rowData, $changes);
                return true;
            }
        }

        return false;
    }

    public function addMktRows(string $menu, array $rows): bool
    {
        if (!$this->connected) {
            return false;
        }

        if (!isset($this->data[$menu])) {
            $this->data[$menu] = [];
        }

        foreach ($rows as $row) {
            $row['.id'] = '*' . (count($this->data[$menu]) + 1);
            $this->data[$menu][] = $row;
        }

        return true;
    }

    public function removeMktRows(string $menu, array $rows): bool
    {
        if (!$this->connected) {
            return false;
        }

        if (!isset($this->data[$menu])) {
            return false;
        }
        
        $idsToRemove = array_map(function($row) {
            return $row['.id'];
        }, $rows);

        $this->data[$menu] = array_filter($this->data[$menu], function ($row) use ($idsToRemove) {
            return !in_array($row['.id'], $idsToRemove);
        });

        return true;
    }
}
