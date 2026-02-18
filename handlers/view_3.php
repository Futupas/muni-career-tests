<?php foreach ($testData['questions'] as $q): ?>
    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
        <span class="me-3"><?php echo htmlspecialchars($q['text']); ?></span>
        <div class="btn-group">
            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="y_<?php echo $q['id']; ?>" value="yes" required>
            <label class="btn btn-outline-success btn-sm px-3" for="y_<?php echo $q['id']; ?>">Так</label>
            
            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="n_<?php echo $q['id']; ?>" value="no" required>
            <label class="btn btn-outline-danger btn-sm px-3" for="n_<?php echo $q['id']; ?>">Ні</label>
        </div>
    </div>
<?php endforeach; ?>
