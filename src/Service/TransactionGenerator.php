<?php

namespace App\Service;

use App\Exception\TransactionGeneratorException;
use App\Model\Transaction;

class TransactionGenerator
{
    /**
     * @param string $json
     * @return Transaction
     * @throws TransactionGeneratorException
     */
    public function generate(string $json): Transaction
    {
        $array = json_decode($json, true);
        if (is_null($array)) {
            throw new TransactionGeneratorException('Json could not be parsed successfully.');
        }

        if (isset($array['bin']) && $array['bin'] !== '') {
            $bin = (string)$array['bin'];
        } else {
            throw new TransactionGeneratorException('BIN could not be generated.');
        }
        $transaction = new Transaction($bin);

        if (isset($array['amount'])) {
            $transaction->setAmount((float)$array['amount']);
        }
        if (isset($array['currency'])) {
            $transaction->setCurrency((string)$array['currency']);
        }

        return $transaction;
    }
}