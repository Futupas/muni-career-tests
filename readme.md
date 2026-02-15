## SQL initialization

```sql
CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_type_id INT,
    test_slug VARCHAR(100),
    user_name VARCHAR(255),
    user_age INT,
    result_key VARCHAR(50),
    result_explanation TEXT,
    result_profession TEXT,
    ip_address VARCHAR(45),
    submission_time DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

