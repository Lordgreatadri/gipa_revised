<?php

namespace App\Modules\Shared\Domain;

/**
 * Origin of an application operation (ADR-0009 "source").
 */
enum RequestSource: string
{
    case Ui = 'UI';
    case Api = 'API';
    case Cli = 'CLI';
    case Job = 'JOB';
}
