<?php

namespace Ahp\Filesystem\Traits;

trait ParameterTrait
{
    private array $params = [];

    protected function getParams(): array
    {
        $this->setBucket($this->bucket);

        return $this->params;
    }

    protected function setParam(string $key, mixed $value)
    {
        $this->params[$key] = $value;
    }

    protected function setBucket(string $bucket)
    {
        $this->setParam('Bucket', $bucket);
    }

    protected function setMaxKeys(int $value)
    {
        $this->setParam('MaxKeys', $value);
    }

    protected function setPrefix(mixed $value)
    {
        $this->setParam('Prefix', $value);
    }

    protected function setToken(string|null $value)
    {
        if (is_null($value)) {
            return;
        }

        $this->setParam('ContinuationToken', $value);
    }

    protected function setKey(mixed $value)
    {
        $this->setParam('Key', $value);
    }

    protected function setAcl(string $value)
    {
        $this->setParam('ACL', $value);
    }
}
