web: /bin/bash /app/start.sh
worker: php artisan queue:work rag --queue=rag --sleep=3 --tries=3 --timeout=300 --max-time=3600
