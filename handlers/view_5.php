<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 75%;">Твердження</th>
                        <th class="text-center" style="width: 10%;">Так</th>
                        <th class="text-center" style="width: 10%;">Ні</th>
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
