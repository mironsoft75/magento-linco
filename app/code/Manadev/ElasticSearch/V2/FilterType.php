<?php

namespace Manadev\ElasticSearch\V2;

use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ElasticSearch\V2\Traits\Dependencies;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\SupportedFilter;

class FilterType implements SupportedFilter
{
    use Dependencies;

    public function supports(Filter $filter) {
        return true;
    }

    public function apply(Filter $filter) {
        throw new NotImplemented();
    }

    protected function applyTerms($field, $ids) {
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['terms' => [$field => $ids]],
                    ],
                ],
            ],
        ]);
    }

    protected function applyAndTerms($field, $ids) {
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'filter' => array_map(function($id) use ($field) {
                        return ['term' => [$field => $id]];
                    }, $ids),
                ],
            ],
        ]);
    }

    protected function applyInclusiveRange($field, $range) {
        $rangeJson = [];
        if ($range[0] !== '') {
            $rangeJson['gte'] = $range[0];
        }
        if ($range[1] !== '') {
            $rangeJson['lte'] = $range[1];
        }

        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['range' => [$field => $rangeJson]],
                    ],
                ],
            ],
        ]);
    }

    protected function applyMinMaxRange($minField, $maxField, $range) {
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['range' => [$maxField => [
                            'gte' => $range[0],
                        ]]],
                        ['range' => [$minField => [
                            'lte' => $range[1],
                        ]]],
                    ],
                ],
            ],
        ]);
    }

    protected function applyExclusiveRanges($field, $ranges) {
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            'query' => [
                'bool' => [
                    'filter' => [
                        ['bool' => [
                            'should' => array_map(function($range) use ($field){
                                $conditions = [];

                                if ($range[0] !== '') {
                                    $conditions['gte'] = $range[0];
                                }
                                if ($range[1] !== '') {
                                    $conditions['lt'] = $range[1];
                                }

                                return ['range' => [$field => $conditions]];
                            }, $ranges),
                        ]],
                    ],
                ],
            ],
        ]);
    }
}
