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

## Check everything

```js
(function() {
    // Fill text and number inputs
    document.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
        input.value = (input.name === 'age') ? '27' : 'Test';
    });
    
    // Fill radio buttons (select the first one for each group)
    const radios = document.querySelectorAll('input[type="radio"]');
    const grouped = {};
    radios.forEach(r => {
        if (!grouped[r.name]) {
            grouped[r.name] = r;
        }
    });
    Object.values(grouped).forEach(r => r.checked = true);
    
    // Fill checkboxes (if any)
    document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = true);
})();
```

