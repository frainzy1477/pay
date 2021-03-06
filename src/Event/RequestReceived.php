<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class RequestReceived extends Event
{
    /**
     * @var array|\Psr\Http\Message\ServerRequestInterface|null
     */
    public $contents;

    /**
     * @var array|null
     */
    public $params;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|\Psr\Http\Message\ServerRequestInterface|null $contents
     */
    public function __construct($contents, ?array $params, ?Rocket $rocket)
    {
        $this->contents = $contents;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
