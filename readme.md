## SQL initialization

```sql
DROP TABLE IF EXISTS test_results;

CREATE TABLE test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_slug VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    user_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    user_age INT,
    result_json TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, 
    ip_address VARCHAR(45),
    submission_time DATETIME DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

