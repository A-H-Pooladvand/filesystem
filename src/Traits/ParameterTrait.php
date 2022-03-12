<?php

namespace Ahp\Filesystem\Traits;

trait ParameterTrait
{
    /**
     * Parameters container.
     *
     * @var array
     */
    private array $params = [];

    /**
     * Get all params.
     *
     * @return array
     */
    protected function getParams(): array
    {
        $this->setBucket($this->bucket);

        return $this->params;
    }

    /**
     * Parameters setter.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    protected function setParam(string $key, mixed $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Sets the bucket name.
     *
     * @param  string  $bucket
     * @return void
     */
    protected function setBucket(string $bucket)
    {
        $this->setParam('Bucket', $bucket);
    }

    /**
     * Sets the max key param.
     *
     * @param  int  $value
     * @return void
     */
    protected function setMaxKeys(int $value)
    {
        $this->setParam('MaxKeys', $value);
    }

    /**
     * Sets prefix param.
     *
     * @param  mixed  $value
     * @return void
     */
    protected function setPrefix(mixed $value)
    {
        $this->setParam('Prefix', $value);
    }

    /**
     * Sets the token.
     *
     * @param  string|null  $value
     * @return void
     */
    protected function setToken(string|null $value)
    {
        if (is_null($value)) {
            return;
        }

        $this->setParam('ContinuationToken', $value);
    }

    /**
     * Sets the key param.
     *
     * @param  mixed  $value
     * @return void
     */
    protected function setKey(mixed $value)
    {
        $this->setParam('Key', $value);
    }

    /**
     * Sets ACL param.
     *
     * @param  string  $value
     * @return void
     */
    protected function setAcl(string $value)
    {
        $this->setParam('ACL', $value);
    }
}
