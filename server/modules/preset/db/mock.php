<?php

class Mock implements dbConnect
{

    /**
     * Mock constructor.
     *
     */
    public function __construct()
    {
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