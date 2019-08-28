<?php

namespace VientoDigital\LaravelExpoPushNotifications;

interface ExpoPushNotifiable
{
    public function tokensForExpo(): array;
}
