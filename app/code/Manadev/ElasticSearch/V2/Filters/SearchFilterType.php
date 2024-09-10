<?php

namespace Manadev\ElasticSearch\V2\Filters;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Filters\SearchFilter;

class SearchFilterType extends FilterType
{
    /**
     * @param Filter|SearchFilter $filter
     */
    public function apply(Filter $filter) {
        $fields = $this->getSearchRequestConfig()->get('quick_search_container/queries/search/match');

        $count = 0;
        $value = preg_replace('#^"(.*)"$#m', '$1', $filter->getText(), -1, $count);

        $should = [];
        foreach ($fields as $field) {
            $name = $field['field'] != '*'
                ? $this->getElasticFieldMapper()->getFieldName($field['field'],
                    ['type' => FieldMapperInterface::TYPE_QUERY])
                : '_search';

            if (mb_strpos($name, 'position_') === 0) {
                // full text search doesn't make sense on a position field
                // and it fails if tried
                continue;
            }

            $condition = ($count) ? 'match_phrase' : 'match';

            $should[] = [
                $condition => [
                    $name => [
                        'query' => $value,
                        //'fuzziness' => 1,
                        'boost' => ($field['boost'] ?? 1) + 1,
                    ],
                ],
            ];

            if ($this->getElasticConfiguration()->searchSkuAndNameByPrefix()) {
                if ($condition == 'match' && $name === 'sku') {
                    $should[] = [
                        'match_phrase_prefix' => [
                            $name => [
                                'query' => $value,
                                'boost' => 2,
                                'analyzer' => 'sku_prefix_search',
                            ],
                        ],
                    ];
                }
                elseif ($condition == 'match' && $name === 'name') {
                    $should[] = [
                        'match_phrase_prefix' => [
                            $name => [
                                'query' => $value,
                                'boost' => 2,
                                'analyzer' => 'prefix_search',
                            ],
                        ],
                    ];
                }
            }
        }

        $this->getQuery()->body['query']['bool']['filter'][1]['terms']['visibility'][0] = '3';
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'should' => $should,
                    'minimum_should_match' => 1,
                ],
            ],
        ]);
    }
}
