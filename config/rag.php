<?php

return [
    'chunk_size' => 500,
    'chunk_overlap' => 50,
    'top_k' => 3,
    'similarity_threshold' => (float) env('RAG_SIMILARITY_THRESHOLD', 0.65),
    'history_turns' => 3,
    'http_timeout' => 25,
    'retry_delay_ms' => 1000,
    'request_budget_seconds' => (int) env('RAG_REQUEST_BUDGET_SECONDS', 55),
    'daily_user_limit' => (int) env('RAG_DAILY_USER_LIMIT', 100),
    'daily_global_limit' => (int) env('RAG_DAILY_GLOBAL_LIMIT', 2000),
    'index_budget_seconds' => 270,
    'max_chunks_per_module' => (int) env('RAG_MAX_CHUNKS_PER_MODULE', 200),
];
