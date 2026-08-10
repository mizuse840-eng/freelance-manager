<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">案件登録</h1>
		<p class="text-muted mb-4">クライアント：<?php echo e($client['name']); ?></p>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="post" action="/clients/<?php echo $client['id']; ?>/projects/create">
			<?php echo \Form::csrf(); ?>

			<div class="mb-3">
				<label class="form-label">案件名</label>
				<input type="text" name="name" class="form-control" value="<?php echo e($name); ?>" required>
			</div>

			<div class="mb-3">
				<label class="form-label">案件URL（任意）</label>
				<input type="url" name="url" class="form-control" value="<?php echo e($url); ?>" placeholder="https://www.lancers.jp/work/detail/...">
			</div>

			<div class="mb-3">
				<label class="form-label">期限</label>
				<input type="date" name="due_date" class="form-control" value="<?php echo e($due_date); ?>" min="<?php echo date('Y-m-d'); ?>" required>
			</div>

			<div class="mb-3">
				<label class="form-label">ステータス</label>
				<select name="project_status_id" class="form-select" required>
					<?php foreach ($statuses as $status): ?>
						<option value="<?php echo $status['id']; ?>" <?php echo ((string) $status_id === (string) $status['id']) ? 'selected' : ''; ?>>
							<?php echo e($status['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="d-flex gap-2">
				<a href="/clients/<?php echo $client['id']; ?>/projects" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-primary">保存</button>
			</div>
		</form>
	</div>
</div>
