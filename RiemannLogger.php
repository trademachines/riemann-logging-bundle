<?php

namespace Trademachines\Bundle\RiemannLoggingBundle;

use Trademachines\Riemann\Client as RiemannClient;

class RiemannLogger
{
    /** @var RiemannClient */
    protected $riemannClient;

    /** @var string */
    protected $name;

    /**
     * RiemannLogger constructor.
     *
     * @param RiemannClient $riemannClient
     * @param string|null   $name
     */
    public function __construct(RiemannClient $riemannClient, $name = null)
    {
        $this->riemannClient = $riemannClient;
        $this->name          = $name;
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

        if (!isset($data['ttl'])) {
            $data['ttl'] = 1;
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

        return $attributes;
    }

    private function getService(array $data)
    {
        if (!$this->name) {
            return null;
        }

        $service = $this->name;
        if (isset($data['service'])) {
            $service = $service . '.' . $data['service'];
        }

        return $service;
    }
}
