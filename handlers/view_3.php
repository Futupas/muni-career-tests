<?php foreach ($testData['questions'] as $q): ?>
    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
        <span><?php echo htmlspecialchars($q['text']); ?></span>
        <div class="btn-group">
            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="y<?php echo $q['id']; ?>" value="yes" required>
            <label class="btn btn-outline-success btn-sm" for="y<?php echo $q['id']; ?>">Так</label>
            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="n<?php echo $q['id']; ?>" value="no" required>
            <label class="btn btn-outline-danger btn-sm" for="n<?php echo $q['id']; ?>">Ні</label>
        </div>
    </div>
<?php endforeach; ?>
