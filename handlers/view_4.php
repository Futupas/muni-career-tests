<div class="row g-3 text-center">
    <?php foreach ($testData['questions'][0]['questions'] as $q): ?>
        <div class="col">
            <input type="radio" class="btn-check" name="q_1" id="i<?php echo $q['value']; ?>" value="<?php echo $q['value']; ?>" required>
            <label class="card card-body option-label h-100" for="i<?php echo $q['value']; ?>">
                <div class="display-4 mb-2"><?php echo $q['img']; ?></div>
                <small><?php echo htmlspecialchars($q['text']); ?></small>
            </label>
        </div>
    <?php endforeach; ?>
</div>
