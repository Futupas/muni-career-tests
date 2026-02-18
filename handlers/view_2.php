<?php foreach ($testData['questions'] as $block): ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($block['name']); ?></h6>
        <div class="row g-3">
            <?php foreach ($block['questions'] as $q): ?>
                <div class="col-md-6">
                    <input type="radio" class="btn-check" name="q_<?php echo $block['id']; ?>" id="o_<?php echo $block['id'].$q['value']; ?>" value="<?php echo $q['value']; ?>" required>
                    <label class="card card-body option-label" for="o_<?php echo $block['id'].$q['value']; ?>"><?php echo htmlspecialchars($q['text']); ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
