<?php

namespace brown\command;
use Composer\Script\Event;
class MyVendor
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postPackageInstall(Event $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}