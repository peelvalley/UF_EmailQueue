<?php

namespace UserFrosting\Sprinkle\EmailQueue\Database\Concerns;

trait HandlesJsonVirtualColumns
{
    protected function jsonValueExpression(string $column, string $path, bool $unquote = true): string
    {
        $column = trim($column, '`');
        $path = $this->normaliseJsonPath($path);

        if ($this->isMariaDb()) {
            $expression = sprintf('JSON_EXTRACT(`%s`, %s)', $column, $path);

            if ($unquote) {
                $expression = sprintf('JSON_UNQUOTE(%s)', $expression);
            }

            return $expression;
        }

        $operator = $unquote ? '->>' : '->';

        return sprintf('`%s` %s %s', $column, $operator, $path);
    }

    protected function normaliseJsonPath(string $path): string
    {
        $path = trim($path, "'\"");

        if ($path === '') {
            $path = '$';
        } elseif ($path[0] !== '$') {
            $path = '$.' . ltrim($path, '.');
        }

        $path = str_replace("'", "\\'", $path);

        return sprintf("'%s'", $path);
    }

    protected function isMariaDb(): bool
    {
        $connection = $this->schema->getConnection();
        $pdo = $connection->getPdo();
        $version = (string) $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

        if ($version === '') {
            $result = $connection->selectOne('select version() as version');
            if (is_array($result)) {
                $version = $result['version'] ?? '';
            } elseif (is_object($result) && isset($result->version)) {
                $version = $result->version;
            }
        }

        return stripos($version, 'mariadb') !== false;
    }
}
