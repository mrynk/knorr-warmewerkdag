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
            'name' => 'batch2',
            'amount' => 25000,
        ],
        [
            'name' => 'testbatch',
            'amount' => env('TEST_BATCH_AMOUNT', 0),
        ],
    ],
];
