<?php foreach ($testData['questions'] as $q): ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3">Пара №<?php echo $q['id']; ?></h6>
        <div class="row g-3">
            <?php foreach ($q['options'] as $index => $option): ?>
                <div class="col-md-6">
                    <?php $safeId = "q" . $q['id'] . "_" . $index; ?>
                    <input type="radio" class="btn-check" 
                           name="q_<?php echo $q['id']; ?>" 
                           id="<?php echo $safeId; ?>" 
                           value="<?php echo htmlspecialchars($option['text']); ?>" required />
                    <label class="card card-body option-label h-100 d-flex align-items-center" for="<?php echo $safeId; ?>">
                        <?php echo htmlspecialchars($option['text']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
