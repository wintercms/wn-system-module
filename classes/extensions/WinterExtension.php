<?php

namespace System\Classes\Extensions;

interface WinterExtension
{
    public function extensionInstall(): static;
    public function extensionUninstall(): static;
    public function extensionEnable(): static;
    public function extensionDisable(): static;
    public function extensionRollback(): static;
    public function extensionRefresh(): static;

    public function extensionUpdate(): static;


//    public function freeze(): WinterExtension;
//    public function unfreeze(): WinterExtension;
}
