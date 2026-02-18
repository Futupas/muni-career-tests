<?php foreach ($testData['questions'] as $blockIndex => $block): ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($block['name']); ?></h6>
        <div class="row g-3">
            <?php foreach ($block['questions'] as $qIndex => $q): ?>
                <div class="col-md-6">
                    <?php 
                        // Generate a safe numeric ID to avoid Cyrillic issues in HTML 'id' attribute
                        $safeId = "b" . $block['id'] . "q" . $qIndex; 
                    ?>
                    <input type="radio" class="btn-check" 
                           name="q_<?php echo $block['id']; ?>" 
                           id="<?php echo $safeId; ?>" 
                           value="<?php echo htmlspecialchars($q['value']); ?>" required>
                    <label class="card card-body option-label h-100 d-flex align-items-center" for="<?php echo $safeId; ?>">
                        <?php echo htmlspecialchars($q['text']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
