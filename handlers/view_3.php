<?php foreach ($testData['questions'] as $q): ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3">Пара №<?php echo $q['id']; ?></h6>
        <div class="row g-3">
            <div class="col-md-6">
                <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="q_<?php echo $q['id']; ?>_a" value="a" required>
                <label class="card card-body option-label h-100 d-flex align-items-center" for="q_<?php echo $q['id']; ?>_a">
                    <?php echo htmlspecialchars($q['a']['text']); ?>
                </label>
            </div>
            <div class="col-md-6">
                <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="q_<?php echo $q['id']; ?>_b" value="b" required>
                <label class="card card-body option-label h-100 d-flex align-items-center" for="q_<?php echo $q['id']; ?>_b">
                    <?php echo htmlspecialchars($q['b']['text']); ?>
                </label>
            </div>
        </div>
    </div>
<?php endforeach; ?>
