# Web Tests Engine

## Project Structure
* `index.php` – Landing page listing available tests from `tests/tests.json`.
* `test.php` – Test execution engine. Loads JSON, includes the appropriate `view_{type}.php`, and triggers `logic_{type}.php` upon submission.
* `admin.php` – Secure panel (HTTP Auth) to view, sort, copy, download, and delete test results.
* `db.php` – Database connection and environment loader.
* `handlers/` – PHP scripts for rendering UI (`view_`) and processing logic (`logic_`) per test type.
* `tests/` – Directory containing test configurations in JSON format (e.g., `2.4.json`).

## Environment Configuration (`.env`)
The system uses the following environment variables:
* `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` – Database credentials.
* `ADMIN_PASS` – Password for `admin.php` and `delete.php` (Basic Auth).
* `QUESTION_TRUNCATE_LEN` – Integer; defines the maximum character length for truncation of question text in stored results.

## Logic Overview
1. **Selection:** `index.php` renders the test list. `test.php` uses the URL slug to locate the corresponding JSON file.
2. **Rendering:** `test.php` dynamically includes a `view_{type}.php` file based on the `type` field in the JSON.
3. **Processing:** On POST, `test.php` validates the input and invokes `logic_{type}.php`. This handler calculates scores, maps user answers to readable text, truncates data based on `QUESTION_TRUNCATE_LEN`, and prepares the `packedResult` array.
4. **Storage:** The result is saved into the `test_results` MySQL table as a complete JSON object.

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
Paste this into the browser console to automatically fill all fields and submit the test:

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
        if (!grouped[r.name]) grouped[r.name] = r;
    });
    Object.values(grouped).forEach(r => r.checked = true);
    
    // Fill checkboxes (if any)
    document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = true);

    // Auto-submit the form
    const form = document.querySelector('form');
    if (form) form.submit();
})();
```

## Credits

Made by Futupas with assistance of Google Gemini
