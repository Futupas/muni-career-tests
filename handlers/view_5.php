<?php foreach ($testData['questions'] as $q): ?>
    <div class="mb-3">
        <label class="form-label fw-bold"><?php echo htmlspecialchars($q['text']); ?></label>
        <textarea class="form-control" name="q_<?php echo $q['id']; ?>" rows="3" required></textarea>
    </div>
<?php endforeach; ?>
