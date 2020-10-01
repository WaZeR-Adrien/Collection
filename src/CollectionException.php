<?php
namespace AdrienM\Collection;

class CollectionException extends \Exception
{
    const KEY_ALREADY_ADDED = 100;
    const KEY_INVALID = 101;
    const METHOD_DOES_NOT_EXIST = 102;
}
