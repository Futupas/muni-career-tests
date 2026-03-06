<div class="row g-3">
    <?php foreach ($testData['questions'] as $q): ?>
        <div class="col-12 border-bottom pb-2">
            <div class="mb-2">
                <strong><?php echo $q['id']; ?>.</strong> <?php echo htmlspecialchars($q['text']); ?>
            </div>
            
            <?php if (isset($q['image'])): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($q['image']); ?>" class="img-fluid border rounded" alt="Питання <?php echo $q['id']; ?>" style="max-height: 200px;">
                </div>
            <?php endif; ?>

            <input type="text" class="form-control form-control-sm w-50" name="q_<?php echo $q['id']; ?>" placeholder="Ваша відповідь..." required>
        </div>
    <?php endforeach; ?>
</div>
