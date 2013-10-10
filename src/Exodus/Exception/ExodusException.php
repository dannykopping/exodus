<?php
namespace Exodus\Exception;

use Exception;

class ExodusException extends Exception
{
    const NO_SOURCE = 'No source server defined';
    const NO_DESTINATION = 'No destination server defined';
    const NO_CONNECTION = 'No connection could be established to [%s]';
}