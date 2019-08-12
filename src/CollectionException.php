<?php
namespace AdrienM\Collection;

class CollectionException extends \Exception
{
    const KEY_ALREADY_USE = 500;
    const KEY_INVALID = 501;
    const METHOD_DOES_NOT_EXIST = 502;
}
