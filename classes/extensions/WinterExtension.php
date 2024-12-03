<?php

namespace System\Classes\Extensions;

interface WinterExtension
{
    public function install(): static;
    public function uninstall(): static;
    public function enable(): static;
    public function disable(): static;
    public function rollback(): static;
    public function refresh(): static;
    public function update(): static;

//    public function freeze(): WinterExtension;
//    public function unfreeze(): WinterExtension;
}
