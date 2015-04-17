<?php

/**
 * Created by PhpStorm.
 * User: Allan McNaughton
 * Date: 4/16/2015
 * Time: 1:40 PM
 */
interface PostInterface
{
    public function save();

    public function delete();

    public function get($key);

    public function set($key, $value);
}