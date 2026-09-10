<?php

return [
    'chunk_size' => 500,
    'chunk_overlap' => 50,
    'top_k' => 3,
    'similarity_threshold' => (float) env('RAG_SIMILARITY_THRESHOLD', 0.65),
    'history_turns' => 3,
    'http_timeout' => 25,
    'retry_delay_ms' => 1000,
];
