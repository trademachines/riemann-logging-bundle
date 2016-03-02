<?php

namespace Trademachines\Bundle\RiemannLoggingBundle;

use Trademachines\Riemann\Client as RiemannClient;

class RiemannLogger
{
    /** @var RiemannClient */
    protected $riemannClient;

    /** @var string */
    protected $name;

    /** @var string */
    protected $identAttribute;

    /**
     * RiemannLogger constructor.
     *
     * @param RiemannClient $riemannClient
     * @param string|null   $name
     */
    public function __construct(RiemannClient $riemannClient, $name = null, $identAttribute = null)
    {
        $this->riemannClient  = $riemannClient;
        $this->name           = $name;
        $this->identAttribute = $identAttribute;
    }

    /**
     * @param array $data
     * @param array $attributes
     */
    public function log(array $data, array $attributes = [])
    {
        $eventData = $this->getRiemannEventData($data, $attributes);
        $this->riemannClient->sendEvent($eventData);
    }

    protected function getRiemannEventData(array $data, array $attributes)
    {
        $data               = $this->enrichData($data);
        $data['attributes'] = $this->asRiemannAttributes($attributes);

        return $data;
    }

    protected function enrichData(array $data)
    {
        if (!isset($data['host'])) {
            $data['host'] = gethostname();
        }

        if ($service = $this->getService($data)) {
            $data['service'] = $service;
        }

        return $data;
    }

    protected function asRiemannAttributes(array $data)
    {
        $attributes = [];
        foreach ($data as $key => $value) {
            $attributes[] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        if ($this->name && $this->identAttribute) {
            $attributes[] = [
                'key'   => $this->identAttribute,
                'value' => $this->name,
            ];
        }

        return $attributes;
    }

    private function getService(array $data)
    {
        if (!$this->name || isset($data['service'])) {
            return null;
        }

        return $this->name;
    }
}
