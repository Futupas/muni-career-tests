<div class="row">
    <div class="col-12">
        <p class="alert alert-info">Відповідайте щиро. Оберіть один варіант для кожного твердження.</p>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Твердження</th>
                        <th class="text-center">Вірно</th>
                        <th class="text-center">Невірно</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testData['questions'] as $q): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars($q['text']); ?></td>
                            <td class="text-center">
                                <input class="form-check-input" type="radio" 
                                       name="q_<?php echo $q['id']; ?>" 
                                       id="q_<?php echo $q['id']; ?>_yes" 
                                       value="1" required>
                            </td>
                            <td class="text-center">
                                <input class="form-check-input" type="radio" 
                                       name="q_<?php echo $q['id']; ?>" 
                                       id="q_<?php echo $q['id']; ?>_no" 
                                       value="0" required>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
