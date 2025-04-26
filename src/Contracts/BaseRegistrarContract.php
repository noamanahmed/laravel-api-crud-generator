<?php

namespace NoamanAhmed\Contracts;

/**
 * Interface BaseRegistrar
 *
 * Defines the a registrar for routing
 */
interface BaseRegistrarContract
{
    public function getResourceDefaults(): array;
}
