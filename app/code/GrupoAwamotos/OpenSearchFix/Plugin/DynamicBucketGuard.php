<?php
namespace GrupoAwamotos\OpenSearchFix\Plugin;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class DynamicBucketGuard
{
    /**
     * Ensure hits array exists before delegating to the core builder so undefined indexes do not break layered navigation.
     */
    public function aroundBuild(
        Dynamic $subject,
        callable $proceed,
        BucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        if (!isset($queryResult['hits']) || !is_array($queryResult['hits'])) {
            $queryResult['hits'] = ['hits' => []];
        } elseif (!isset($queryResult['hits']['hits']) || !is_array($queryResult['hits']['hits'])) {
            $queryResult['hits']['hits'] = [];
        }

        return $proceed($bucket, $dimensions, $queryResult, $dataProvider);
    }
}
