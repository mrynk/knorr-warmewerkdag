<?php

return [
    'alphabet' => 'ACDEFGHJKLMNPQRTUVWXYZ234679',
    'seed' => 785,
    'min_length' => 8,

    'batches' => [
        [
            'name' => 'initial',
            'amount' => 50000,
        ],
        [
            'name' => 'extra',
            'amount' => 100000,
        ]
    ]
];