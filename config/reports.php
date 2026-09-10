<?php

return [
    // Key by account email. Missing administrative data is shown as "-".
    'teacher_ids' => [],
    'student_classes' => [],
    'max_export_rows' => (int) env('REPORT_MAX_EXPORT_ROWS', 20000),
];
