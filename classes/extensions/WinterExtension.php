<?php

namespace System\Classes\Extensions;

interface WinterExtension
{
    public function getPath(): string;

    public function getVersion(): string;

    public function getIdentifier(): string;
}
