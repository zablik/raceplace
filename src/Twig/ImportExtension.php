<?php

namespace App\Twig;

use App\Entity\Event;
use App\Service\Importer\EventDumper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ImportExtension extends AbstractExtension
{
    protected EventDumper $eventDumper;

    public function __construct(EventDumper $eventDumper)
    {
        $this->eventDumper = $eventDumper;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('event_config_exists', [$this, 'eventConfigExists']),
        ];
    }

    public function eventConfigExists(Event $event)
    {
        return $this->eventDumper->dumpExists($event);
    }
}
