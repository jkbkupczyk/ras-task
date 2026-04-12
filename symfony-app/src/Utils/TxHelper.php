<?php

namespace App\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Throwable;

final class TxHelper
{
    private function __construct()
    {
        // noop
    }

    /**
     * @throws Exception|Throwable
     */
    public static function runWithinTx(Connection $connection, callable $closure): mixed
    {
        $connection->beginTransaction();
        try {
            $result = $closure($connection);
            $connection->commit();
            return $result;
        } catch (Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
