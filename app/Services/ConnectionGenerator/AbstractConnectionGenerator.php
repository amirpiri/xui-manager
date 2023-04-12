<?php

namespace App\Services\ConnectionGenerator;

abstract class AbstractConnectionGenerator
{
    abstract function generate(): string;
}
