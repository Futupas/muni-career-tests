# Web tests

## Project Structure
* `index.php` – The landing page listing all available tests from `tests/tests.json`.
* `test.php` – The main test engine that parses the JSON, renders the UI via handler, and saves results to the DB.
* `admin.php` – Admin panel to view and sort stored test results.
* `db.php` – Database connection and environment configuration.
* `handlers/` – PHP scripts containing specific view and logic code for different test types.
* `tests/` – Directory containing the `.json` files for each individual test.

## Logic Overview
1. **Selection:** `index.php` reads the test list. `test.php` matches the URL slug to a JSON filename.
2. **Dynamic Rendering:** `test.php` includes a `view_{type}.php` handler to render the questions dynamically based on the JSON structure.
3. **Submission:** Upon POST, `test.php` validates the input and calls a `logic_{type}.php` handler to calculate the result based on the specific test's rules.
4. **Storage:** Results are encoded as JSON and saved into the `test_results` MySQL table.

## SQL Initialization

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

## Auto-Fill Script (for testing)

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
