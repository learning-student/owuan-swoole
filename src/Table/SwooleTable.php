<?php

namespace SwooleTW\Http\Table;

use Swoole\Table;

class SwooleTable
{

    /**
     * @var  array
     */
    protected $tables = [];

    /**
     * SwooleTable constructor.
     * @param array $tables
     */
    public function __construct(array $tables = [])
    {
        $this->registerTables($tables);
    }

    /**
     * Register user-defined swoole tables.
     * @param array $tables
     */
    protected function registerTables(array $tables)
    {

        foreach ($tables as $key => $value) {
            $table = new Table($value['size']);
            $columns = $value['columns'] ?? [];
            foreach ($columns as $column) {
                if (isset($column['size'])) {
                    $table->column($column['name'], $column['type'], $column['size']);
                } else {
                    $table->column($column['name'], $column['type']);
                }
            }
            $table->create();

            $this->add($key, $table);
        }
    }


    /**
     * Add a swoole table to existing tables.
     *
     * @param string $name
     * @param \Swoole\Table $table
     *
     * @return \SwooleTW\Http\Table\SwooleTable
     */
    public function add(string $name, Table $table)
    {
        $this->tables[$name] = $table;

        return $this;
    }

    /**
     * Get a swoole table by its name from existing tables.
     *
     * @param string $name
     *
     * @return \Swoole\Table $table
     */
    public function get(string $name)
    {
        return $this->tables[$name] ?? null;
    }

    /**
     * Get all existing swoole tables.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->tables;
    }

    /**
     * Dynamically access table.
     *
     * @param string $key
     *
     * @return table
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param array $tables
     * @return SwooleTable
     */
    public function setTables(array $tables): SwooleTable
    {
        $this->tables = $tables;
        return $this;
    }

}
