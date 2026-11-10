<?php

declare(strict_types=1);

namespace Redmine\Api;

/**
 * Build the params of a `create()` or `update()` request.
 *
 * @internal
 */
trait RequestParamsTrait
{
    /**
     * Merges the defaults with the params and removes all empty values.
     * `Param::Clear` is the only way to send an empty value.
     *
     * @param array<mixed> $defaults
     * @param array<mixed> $params
     *
     * @throws \InvalidArgumentException if future mode is enabled and a value is `''`, `[]` or `false`
     *
     * @return array<mixed>
     */
    private function buildRequestParams(array $defaults, array $params): array
    {
        $requestParams = [];

        foreach (array_merge($defaults, $params) as $name => $value) {
            if ($value === Param::Clear) {
                $requestParams[$name] = '';

                continue;
            }

            if (is_array($value)) {
                $value = $this->replaceNestedParamClear($value);
            }

            if ($this->isFutureModeEnabled() && ('' === $value || [] === $value || false === $value)) {
                throw new \InvalidArgumentException(sprintf(
                    'The parameter "%s" must not be %s. Use `null` to skip the parameter, `Param::Clear` to clear the field%s.',
                    $name,
                    false === $value ? '`false`' : ('' === $value ? 'an empty string' : 'an empty array'),
                    false === $value ? ' or `0` to disable a boolean' : '',
                ));
            }

            if (
                false !== $value
                && null !== $value
                && '' !== $value
                && (!is_array($value) && !is_object($value) || !empty($value))
            ) {
                $requestParams[$name] = $value;
            }
        }

        return $requestParams;
    }

    /**
     * Replaces `Param::Clear` in nested values (e.g. `custom_fields`) with an empty string.
     * All other nested values are not changed.
     *
     * @param array<mixed> $values
     *
     * @return array<mixed>
     */
    private function replaceNestedParamClear(array $values): array
    {
        foreach ($values as $key => $value) {
            if ($value === Param::Clear) {
                $values[$key] = '';
            } elseif (is_array($value)) {
                $values[$key] = $this->replaceNestedParamClear($value);
            }
        }

        return $values;
    }
}
