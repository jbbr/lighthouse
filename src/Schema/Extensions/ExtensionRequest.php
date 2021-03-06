<?php

namespace Nuwave\Lighthouse\Schema\Extensions;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ExtensionRequest
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Nuwave\Lighthouse\Support\Contracts\GraphQLContext
     */
    protected $context;

    /**
     * @var bool
     */
    protected $batched;

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context
     * @param  bool  $batched
     * @return void
     */
    public function __construct(Request $request, GraphQLContext $context, $batched = false)
    {
        $this->request = $request;
        $this->context = $context;
        $this->batched = $batched;
    }

    /**
     * Get request instance.
     *
     * @return \Illuminate\Http\Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Get request context.
     *
     * @return \Nuwave\Lighthouse\Support\Contracts\GraphQLContext
     */
    public function context(): GraphQLContext
    {
        return $this->context;
    }

    /**
     * Get GraphQL query string.
     *
     * @param  int|null  $index
     * @return string
     */
    public function queryString(?int $index = null): string
    {
        return $index === null
            ? $this->request->input('query', '')
            : Arr::get($this->request, "{$index}.query");
    }

    /**
     * Get request variables.
     *
     * @param  int|null  $index
     * @return array|null
     */
    public function variables(?int $index = null)
    {
        $variables = $index === null
            ? $this->request->input('variables')
            : Arr::get($this->request, "{$index}.variables");

        return is_string($variables) ? json_decode($variables, true) : $variables;
    }

    /**
     * Check if request is batched.
     *
     * @return bool
     */
    public function isBatchedRequest(): bool
    {
        return $this->batched;
    }
}
