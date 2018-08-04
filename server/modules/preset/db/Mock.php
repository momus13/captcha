<?php

class Mock implements dbConnect
{
    /**
     * @var array
     */
    private $config = [];



    /**
     * Mock constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function connect()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        return true;
    }

    /**
     * @param $data
     *
     * @param int $count
     * @param array $keys
     * @return mixed
     */
    public function execute($data, $count = 0, $keys = [])
    {
        return true;
    }
}