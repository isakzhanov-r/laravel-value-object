<?php


namespace IsakzhanovR\ValueObject\Contracts;


interface Transferable
{
    /**
     * Get the instance as Data Transfer.
     *
     * @return array
     */
    public function toDTO();
}
