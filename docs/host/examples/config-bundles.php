<?php

declare(strict_types=1);

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],

    /*
     * Exchanging bundle registration.
     *
     * Use this when the component is installed as a package or when the host app
     * can autoload App\Exchanging from the Exchanging repository.
     */
    App\Exchanging\ExchangingBundle::class => ['all' => true],
];
