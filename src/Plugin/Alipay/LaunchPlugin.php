<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class LaunchPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        if (should_do_http_request($rocket)) {
            $rocket->setDestination($this->getMethodResponse($rocket));
        }

        Logger::info('[alipay][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getMethodResponse(Rocket $rocket): Collection
    {
        $response = Collection::wrap(
            $rocket->getDestination()->get($this->getResponseKey($rocket))
        );

        $this->verifySign($rocket);

        if (10000 != $response->get('code', 10000)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_CODE, 'Invalid response code', $response->all());
        }

        return $response;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function verifySign(Rocket $rocket): void
    {
        $response = $rocket->getDestination()->get($this->getResponseKey($rocket));
        $sign = $rocket->getDestination()->get('sign', '');

        if ('' === $sign || is_null($response)) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', $response);
        }

        if (!verify_alipay_response($rocket->getParams(), json_encode($response, JSON_UNESCAPED_UNICODE), base64_decode($sign))) {
            throw new InvalidResponseException(InvalidResponseException::INVALID_RESPONSE_SIGN, '', $response);
        }
    }

    protected function getResponseKey(Rocket $rocket): string
    {
        $method = $rocket->getPayload()->get('method');

        return str_replace('.', '_', $method).'_response';
    }
}
