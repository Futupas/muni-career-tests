<div class="row g-3 text-center">
    <?php 
    $block = $testData['questions'][0]; // Get the first block
    foreach ($block['questions'] as $qIndex => $q): 
        $safeId = "shape_" . $qIndex;
    ?>
        <div class="col">
            <input type="radio" class="btn-check" 
                   name="q_<?php echo $block['id']; ?>" 
                   id="<?php echo $safeId; ?>" 
                   value="<?php echo htmlspecialchars($q['value']); ?>" required>
            <label class="card card-body option-label h-100 d-flex flex-column align-items-center justify-content-center" for="<?php echo $safeId; ?>">
                <div class="fs-1 mb-2"><?php echo $q['img']; ?></div>
                <small class="fw-bold"><?php echo htmlspecialchars($q['text']); ?></small>
            </label>
        </div>
    <?php endforeach; ?>
</div>
