<?php

namespace SW\Notebook;

interface Mutator
{
    /**
     * Encode the value to be stored in database.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function encode($value);

    /**
     * Decode the value from the database.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function decode($value);
}
