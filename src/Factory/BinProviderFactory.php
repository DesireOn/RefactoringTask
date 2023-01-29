<?php

namespace App\Factory;

use App\External\Binlist;
use App\External\BinProviderInterface;
use Exception;

class BinProviderFactory
{
    /**
     * @param string $type
     * @param $param
     * @return BinProviderInterface
     * @throws Exception
     */
    public static function createBinProvider(string $type, $param): BinProviderInterface
    {
        switch ($type) {
            case BinProviderInterface::BINLIST:
                return new Binlist($param);
            default:
                throw new Exception('Invalid bin provider type');
        }
    }
}