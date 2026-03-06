<div class="row g-4">
    <?php foreach ($testData['questions'] as $block): ?>
        <div class="col-12">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header fw-bold bg-light">
                    <?php echo htmlspecialchars($block['name']); ?>
                </div>
                <div class="card-body">
                    <?php foreach ($block['options'] as $option): ?>
                        <div class="form-check mb-3">
                            <?php 
                                // id for the input
                                $inputId = "q_" . $block['id'] . "_" . $option['value'];
                            ?>
                            <input class="form-check-input" type="radio" 
                                   name="q_<?php echo $block['id']; ?>" 
                                   id="<?php echo $inputId; ?>" 
                                   value="<?php echo $option['value']; ?>" required>
                            <label class="form-check-label option-label w-100 p-2" for="<?php echo $inputId; ?>">
                                <?php echo htmlspecialchars($option['text']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
